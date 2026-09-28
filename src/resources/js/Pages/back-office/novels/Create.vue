<script setup>
import Layout from "@/pages/layouts/AuthLayout.vue";

import NovelGenerationForm from "@/components/back-office/novel/NovelGenerationForm.vue";
import NovelDataPanel from "@/components/back-office/novel/NovelDataPanel.vue";
import NovelChapterContentPanel from "@/components/back-office/novel/NovelChapterContentPanel.vue";

import {
    ref,
    computed,
    watch,
    onMounted,
    onBeforeUnmount,
    nextTick,
} from "vue";
import { Head, router } from "@inertiajs/vue3";

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library as FontAwesomeLibrary } from "@fortawesome/fontawesome-svg-core";
import {
    faSpinner,
    faCircleCheck,
    faCircleXmark,
    faCircleInfo,
    faCircleExclamation,
    faHourglassHalf,
    faListCheck,
    faPlay,
    faStop,
    faWandMagicSparkles,
} from "@fortawesome/free-solid-svg-icons";

import {
    statuses,
    stepStatuses,
    stepNames,
    novelTabs,
    novelStageSectionData,
    emptyGenerationState,
    stepStateOf,
    stepById,
    canAutoGenerate,
} from "@/composables/useNovel";

FontAwesomeLibrary.add(
    faSpinner,
    faCircleCheck,
    faCircleXmark,
    faCircleInfo,
    faCircleExclamation,
    faHourglassHalf,
    faListCheck,
    faPlay,
    faStop,
    faWandMagicSparkles,
);

defineOptions({ layout: Layout });

const createPageTitle = "Novel Generation";
const autoContinueDelaySeconds = 3;
const maxAutoContinueErrors = 3;

const props = defineProps({
    novel: {
        type: Object,
        default: null,
    },
    generationState: {
        type: Object,
        default: null,
    },
    auto: {
        type: Boolean,
        default: false,
    },
});

const isEdit = computed(() => !!props.novel?.slug);

const pageTitle = computed(() => {
    return isEdit.value
        ? `Edit ${props.novel?.title ?? ""}`.trim()
        : "New Novel";
});

const state = computed(() => props.generationState ?? emptyGenerationState());

const creating = ref(false);
const isGenerating = ref(false);
const autoContinueRemaining = ref(null);
const consecutiveAutoErrors = ref(0);

const activeStepComponentRef = ref(null);
const activeTabKey = ref(null);

let autoContinueTimer = null;

const steps = computed(() => state.value.steps ?? []);

const totalSteps = computed(() => state.value.total_count ?? steps.value.length);

const completedSteps = computed(() => state.value.completed_count ?? 0);

const progressPercent = computed(() => {
    if (totalSteps.value === 0) {
        return 0;
    }

    return Math.round((completedSteps.value / totalSteps.value) * 100);
});

const stepState = (stepId) => stepStateOf(state.value, stepId);

const stepError = (stepId) => stepById(state.value, stepId)?.error ?? null;

const latestCompletedStepId = computed(
    () => state.value.latest_completed_step?.id ?? null,
);

const nextStep = computed(() => state.value.next_step ?? null);

const runningStepId = computed(() => {
    const inProgressStep = steps.value.find(
        (step) => step.status === stepStatuses.inProgress,
    );

    if (inProgressStep) {
        return inProgressStep.id;
    }

    return isGenerating.value ? (nextStep.value?.id ?? null) : null;
});

const generationStatus = computed(() => state.value.status ?? statuses.Draft);

const isOngoing = computed(() => generationStatus.value === statuses.Ongoing);

const isRestartable = computed(() => {
    return [statuses.Failed, statuses.Stopped].includes(generationStatus.value);
});

const isAutoEnabled = computed(() => isEdit.value && props.auto === true);

const clearAutoContinue = () => {
    if (autoContinueTimer !== null) {
        clearInterval(autoContinueTimer);
        autoContinueTimer = null;
    }

    autoContinueRemaining.value = null;
};

const isAutoContinueScheduled = () => autoContinueTimer !== null;

const shouldAutoContinue = () => {
    if (!isAutoEnabled.value) {
        return false;
    }

    if (isGenerating.value || isAutoContinueScheduled()) {
        return false;
    }

    if (generationStatus.value === statuses.Complete) {
        return false;
    }

    if (consecutiveAutoErrors.value >= maxAutoContinueErrors) {
        return false;
    }

    return canAutoGenerate(state.value);
};

const scheduleAutoContinue = () => {
    clearAutoContinue();

    if (!shouldAutoContinue()) {
        return;
    }

    autoContinueRemaining.value = autoContinueDelaySeconds;

    autoContinueTimer = setInterval(() => {
        autoContinueRemaining.value -= 1;

        if (autoContinueRemaining.value > 0) {
            return;
        }

        clearAutoContinue();

        runGeneration();
    }, 1000);
};

const syncAutoContinue = () => {
    if (isGenerating.value) {
        return;
    }

    if (!shouldAutoContinue()) {
        return;
    }

    scheduleAutoContinue();
};

const runGeneration = () => {
    if (!isEdit.value || isGenerating.value) {
        return;
    }

    clearAutoContinue();
    isGenerating.value = true;

    router.patch(
        route("back-office.novels.generate", { slug: props.novel?.slug }),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                consecutiveAutoErrors.value = 0;
            },
            onError: () => {
                consecutiveAutoErrors.value += 1;
            },
            onFinish: () => {
                isGenerating.value = false;
                syncAutoContinue();
            },
        },
    );
};

const stopGeneration = () => {
    if (isGenerating.value) {
        return;
    }

    isGenerating.value = true;
    clearAutoContinue();

    router.patch(
        route("back-office.novels.stop", { slug: props.novel?.slug }),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                isGenerating.value = false;
                clearAutoContinue();
            },
        },
    );
};

const startGeneration = () => {
    creating.value = true;
    activeStepComponentRef.value?.submit();
};

const handleCreated = () => {
    creating.value = false;
    consecutiveAutoErrors.value = 0;
    syncAutoContinue();
};

const handleFinished = () => {
    creating.value = false;
};

const statusBadgeClass = computed(() => {
    switch (generationStatus.value) {
        case statuses.Ongoing:
            return "bg-blue-100 text-blue-700";
        case statuses.Pending:
            return "bg-amber-100 text-amber-700";
        case statuses.Failed:
            return "bg-red-100 text-red-700";
        case statuses.Stopped:
            return "bg-slate-200 text-slate-700";
        case statuses.Complete:
            return "bg-green-100 text-green-700";
        default:
            return "bg-gray-100 text-gray-700";
    }
});

const stepCardClass = (stepId) => {
    if (runningStepId.value === stepId) {
        return "border-blue-500 bg-blue-50";
    }

    switch (stepState(stepId)) {
        case stepStatuses.inProgress:
            return "border-blue-500 bg-blue-50";
        case stepStatuses.completed:
            return "border-green-500 bg-green-50";
        case stepStatuses.failed:
            return "border-red-500 bg-red-50";
        default:
            return "border-gray-200 bg-white";
    }
};

const stepIconClass = (stepId) => {
    if (runningStepId.value === stepId) {
        return "text-blue-600";
    }

    switch (stepState(stepId)) {
        case stepStatuses.inProgress:
            return "text-blue-600";
        case stepStatuses.completed:
            return "text-green-600";
        case stepStatuses.failed:
            return "text-red-600";
        default:
            return "text-gray-400";
    }
};

const stepLabel = (stepId) => {
    if (runningStepId.value === stepId) {
        return "Generating...";
    }

    switch (stepState(stepId)) {
        case stepStatuses.inProgress:
            return "Generating...";
        case stepStatuses.completed:
            return "Completed";
        case stepStatuses.failed:
            return "Failed";
        default:
            return "Pending";
    }
};

const isLatestCompletedStep = (stepId) => latestCompletedStepId.value === stepId;

const isNextStep = (stepId) => nextStep.value?.id === stepId;

const getTabClass = (tab) => {
    if (!tab.enabled) {
        return "text-gray-300 cursor-not-allowed border border-transparent";
    }

    if (activeTabKey.value === tab.key) {
        return "bg-white text-blue-800 border border-blue-400 shadow-sm ring-1 ring-blue-200";
    }

    if (isNextStep(tab.stepId)) {
        return "text-gray-700 hover:bg-gray-100 border border-transparent";
    }

    return "text-gray-600 hover:bg-gray-100 hover:text-gray-900 border border-transparent";
};

const getTabDotClass = (stepId) => {
    if (runningStepId.value === stepId) {
        return "bg-blue-500";
    }

    switch (stepState(stepId)) {
        case stepStatuses.inProgress:
            return "bg-blue-500";
        case stepStatuses.completed:
            return "bg-green-500";
        case stepStatuses.failed:
            return "bg-red-500";
        default:
            return "bg-gray-300";
    }
};

const failedStep = computed(() => {
    return steps.value.find((step) => step.status === stepStatuses.failed) ?? null;
});

const autoGenerationHint = computed(() => {
    if (!isEdit.value) {
        return null;
    }

    if (isGenerating.value) {
        const runningStep = steps.value.find((step) => step.id === runningStepId.value);

        return {
            tone: "running",
            icon: "spinner",
            spin: true,
            text: runningStep
                ? `Generating ${runningStep.name}...`
                : "Preparing generation...",
        };
    }

    if (!isAutoEnabled.value) {
        return {
            tone: "paused",
            icon: "circle-info",
            spin: false,
            text: state.value.is_complete
                ? "Every stage is generated."
                : "Automatic generation is off.",
        };
    }

    if (consecutiveAutoErrors.value >= maxAutoContinueErrors) {
        return {
            tone: "error",
            icon: "circle-exclamation",
            spin: false,
            text: "Automatic generation paused after repeated request errors.",
        };
    }

    if (!canAutoGenerate(state.value)) {
        return {
            tone: "paused",
            icon: "circle-info",
            spin: false,
            text: "Every stage is generated.",
        };
    }

    if (isAutoContinueScheduled()) {
        return {
            tone: "waiting",
            icon: "hourglass-half",
            spin: false,
            text: `Next up: ${nextStep.value.name} in ${autoContinueRemaining.value}s`,
        };
    }

    return {
        tone: "running",
        icon: "hourglass-half",
        spin: false,
        text: `Next up: ${nextStep.value.name}`,
    };
});

const autoHintClass = computed(() => {
    switch (autoGenerationHint.value?.tone) {
        case "running":
            return "text-blue-700";
        case "waiting":
            return "text-slate-600";
        case "error":
            return "text-red-600";
        default:
            return "text-slate-500";
    }
});

const tabs = computed(() => novelTabs(props.novel, steps.value));

const enabledTabs = computed(() => tabs.value.filter((tab) => tab.enabled));

const activeStep = computed(() => {
    return (
        steps.value.find((step) => `step-${step.id}` === activeTabKey.value) ??
        null
    );
});

const activeStageSections = computed(() => {
    return activeStep.value
        ? novelStageSectionData(activeStep.value.name, props.novel)
        : [];
});

const selectTab = (tab) => {
    if (!tab?.enabled) {
        return;
    }

    activeTabKey.value = tab.key;
};

const focusTab = (key) => {
    nextTick(() => {
        document.getElementById(`novel-tab-${key}`)?.focus();
    });
};

const onTabKeydown = (event) => {
    const available = enabledTabs.value;

    if (
        available.length === 0 ||
        !["ArrowLeft", "ArrowRight", "Home", "End"].includes(event.key)
    ) {
        return;
    }

    event.preventDefault();

    const currentIndex = available.findIndex(
        (tab) => tab.key === activeTabKey.value,
    );

    if (event.key === "Home") {
        activeTabKey.value = available[0].key;
    } else if (event.key === "End") {
        activeTabKey.value = available[available.length - 1].key;
    } else {
        const offset = event.key === "ArrowRight" ? 1 : -1;
        const nextIndex =
            currentIndex < 0
                ? 0
                : (currentIndex + offset + available.length) %
                  available.length;

        activeTabKey.value = available[nextIndex].key;
    }

    focusTab(activeTabKey.value);
};

const tabKeyForStepId = (stepId) => {
    const tab = tabs.value.find((item) => item.stepId === stepId);

    return tab?.enabled ? tab.key : null;
};

watch(
    latestCompletedStepId,
    (stepId) => {
        const key = tabKeyForStepId(stepId);

        if (key === null) {
            return;
        }

        activeTabKey.value = key;
    },
    { immediate: true },
);

watch(
    enabledTabs,
    (available) => {
        if (available.some((tab) => tab.key === activeTabKey.value)) {
            return;
        }

        activeTabKey.value = available.length > 0 ? available[0].key : null;
    },
    { immediate: true },
);

watch(
    [
        () => props.auto,
        isEdit,
        generationStatus,
        latestCompletedStepId,
        () => state.value.is_complete,
    ],
    () => {
        syncAutoContinue();
    },
);

onMounted(async () => {
    await nextTick();

    window.dispatchEvent(
        new CustomEvent("set-breadcrumb", {
            detail: [
                {
                    text: "Novels",
                    href: route("back-office.novels.index"),
                },
                {
                    text: createPageTitle,
                    active: true,
                },
            ],
        }),
    );

    syncAutoContinue();
});

onBeforeUnmount(() => {
    clearAutoContinue();
});
</script>

<template>
    <Head :title="createPageTitle" />

    <div class="w-full">
        <div
            class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 md:p-6"
        >
            <div
                class="flex items-center justify-between gap-4 px-0 py-4 border-b border-gray-200"
            >
                <h2 class="text-lg font-semibold flex items-center gap-2">
                    <FontAwesomeIcon
                        icon="wand-magic-sparkles"
                        class="text-purple-600"
                    />
                    {{ pageTitle }}
                </h2>

                <span
                    v-if="isEdit"
                    class="inline-flex items-center gap-2 px-3 py-1 text-xs font-semibold rounded-full"
                    :class="statusBadgeClass"
                >
                    <FontAwesomeIcon
                        v-if="isGenerating"
                        icon="spinner"
                        spin
                    />
                    {{ generationStatus }}
                </span>
            </div>

            <div v-if="!isEdit" class="px-0 py-6">
                <NovelGenerationForm
                    ref="activeStepComponentRef"
                    :novel="novel"
                    @submitting="creating = true"
                    @completed="handleCreated"
                    @finished="handleFinished"
                />

                <div
                    class="px-0 pt-4 border-t border-gray-200 flex justify-end items-center gap-2"
                >
                    <button
                        type="button"
                        @click="startGeneration"
                        :disabled="creating"
                        class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md flex items-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        <FontAwesomeIcon
                            v-if="creating"
                            icon="spinner"
                            spin
                        />
                        <FontAwesomeIcon
                            v-else
                            icon="wand-magic-sparkles"
                        />

                        {{ creating ? "Generating Foundation..." : "Generate Novel" }}
                    </button>
                </div>
            </div>

            <div v-else class="px-0 pt-6 space-y-6">
                <div
                    class="flex flex-col gap-4 bg-gray-50 border border-gray-200 rounded-xl p-4 lg:flex-row lg:items-center lg:justify-between"
                >
                    <div class="space-y-2 flex-1">
                        <div
                            class="flex flex-wrap items-center gap-x-3 gap-y-1"
                        >
                            <span class="text-sm text-gray-600">
                                Progress
                            </span>
                            <span class="text-sm font-semibold">
                                {{ completedSteps }} / {{ totalSteps }} stages
                            </span>
                            <span class="text-sm font-semibold text-blue-700">
                                {{ progressPercent }}%
                            </span>
                        </div>

                        <div
                            class="w-full h-2 bg-gray-200 rounded-full overflow-hidden"
                        >
                            <div
                                class="h-full bg-blue-600 rounded-full transition-all"
                                :style="{ width: `${progressPercent}%` }"
                            ></div>
                        </div>

                        <p
                            v-if="autoGenerationHint"
                            class="text-sm flex items-center gap-2"
                            :class="autoHintClass"
                        >
                            <FontAwesomeIcon
                                :icon="autoGenerationHint.icon"
                                :spin="autoGenerationHint.spin"
                            />
                            {{ autoGenerationHint.text }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <button
                            v-if="isRestartable || isOngoing"
                            type="button"
                            @click="runGeneration"
                            :disabled="isGenerating || !nextStep"
                            class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md flex items-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <FontAwesomeIcon
                                v-if="isGenerating"
                                icon="spinner"
                                spin
                            />
                            <FontAwesomeIcon v-else icon="play" />
                            {{ isRestartable ? "Resume Generation" : "Continue" }}
                        </button>

                        <button
                            v-if="isOngoing"
                            type="button"
                            @click="stopGeneration"
                            :disabled="isGenerating"
                            class="px-4 py-2 text-sm bg-red-500 hover:bg-red-600 text-white rounded-md flex items-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <FontAwesomeIcon icon="stop" />
                            Stop
                        </button>
                    </div>
                </div>

                <ol
                    class="flex gap-2 overflow-x-auto pb-1"
                    aria-label="Generation stages"
                >
                    <li
                        v-for="step in steps"
                        :key="step.id"
                        class="min-w-[180px] flex-1 rounded-xl border px-3 py-2 transition"
                        :class="stepCardClass(step.id)"
                    >
                        <div class="flex items-center gap-2">
                            <span
                                class="flex items-center justify-center w-7 h-7 rounded-full text-xs font-bold flex-shrink-0"
                                :class="stepIconClass(step.id)"
                            >
                                <FontAwesomeIcon
                                    v-if="
                                        stepState(step.id) ===
                                        stepStatuses.completed
                                    "
                                    icon="circle-check"
                                />
                                <FontAwesomeIcon
                                    v-else-if="
                                        stepState(step.id) === stepStatuses.failed
                                    "
                                    icon="circle-xmark"
                                />
                                <FontAwesomeIcon
                                    v-else-if="runningStepId === step.id"
                                    icon="spinner"
                                    spin
                                />
                                <FontAwesomeIcon v-else icon="hourglass-half" />
                            </span>

                            <div class="min-w-0">
                                <p
                                    class="text-sm font-medium text-gray-900 truncate"
                                >
                                    {{ step.name }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ stepLabel(step.id) }}
                                </p>
                            </div>
                        </div>

                        <p
                            v-if="stepState(step.id) === stepStatuses.failed && stepError(step.id)"
                            class="text-xs text-red-600 mt-2 break-words"
                        >
                            {{ stepError(step.id) }}
                        </p>
                    </li>
                </ol>

                <p
                    v-if="failedStep"
                    class="text-sm text-red-700 bg-red-50 border border-red-200 rounded-lg px-4 py-3 flex items-start gap-2"
                >
                    <FontAwesomeIcon
                        icon="circle-exclamation"
                        class="mt-0.5"
                    />
                    <span>
                        Stage "{{ failedStep.name }}" failed and is not marked
                        as generated. Automatic generation is paused. Use
                        "Resume Generation" to retry it.
                    </span>
                </p>

                <div
                    role="tablist"
                    aria-label="Novel generation data"
                    class="bg-gray-50 border border-gray-200 rounded-xl p-1 flex gap-1 overflow-x-auto"
                    @keydown="onTabKeydown"
                >
                    <button
                        v-for="tab in tabs"
                        :id="`novel-tab-${tab.key}`"
                        :key="tab.key"
                        type="button"
                        role="tab"
                        :aria-selected="activeTabKey === tab.key"
                        :aria-controls="`novel-panel-${tab.key}`"
                        :tabindex="activeTabKey === tab.key ? 0 : -1"
                        :disabled="!tab.enabled"
                        :class="getTabClass(tab)"
                        class="shrink-0 px-3 py-2 text-sm font-medium rounded-lg whitespace-nowrap transition flex items-center gap-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        @click="selectTab(tab)"
                    >
                        <span
                            aria-hidden="true"
                            class="w-2 h-2 rounded-full flex-shrink-0"
                            :class="getTabDotClass(tab.stepId)"
                        ></span>

                        {{ tab.label }}

                        <span
                            v-if="isLatestCompletedStep(tab.stepId)"
                            class="text-[10px] font-semibold uppercase tracking-wide text-blue-600"
                        >
                            Latest
                        </span>
                    </button>
                </div>

                <Transition
                    mode="out-in"
                    enter-active-class="transition ease-out duration-200"
                    enter-from-class="opacity-0 translate-y-1"
                    leave-active-class="transition ease-in duration-150"
                    leave-to-class="opacity-0"
                >
                    <div
                        :id="
                            activeTabKey
                                ? `novel-panel-${activeTabKey}`
                                : undefined
                        "
                        :key="activeTabKey || 'empty'"
                        :role="activeTabKey ? 'tabpanel' : 'status'"
                        :aria-labelledby="
                            activeTabKey
                                ? `novel-tab-${activeTabKey}`
                                : undefined
                        "
                        :tabindex="activeTabKey ? 0 : undefined"
                        class="focus:outline-none"
                    >
                        <div v-if="activeStep" class="space-y-4">
                            <div
                                class="border rounded-lg p-4 flex items-start gap-3 transition"
                                :class="stepCardClass(activeStep.id)"
                            >
                                <span
                                    class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold flex-shrink-0"
                                    :class="stepIconClass(activeStep.id)"
                                >
                                    <FontAwesomeIcon
                                        v-if="
                                            stepState(activeStep.id) ===
                                            stepStatuses.completed
                                        "
                                        icon="circle-check"
                                    />
                                    <FontAwesomeIcon
                                        v-else-if="
                                            stepState(activeStep.id) ===
                                            stepStatuses.failed
                                        "
                                        icon="circle-xmark"
                                    />
                                    <FontAwesomeIcon
                                        v-else-if="
                                            runningStepId === activeStep.id
                                        "
                                        icon="spinner"
                                        spin
                                    />
                                    <FontAwesomeIcon
                                        v-else
                                        icon="hourglass-half"
                                    />
                                </span>

                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-800">
                                        {{ activeStep.name }}
                                    </p>
                                    <p class="text-xs text-gray-500">
                                        {{ stepLabel(activeStep.id) }}
                                    </p>
                                    <p
                                        v-if="
                                            stepState(activeStep.id) ===
                                                stepStatuses.failed &&
                                            stepError(activeStep.id)
                                        "
                                        class="text-xs text-red-600 mt-1 break-words"
                                    >
                                        {{ stepError(activeStep.id) }}
                                    </p>
                                </div>
                            </div>

                            <NovelChapterContentPanel
                                v-if="activeStep.name === stepNames.chapterContent"
                                :chapters="props.novel?.novelChapters ?? []"
                            />

                            <div
                                v-else
                                class="space-y-4"
                            >
                                <section
                                    v-for="section in activeStageSections"
                                    :key="section.key"
                                    class="border border-gray-200 rounded-xl bg-white p-4 shadow-sm"
                                >
                                    <h3
                                        class="text-sm font-semibold text-gray-900 mb-3"
                                    >
                                        {{ section.label }}
                                    </h3>

                                    <NovelDataPanel :sections="section.data" />
                                </section>
                            </div>
                        </div>

                        <div
                            v-else
                            class="border border-dashed border-gray-300 rounded-xl bg-white p-8 text-center"
                        >
                            <FontAwesomeIcon
                                icon="list-check"
                                class="text-gray-300 text-2xl mb-3"
                            />

                            <p class="text-sm font-medium text-gray-700">
                                No generated data yet
                            </p>

                            <p class="text-xs text-gray-500 mt-1">
                                Each tab becomes available once its generated
                                data is stored on this novel.
                            </p>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>
    </div>
</template>

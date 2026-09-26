<script setup>
import Layout from "@/pages/layouts/AuthLayout.vue";

import NovelStep1Form from "@/components/back-office/novel/NovelStep1Form.vue";

import { ref, computed, onMounted, onBeforeUnmount, nextTick } from "vue";
import { Head, router } from "@inertiajs/vue3";

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library as FontAwesomeLibrary } from "@fortawesome/fontawesome-svg-core";
import {
    faSpinner,
    faCircleCheck,
    faCircleXmark,
    faHourglassHalf,
    faStop,
    faRotateRight,
    faArrowLeft,
    faWandMagicSparkles,
} from "@fortawesome/free-solid-svg-icons";

import { statuses, stepStatuses } from "@/composables/useNovel";

FontAwesomeLibrary.add(
    faSpinner,
    faCircleCheck,
    faCircleXmark,
    faHourglassHalf,
    faStop,
    faRotateRight,
    faArrowLeft,
    faWandMagicSparkles,
);

defineOptions({ layout: Layout });

const createPageTitle = "Novel Generation";

const props = defineProps({
    novel: {
        type: Object,
        default: null,
    },
    generationSteps: {
        type: Array,
        default: () => [],
    },
});

const isEdit = computed(() => !!props.novel?.slug);

const pageTitle = computed(() => {
    return isEdit.value ? `Edit ${props.novel?.name}` : "New Novel";
});

const steps = computed(() => props.generationSteps ?? []);

const progressMap = computed(() => props.novel?.generation_steps ?? {});

const totalSteps = computed(() => steps.value.length);

const completedSteps = computed(() => {
    return steps.value.filter(
        (step) =>
            progressMap.value[step.id]?.status === stepStatuses.completed,
    ).length;
});

const progressPercent = computed(() => {
    if (totalSteps.value === 0) {
        return 0;
    }

    return Math.round((completedSteps.value / totalSteps.value) * 100);
});

const stepState = (stepId) => {
    return progressMap.value[stepId]?.status ?? stepStatuses.pending;
};

const currentStep = computed(() => {
    return (
        steps.value.find(
            (step) => stepState(step.id) === stepStatuses.inProgress,
        ) ??
        steps.value.find((step) => stepState(step.id) === stepStatuses.pending) ??
        null
    );
});

const generationStatus = computed(() => props.novel?.status);

const isOngoing = computed(() => {
    return generationStatus.value === statuses.Ongoing;
});

const isRestartable = computed(() => {
    return [statuses.Failed, statuses.Stopped].includes(generationStatus.value);
});

const hasAutoFlag = () => {
    return new URL(window.location.href).searchParams.get("auto") === "1";
};

const AUTO_CONTINUE_DELAY = 3;

const creating = ref(false);
const isGenerating = ref(false);
const autoContinueRemaining = ref(null);
let autoContinueTimer = null;

const stopCountdown = () => {
    if (autoContinueTimer !== null) {
        clearInterval(autoContinueTimer);
        autoContinueTimer = null;
    }

    autoContinueRemaining.value = null;
};

const startAutoContinue = () => {
    stopCountdown();

    if (!isEdit.value || !isOngoing.value || !hasAutoFlag()) {
        return;
    }

    autoContinueRemaining.value = AUTO_CONTINUE_DELAY;

    autoContinueTimer = setInterval(() => {
        autoContinueRemaining.value -= 1;

        if (autoContinueRemaining.value <= 0) {
            stopCountdown();
            continueGeneration();
        }
    }, 1000);
};

const continueGeneration = () => {
    isGenerating.value = true;

    router.patch(
        route("back-office.novels.generate", { slug: props.novel?.slug }),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                isGenerating.value = false;
                stopCountdown();
            },
        },
    );
};

const stopGeneration = () => {
    isGenerating.value = true;

    router.patch(
        route("back-office.novels.stop", { slug: props.novel?.slug }),
        {},
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                isGenerating.value = false;
                stopCountdown();
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
};

const handleFinished = () => {
    creating.value = false;
};

const activeStepComponentRef = ref(null);

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

const getStepClass = (stepId) => {
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

const getStepIconClass = (stepId) => {
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

const getStepLabel = (stepId) => {
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

const failedStep = computed(() => {
    return (
        steps.value.find(
            (step) => stepState(step.id) === stepStatuses.failed,
        ) ?? null
    );
});

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

    startAutoContinue();
});

onBeforeUnmount(stopCountdown);
</script>

<template>
    <Head :title="createPageTitle" />

    <div class="w-full">
        <div
            class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 md:p-6"
        >
            <div
                class="flex items-center justify-between px-0 py-4 border-b border-gray-200"
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
                <NovelStep1Form
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
                    class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 bg-gray-50 border border-gray-200 rounded-xl p-4"
                >
                    <div class="space-y-1 flex-1">
                        <div class="flex items-center gap-3">
                            <span class="text-sm text-gray-600">
                                Progress
                            </span>
                            <span class="text-sm font-semibold">
                                {{ completedSteps }} / {{ totalSteps }} steps
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

                        <p v-if="isGenerating" class="text-sm text-blue-700">
                            <FontAwesomeIcon icon="spinner" spin class="mr-1" />
                            Generating...
                        </p>

                        <p
                            v-else-if="
                                autoContinueRemaining !== null &&
                                isOngoing
                            "
                            class="text-sm text-slate-500"
                        >
                            <FontAwesomeIcon
                                icon="hourglass-half"
                                class="mr-1"
                            />
                            Auto-continuing in
                            {{ autoContinueRemaining }}s...
                        </p>

                        <p
                            v-else-if="currentStep"
                            class="text-sm text-slate-500"
                        >
                            <FontAwesomeIcon
                                icon="hourglass-half"
                                class="mr-1 text-gray-400"
                            />
                            Upcoming:
                            {{ currentStep.name }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <template v-if="isRestartable">
                            <button
                                type="button"
                                @click="continueGeneration"
                                :disabled="isGenerating"
                                class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md flex items-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed"
                            >
                                <FontAwesomeIcon
                                    v-if="isGenerating"
                                    icon="spinner"
                                    spin
                                />
                                <FontAwesomeIcon v-else icon="rotate-right" />
                                Resume Generation
                            </button>
                        </template>

                        <template v-else-if="isOngoing">
                            <button
                                type="button"
                                @click="continueGeneration"
                                :disabled="isGenerating"
                                class="px-4 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md flex items-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed"
                            >
                                <FontAwesomeIcon
                                    v-if="isGenerating"
                                    icon="spinner"
                                    spin
                                />
                                <FontAwesomeIcon v-else icon="rotate-right" />
                                Continue
                            </button>

                            <button
                                type="button"
                                @click="stopGeneration"
                                :disabled="isGenerating"
                                class="px-4 py-2 text-sm bg-red-500 hover:bg-red-600 text-white rounded-md flex items-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed"
                            >
                                <FontAwesomeIcon icon="stop" />
                                Stop
                            </button>
                        </template>
                    </div>
                </div>

                <p
                    v-if="failedStep"
                    class="text-sm text-red-600 bg-red-50 border border-red-200 rounded-lg px-4 py-3"
                >
                    <FontAwesomeIcon icon="circle-xmark" class="mr-1" />
                    Step "{{ failedStep.name }}" failed. You can resume
                    generation to retry.
                </p>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div
                        v-for="step in steps"
                        :key="step.id"
                        class="border rounded-lg p-3 flex items-start gap-3 transition"
                        :class="getStepClass(step.id)"
                    >
                        <span
                            class="flex items-center justify-center w-8 h-8 rounded-full text-sm font-bold flex-shrink-0"
                            :class="getStepIconClass(step.id)"
                        >
                            <FontAwesomeIcon
                                v-if="stepState(step.id) === stepStatuses.completed"
                                icon="circle-check"
                            />
                            <FontAwesomeIcon
                                v-else-if="
                                    stepState(step.id) === stepStatuses.failed
                                "
                                icon="circle-xmark"
                            />
                            <FontAwesomeIcon
                                v-else-if="
                                    stepState(step.id) ===
                                    stepStatuses.inProgress
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
                                {{ step.name }}
                            </p>
                            <p class="text-xs text-gray-500">
                                {{ getStepLabel(step.id) }}
                            </p>
                            <p
                                v-if="
                                    stepState(step.id) ===
                                        stepStatuses.failed &&
                                    progressMap[step.id]?.error
                                "
                                class="text-xs text-red-600 mt-1 break-words"
                            >
                                {{ progressMap[step.id].error }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
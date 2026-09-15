<script setup>
import Layout from "@/pages/layouts/AuthLayout.vue";
import InfiniteScrollApiSelect from "@/components/common/multi-select/InfiniteScrollApiSelect.vue";

import {
    AiBrainOutputTypes,
    buildAiBrainSearchUrl,
} from "@/composables/useAiBrain";

import { ref, computed, onMounted, nextTick, watch } from "vue";
import { Head, useForm } from "@inertiajs/vue3";

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library as FontAwesomeLibrary } from "@fortawesome/fontawesome-svg-core";
import {
    faSave,
    faSpinner,
    faCheck,
    faLock,
    faArrowRight,
    faArrowLeft,
    faWandMagicSparkles,
    faBrain,
    faUser,
    faGlobe,
    faBook,
    faCog,
} from "@fortawesome/free-solid-svg-icons";

FontAwesomeLibrary.add(
    faSave,
    faSpinner,
    faCheck,
    faLock,
    faArrowRight,
    faArrowLeft,
    faWandMagicSparkles,
    faBrain,
    faUser,
    faGlobe,
    faBook,
    faCog,
);

defineOptions({ layout: Layout });

const createPageTitle = "Create Novel";

const props = defineProps({
    novel: { type: Object, default: null },
});

const isUpdate = computed(() => !!props.novel?.slug);

const pageTitle = computed(() => {
    return isUpdate.value ? `Edit ${props.novel?.name}` : "New Novel";
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
});

const STEP_DEFINITIONS = [
    {
        id: 1,
        name: "Plot Generator",
        icon: "cog",
        description: "Basic generation settings",
        aiBrainOutputTypeCode: AiBrainOutputTypes.Text,
    },
    {
        id: 2,
        name: "World Building",
        icon: "globe",
        description: "World and setting details",
        aiBrainOutputTypeCode: AiBrainOutputTypes.Text,
    },
    {
        id: 3,
        name: "Characters",
        icon: "user",
        description: "Character development",
        aiBrainOutputTypeCode: null,
    },
    {
        id: 4,
        name: "Plot & Outline",
        icon: "book",
        description: "Story structure",
        aiBrainOutputTypeCode: null,
    },
    {
        id: 5,
        name: "Review & Generate",
        icon: "wand-magic-sparkles",
        description: "Final review",
        aiBrainOutputTypeCode: null,
    },
];

const getStepDefinition = (stepId) => {
    return STEP_DEFINITIONS.find((item) => item.id === stepId);
};

const activeStep = ref(1);
const completedSteps = ref(new Set());
const submittingStep = ref(null);

const plotGeneratorSaveForm = useForm({
    is_18_plus: props.novel?.is_18_plus ?? false,
    enable_mature_content: props.novel?.enable_mature_content ?? false,
    additional_information: props.novel?.additional_information ?? null,
    novel_continuity: props.novel?.novel_continuity ?? null,
    language_id: props.novel?.language_id ?? null,
    genre_ids: props.novel?.genres?.map((genre) => genre.id) ?? [],
    novel_type_id: props.novel?.novel_type_id ?? null,
    audience_id: props.novel?.audience_id ?? null,
    ai_brain_id: props.novel?.ai_brain_id ?? null,
});

const worldBuildingSaveForm = useForm({
    ai_brain_id: null,
});

const charactersSaveForm = useForm({
    ai_brain_id: null,
});

const plotOutlineSaveForm = useForm({
    ai_brain_id: null,
});

const reviewGenerateSaveForm = useForm({
    ai_brain_id: null,
});

const STEP_FORMS = {
    1: plotGeneratorSaveForm,
    2: worldBuildingSaveForm,
    3: charactersSaveForm,
    4: plotOutlineSaveForm,
    5: reviewGenerateSaveForm,
};

const genresApiUrl = computed(() => {
    const audienceId = plotGeneratorSaveForm.audience_id;

    if (!audienceId) {
        return route("search.genres");
    }

    return `${route("search.genres")}?audience_id=${encodeURIComponent(
        audienceId,
    )}`;
});

const audienceDependentFieldsReset = ref(false);
const audienceDependentFieldsKey = ref(0);

watch(
    () => plotGeneratorSaveForm.audience_id,
    (newAudienceId, oldAudienceId) => {
        if (newAudienceId === oldAudienceId) {
            return;
        }

        audienceDependentFieldsReset.value = true;

        plotGeneratorSaveForm.novel_continuity = null;
        plotGeneratorSaveForm.language_id = null;
        plotGeneratorSaveForm.genre_ids = [];
        plotGeneratorSaveForm.novel_type_id = null;
        plotGeneratorSaveForm.additional_information = null;
        plotGeneratorSaveForm.is_18_plus = false;
        plotGeneratorSaveForm.enable_mature_content = false;

        plotGeneratorSaveForm.clearErrors(
            "novel_continuity",
            "language_id",
            "genre_ids",
            "novel_type_id",
            "additional_information",
            "is_18_plus",
            "enable_mature_content",
        );

        audienceDependentFieldsKey.value++;
    },
);

const isStepAccessible = (stepId) => {
    if (stepId === 1) {
        return true;
    }

    return completedSteps.value.has(stepId - 1);
};

const isStepCompleted = (stepId) => {
    return completedSteps.value.has(stepId);
};

const getStepState = (stepId) => {
    if (isStepCompleted(stepId)) {
        return "completed";
    }

    if (stepId === activeStep.value) {
        return "active";
    }

    if (isStepAccessible(stepId)) {
        return "accessible";
    }

    return "locked";
};

const validateStep = (stepId) => {
    const step = getStepDefinition(stepId);
    const stepForm = STEP_FORMS[stepId];

    stepForm.clearErrors();

    let valid = true;

    if (step?.aiBrainOutputTypeCode && !stepForm.ai_brain_id) {
        stepForm.setError("ai_brain_id", "AI Brain selection is required");
        valid = false;
    }

    if (stepId === 1) {
        if (!stepForm.novel_continuity) {
            stepForm.setError(
                "novel_continuity",
                "Novel continuity is required",
            );
            valid = false;
        }

        if (!stepForm.language_id) {
            stepForm.setError("language_id", "Language is required");
            valid = false;
        }

        if (
            !Array.isArray(stepForm.genre_ids) ||
            stepForm.genre_ids.length === 0
        ) {
            stepForm.setError("genre_ids", "Genres is required");
            valid = false;
        }

        if (!stepForm.novel_type_id) {
            stepForm.setError("novel_type_id", "Novel type is required");
            valid = false;
        }

        if (!stepForm.audience_id) {
            stepForm.setError("audience_id", "Audience is required");
            valid = false;
        }

        if (stepForm.enable_mature_content && !stepForm.is_18_plus) {
            stepForm.setError(
                "enable_mature_content",
                "Mature content requires 18+ setting",
            );
            valid = false;
        }
    }

    return valid;
};

function submitStep1() {
    if (plotGeneratorSaveForm.processing) {
        return;
    }

    if (!validateStep(1)) {
        return;
    }

    submittingStep.value = 1;

    plotGeneratorSaveForm.post(route("back-office.novels.save.plot"), {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            completedSteps.value.add(1);
            activeStep.value = 2;
            plotGeneratorSaveForm.clearErrors();
        },
        onError: (errors) => {
            plotGeneratorSaveForm.clearErrors();
            plotGeneratorSaveForm.setError(errors);
        },
        onFinish: () => {
            submittingStep.value = null;
        },
    });
}

function submitStep2() {
    if (worldBuildingSaveForm.processing) {
        return;
    }

    if (!validateStep(2)) {
        return;
    }

    submittingStep.value = 2;

    completedSteps.value.add(2);
    activeStep.value = 3;

    worldBuildingSaveForm.clearErrors();
    submittingStep.value = null;
}

const goToStep = (stepId) => {
    if (isStepAccessible(stepId) || isStepCompleted(stepId)) {
        activeStep.value = stepId;
    }
};

const goNext = () => {
    if (activeStep.value < 5 && isStepAccessible(activeStep.value + 1)) {
        activeStep.value++;
    }
};

const goPrev = () => {
    if (activeStep.value > 1) {
        activeStep.value--;
    }
};
</script>

<template>
    <Head :title="createPageTitle" />

    <div class="w-full">
        <div
            class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 md:p-6"
        >
            <div class="flex flex-col">
                <div
                    class="flex items-center px-0 py-4 border-b border-gray-200"
                >
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <FontAwesomeIcon
                            icon="wand-magic-sparkles"
                            class="text-purple-600"
                        />
                        {{ pageTitle }}
                    </h2>
                </div>

                <div class="px-0 pt-4 border-b border-gray-200">
                    <nav class="hidden md:flex overflow-x-auto pb-px">
                        <button
                            v-for="step in STEP_DEFINITIONS"
                            :key="step.id"
                            type="button"
                            @click="goToStep(step.id)"
                            :disabled="
                                !isStepAccessible(step.id) &&
                                !isStepCompleted(step.id)
                            "
                            class="flex items-center gap-2 px-4 py-3 text-sm font-medium border-b-2 whitespace-nowrap transition disabled:opacity-40 disabled:cursor-not-allowed"
                            :class="{
                                'border-blue-600 text-blue-600':
                                    getStepState(step.id) === 'active',
                                'border-green-500 text-green-600':
                                    getStepState(step.id) === 'completed',
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300':
                                    getStepState(step.id) === 'accessible',
                                'border-transparent text-gray-300':
                                    getStepState(step.id) === 'locked',
                            }"
                        >
                            <span
                                class="flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold"
                                :class="{
                                    'bg-blue-600 text-white':
                                        getStepState(step.id) === 'active',
                                    'bg-green-500 text-white':
                                        getStepState(step.id) === 'completed',
                                    'bg-gray-200 text-gray-600':
                                        getStepState(step.id) === 'accessible',
                                    'bg-gray-100 text-gray-400':
                                        getStepState(step.id) === 'locked',
                                }"
                            >
                                <FontAwesomeIcon
                                    v-if="isStepCompleted(step.id)"
                                    icon="check"
                                    class="text-xs"
                                />
                                <span v-else>{{ step.id }}</span>
                            </span>

                            <span>{{ step.name }}</span>

                            <FontAwesomeIcon
                                v-if="
                                    !isStepAccessible(step.id) &&
                                    !isStepCompleted(step.id)
                                "
                                icon="lock"
                                class="text-xs text-gray-300"
                            />
                        </button>
                    </nav>

                    <nav class="md:hidden -mx-2 px-2">
                        <button
                            v-for="step in STEP_DEFINITIONS"
                            :key="step.id"
                            type="button"
                            @click="goToStep(step.id)"
                            :disabled="
                                !isStepAccessible(step.id) &&
                                !isStepCompleted(step.id)
                            "
                            class="w-full flex items-center gap-3 px-3 py-2.5 text-sm rounded-lg transition disabled:opacity-40 disabled:cursor-not-allowed text-left"
                            :class="{
                                'bg-blue-50 text-blue-700':
                                    getStepState(step.id) === 'active' ||
                                    getStepState(step.id) === 'completed',
                                'text-gray-600 hover:bg-gray-50':
                                    getStepState(step.id) === 'accessible',
                                'text-gray-300':
                                    getStepState(step.id) === 'locked',
                            }"
                        >
                            <span
                                class="flex items-center justify-center w-6 h-6 rounded-full text-xs font-bold flex-shrink-0"
                                :class="{
                                    'bg-blue-600 text-white':
                                        getStepState(step.id) === 'active',
                                    'bg-green-500 text-white':
                                        getStepState(step.id) === 'completed',
                                    'bg-gray-200 text-gray-600':
                                        getStepState(step.id) === 'accessible',
                                    'bg-gray-100 text-gray-400':
                                        getStepState(step.id) === 'locked',
                                }"
                            >
                                <FontAwesomeIcon
                                    v-if="isStepCompleted(step.id)"
                                    icon="check"
                                    class="text-xs"
                                />
                                <span v-else>{{ step.id }}</span>
                            </span>

                            <span class="flex-1">
                                {{ step.name }}
                            </span>

                            <FontAwesomeIcon
                                v-if="
                                    !isStepAccessible(step.id) &&
                                    !isStepCompleted(step.id)
                                "
                                icon="lock"
                                class="text-xs text-gray-300 flex-shrink-0"
                            />
                        </button>
                    </nav>
                </div>

                <div class="px-0 py-6">
                    <div v-if="activeStep === 1" class="space-y-6">
                        <div
                            class="bg-white border rounded-xl p-5 shadow-sm space-y-4"
                        >
                            <h3
                                class="text-base font-semibold flex items-center gap-2"
                            >
                                <FontAwesomeIcon
                                    icon="cog"
                                    class="text-blue-600"
                                />
                                Generation Configuration
                            </h3>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label
                                        class="block text-sm font-medium mb-1"
                                    >
                                        Audience
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <InfiniteScrollApiSelect
                                        :form="plotGeneratorSaveForm"
                                        fieldName="audience_id"
                                        :selectedItem="props.novel?.audience"
                                        :apiUrl="route('search.audiences')"
                                        :multiple="false"
                                        placeholder="Select audiences"
                                        :error="
                                            plotGeneratorSaveForm.errors
                                                .audience_id
                                        "
                                    />

                                    <p
                                        v-if="
                                            plotGeneratorSaveForm.errors
                                                .audience_id
                                        "
                                        class="text-red-500 text-sm mt-1"
                                    >
                                        {{
                                            plotGeneratorSaveForm.errors
                                                .audience_id
                                        }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium mb-1"
                                    >
                                        Novel Continuity
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <InfiniteScrollApiSelect
                                        :key="`novel-continuity-${audienceDependentFieldsKey}`"
                                        :form="plotGeneratorSaveForm"
                                        fieldName="novel_continuity"
                                        :selectedItem="
                                            audienceDependentFieldsReset
                                                ? null
                                                : props.novel?.novel_continuity
                                        "
                                        :apiUrl="
                                            route('search.novel-continuities')
                                        "
                                        :multiple="false"
                                        placeholder="Select continuity"
                                        :error="
                                            plotGeneratorSaveForm.errors
                                                .novel_continuity
                                        "
                                    />

                                    <p
                                        v-if="
                                            plotGeneratorSaveForm.errors
                                                .novel_continuity
                                        "
                                        class="text-red-500 text-sm mt-1"
                                    >
                                        {{
                                            plotGeneratorSaveForm.errors
                                                .novel_continuity
                                        }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium mb-1"
                                    >
                                        Language
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <InfiniteScrollApiSelect
                                        :key="`language-${audienceDependentFieldsKey}`"
                                        :form="plotGeneratorSaveForm"
                                        fieldName="language_id"
                                        :selectedItem="
                                            audienceDependentFieldsReset
                                                ? null
                                                : props.novel?.language
                                        "
                                        :apiUrl="route('search.languages')"
                                        :multiple="false"
                                        placeholder="Select languages"
                                        :error="
                                            plotGeneratorSaveForm.errors
                                                .language_id
                                        "
                                    />

                                    <p
                                        v-if="
                                            plotGeneratorSaveForm.errors
                                                .language_id
                                        "
                                        class="text-red-500 text-sm mt-1"
                                    >
                                        {{
                                            plotGeneratorSaveForm.errors
                                                .language_id
                                        }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium mb-1"
                                    >
                                        Genres
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <InfiniteScrollApiSelect
                                        :key="`genres-${audienceDependentFieldsKey}`"
                                        :form="plotGeneratorSaveForm"
                                        fieldName="genre_ids"
                                        :selectedItem="
                                            audienceDependentFieldsReset
                                                ? null
                                                : props.novel?.genres
                                        "
                                        :apiUrl="genresApiUrl"
                                        :multiple="true"
                                        placeholder="Select genres"
                                        :error="
                                            plotGeneratorSaveForm.errors
                                                .genre_ids
                                        "
                                    />

                                    <p
                                        v-if="
                                            plotGeneratorSaveForm.errors
                                                .genre_ids
                                        "
                                        class="text-red-500 text-sm mt-1"
                                    >
                                        {{
                                            plotGeneratorSaveForm.errors
                                                .genre_ids
                                        }}
                                    </p>
                                </div>

                                <div>
                                    <label
                                        class="block text-sm font-medium mb-1"
                                    >
                                        Novel Type
                                        <span class="text-red-500">*</span>
                                    </label>

                                    <InfiniteScrollApiSelect
                                        :key="`novel-type-${audienceDependentFieldsKey}`"
                                        :form="plotGeneratorSaveForm"
                                        fieldName="novel_type_id"
                                        :selectedItem="
                                            audienceDependentFieldsReset
                                                ? null
                                                : props.novel?.novel_type
                                        "
                                        :apiUrl="route('search.novel-types')"
                                        :multiple="false"
                                        placeholder="Select novel types"
                                        :error="
                                            plotGeneratorSaveForm.errors
                                                .novel_type_id
                                        "
                                    />

                                    <p
                                        v-if="
                                            plotGeneratorSaveForm.errors
                                                .novel_type_id
                                        "
                                        class="text-red-500 text-sm mt-1"
                                    >
                                        {{
                                            plotGeneratorSaveForm.errors
                                                .novel_type_id
                                        }}
                                    </p>
                                </div>

                                <div class="md:col-span-2">
                                    <label
                                        class="block text-sm font-medium mb-1"
                                    >
                                        Additional Information
                                    </label>

                                    <textarea
                                        v-model="
                                            plotGeneratorSaveForm.additional_information
                                        "
                                        rows="3"
                                        placeholder="Any additional context or instructions for the AI..."
                                        class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none border-gray-300"
                                    ></textarea>
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-6 pt-2">
                                <label
                                    class="flex items-center gap-2 cursor-pointer"
                                >
                                    <input
                                        type="checkbox"
                                        v-model="
                                            plotGeneratorSaveForm.is_18_plus
                                        "
                                        class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                                    />
                                    <span class="text-sm"> 18+ Content </span>
                                </label>

                                <label
                                    class="flex items-center gap-2 cursor-pointer"
                                    :class="{
                                        'opacity-50':
                                            !plotGeneratorSaveForm.is_18_plus,
                                    }"
                                >
                                    <input
                                        type="checkbox"
                                        v-model="
                                            plotGeneratorSaveForm.enable_mature_content
                                        "
                                        :disabled="
                                            !plotGeneratorSaveForm.is_18_plus
                                        "
                                        class="w-4 h-4 text-blue-600 rounded focus:ring-blue-500"
                                    />
                                    <span class="text-sm">
                                        Enable Mature Content
                                    </span>
                                </label>

                                <p
                                    v-if="
                                        plotGeneratorSaveForm.errors
                                            .enable_mature_content
                                    "
                                    class="text-red-500 text-sm w-full"
                                >
                                    {{
                                        plotGeneratorSaveForm.errors
                                            .enable_mature_content
                                    }}
                                </p>
                            </div>
                        </div>

                        <div
                            class="bg-white border rounded-xl p-5 shadow-sm space-y-4"
                        >
                            <h3
                                class="text-base font-semibold flex items-center gap-2"
                            >
                                <FontAwesomeIcon
                                    icon="brain"
                                    class="text-purple-600"
                                />
                                AI Brain Configuration
                            </h3>

                            <p class="text-sm text-gray-500">
                                Select the AI model that will be used for
                                generating your novel.
                            </p>

                            <div
                                class="border-2 border-dashed border-purple-200 rounded-xl p-4 bg-gradient-to-br from-purple-50 to-blue-50"
                            >
                                <InfiniteScrollApiSelect
                                    :form="plotGeneratorSaveForm"
                                    fieldName="ai_brain_id"
                                    :selectedItem="props.novel?.ai_brain"
                                    :apiUrl="
                                        buildAiBrainSearchUrl(
                                            getStepDefinition(1)
                                                .aiBrainOutputTypeCode,
                                        )
                                    "
                                    :multiple="false"
                                    placeholder="Select AI Brain"
                                    :error="
                                        plotGeneratorSaveForm.errors.ai_brain_id
                                    "
                                    class="ai-brain-select"
                                />
                            </div>

                            <p
                                v-if="plotGeneratorSaveForm.errors.ai_brain_id"
                                class="text-red-500 text-sm"
                            >
                                {{ plotGeneratorSaveForm.errors.ai_brain_id }}
                            </p>
                        </div>
                    </div>

                    <div v-if="activeStep === 2" class="space-y-6">
                        <div
                            class="bg-white border rounded-xl p-5 shadow-sm space-y-4"
                        >
                            <h3
                                class="text-base font-semibold flex items-center gap-2"
                            >
                                <FontAwesomeIcon
                                    icon="globe"
                                    class="text-blue-600"
                                />
                                World Building
                            </h3>

                            <p class="text-sm text-gray-500">
                                Define the world, setting, and rules that will
                                shape your novel.
                            </p>

                            <div
                                class="border-2 border-dashed border-purple-200 rounded-xl p-4 bg-gradient-to-br from-purple-50 to-blue-50"
                            >
                                <InfiniteScrollApiSelect
                                    :form="worldBuildingSaveForm"
                                    fieldName="ai_brain_id"
                                    :selectedItem="
                                        worldBuildingSaveForm.ai_brain_id
                                    "
                                    :apiUrl="
                                        buildAiBrainSearchUrl(
                                            getStepDefinition(2)
                                                .aiBrainOutputTypeCode,
                                        )
                                    "
                                    :multiple="false"
                                    placeholder="Select AI Brain"
                                    :error="
                                        worldBuildingSaveForm.errors.ai_brain_id
                                    "
                                    class="ai-brain-select"
                                />
                            </div>

                            <p
                                v-if="worldBuildingSaveForm.errors.ai_brain_id"
                                class="text-red-500 text-sm"
                            >
                                {{ worldBuildingSaveForm.errors.ai_brain_id }}
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="activeStep === 3"
                        class="flex items-center justify-center h-64"
                    >
                        <div class="text-center space-y-3">
                            <FontAwesomeIcon
                                icon="user"
                                class="text-4xl text-gray-300"
                            />
                            <p class="text-gray-400 text-sm">
                                Characters - Coming Soon
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="activeStep === 4"
                        class="flex items-center justify-center h-64"
                    >
                        <div class="text-center space-y-3">
                            <FontAwesomeIcon
                                icon="book"
                                class="text-4xl text-gray-300"
                            />
                            <p class="text-gray-400 text-sm">
                                Plot & Outline - Coming Soon
                            </p>
                        </div>
                    </div>

                    <div
                        v-if="activeStep === 5"
                        class="flex items-center justify-center h-64"
                    >
                        <div class="text-center space-y-3">
                            <FontAwesomeIcon
                                icon="wand-magic-sparkles"
                                class="text-4xl text-gray-300"
                            />
                            <p class="text-gray-400 text-sm">
                                Review & Generate - Coming Soon
                            </p>
                        </div>
                    </div>
                </div>

                <div
                    class="px-0 py-4 border-t border-gray-200 flex justify-between items-center"
                >
                    <button
                        type="button"
                        @click="goPrev"
                        :disabled="activeStep === 1"
                        class="px-4 py-2 text-sm rounded-md border border-gray-300 hover:bg-gray-50 transition disabled:opacity-40 disabled:cursor-not-allowed flex items-center gap-2"
                    >
                        <FontAwesomeIcon icon="arrow-left" />
                        Previous
                    </button>

                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-400">
                            Step {{ activeStep }} of
                            {{ STEP_DEFINITIONS.length }}
                        </span>
                    </div>

                    <div>
                        <button
                            v-if="activeStep === 1"
                            type="button"
                            @click="submitStep1"
                            :disabled="plotGeneratorSaveForm.processing"
                            class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md flex items-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <FontAwesomeIcon
                                v-if="submittingStep === 1"
                                icon="spinner"
                                spin
                            />
                            <FontAwesomeIcon v-else icon="save" />

                            {{
                                submittingStep === 1
                                    ? "Saving..."
                                    : "Save & Continue"
                            }}
                        </button>

                        <button
                            v-else-if="activeStep === 2"
                            type="button"
                            @click="submitStep2"
                            :disabled="worldBuildingSaveForm.processing"
                            class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md flex items-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <FontAwesomeIcon
                                v-if="submittingStep === 2"
                                icon="spinner"
                                spin
                            />
                            <FontAwesomeIcon v-else icon="check" />

                            {{
                                submittingStep === 2 ? "Saving..." : "Continue"
                            }}
                        </button>

                        <button
                            v-else
                            type="button"
                            @click="goNext"
                            :disabled="
                                !isStepAccessible(activeStep + 1) ||
                                activeStep === 5
                            "
                            class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md flex items-center gap-2 transition disabled:opacity-40 disabled:cursor-not-allowed"
                        >
                            Next
                            <FontAwesomeIcon icon="arrow-right" />
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.ai-brain-select :deep(.multiselect) {
    min-height: 44px;
}

.ai-brain-select :deep(.multiselect__tags) {
    min-height: 44px;
    padding: 8px 40px 0 12px;
    border-color: #a78bfa;
    border-width: 1px;
    border-radius: 0.5rem;
    background: white;
}

.ai-brain-select :deep(.multiselect__single) {
    padding: 4px 0 0 0;
    margin-bottom: 0;
    color: #1f2937;
}

.ai-brain-select :deep(.multiselect__placeholder) {
    padding: 4px 0 0 0;
    color: #9ca3af;
}

.ai-brain-select :deep(.multiselect__select) {
    height: 44px;
}

.ai-brain-select :deep(.multiselect__content-wrapper) {
    border-color: #a78bfa;
}
</style>

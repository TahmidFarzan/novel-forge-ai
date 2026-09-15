<script setup>
import Layout from "@/pages/layouts/AuthLayout.vue";
import InfiniteScrollApiSelect from "@/components/common/multi-select/InfiniteScrollApiSelect.vue";

import {
    AiBrainOutputTypes,
} from "@/composables/useAiBrain";

import { ref, computed, onMounted, nextTick, watch } from "vue";
import { Head, useForm } from "@inertiajs/vue3";

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library as FontAwesomeLibrary } from "@fortawesome/fontawesome-svg-core";
import {
    faSpinner,
    faCheck,
    faLock,
    faArrowLeft,
    faWandMagicSparkles,
    faBrain,
    faLightbulb,
    faUser,
    faGlobe,
    faLocationDot,
    faPeopleGroup,
    faPaw,
    faGear,
    faTimeline,
    faDiagramProject,
    faShuffle,
    faListCheck,
    faComments,
    faBookOpen,
    faFileLines,
} from "@fortawesome/free-solid-svg-icons";

FontAwesomeLibrary.add(
    faSpinner,
    faCheck,
    faLock,
    faArrowLeft,
    faWandMagicSparkles,
    faBrain,
    faLightbulb,
    faUser,
    faGlobe,
    faLocationDot,
    faPeopleGroup,
    faPaw,
    faGear,
    faTimeline,
    faDiagramProject,
    faShuffle,
    faListCheck,
    faComments,
    faBookOpen,
    faFileLines,
);

defineOptions({ layout: Layout });

const createPageTitle = "Create Novel";

const { novel } = defineProps({
    novel: {
        type: Object,
        default: null,
    },
});

const isUpdate = computed(() => !!novel?.slug);

const pageTitle = computed(() => {
    return isUpdate.value ? `Edit ${novel?.name}` : "New Novel";
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
        name: "Foundation Generator",
        icon: "lightbulb",
        description: "Generate the title, subtitle, and initial plot foundation",
        outputField: "plot",
        aiBrainOutputTypeCode: AiBrainOutputTypes.Text,
    },
    {
        id: 2,
        name: "Character Development",
        icon: "user",
        description: "Develop the novel's characters",
        outputField: "characters",
        aiBrainOutputTypeCode: AiBrainOutputTypes.Text,
    },
    {
        id: 3,
        name: "World Bible Generator",
        icon: "globe",
        description: "Build the world bible and setting rules",
        outputField: "world_bible",
        aiBrainOutputTypeCode: AiBrainOutputTypes.Text,
    },
    {
        id: 4,
        name: "Location Generator",
        icon: "location-dot",
        description: "Generate the story locations",
        outputField: "locations",
        aiBrainOutputTypeCode: AiBrainOutputTypes.Text,
    },
    {
        id: 5,
        name: "Faction Generator",
        icon: "people-group",
        description: "Generate the story factions",
        outputField: "factions",
        aiBrainOutputTypeCode: AiBrainOutputTypes.Text,
    },
    {
        id: 6,
        name: "Creature / Being Generator",
        icon: "paw",
        description: "Generate creatures and other beings",
        outputField: "creatures",
        aiBrainOutputTypeCode: AiBrainOutputTypes.Text,
    },
    {
        id: 7,
        name: "Dynamic System Generator",
        icon: "gear",
        description: "Generate the novel's dynamic systems",
        outputField: "systems",
        aiBrainOutputTypeCode: AiBrainOutputTypes.Text,
    },
    {
        id: 8,
        name: "Timeline Generator",
        icon: "timeline",
        description: "Generate the story timeline",
        outputField: "timeline",
        aiBrainOutputTypeCode: AiBrainOutputTypes.Text,
    },
    {
        id: 9,
        name: "Story Structure Generator",
        icon: "diagram-project",
        description: "Generate the story structure",
        outputField: "story_structure",
        aiBrainOutputTypeCode: AiBrainOutputTypes.Text,
    },
    {
        id: 10,
        name: "Twist & Foreshadowing Generator",
        icon: "shuffle",
        description: "Plan twists and foreshadowing",
        outputField: "twists_and_foreshadowing",
        aiBrainOutputTypeCode: AiBrainOutputTypes.Text,
    },
    {
        id: 11,
        name: "Scene Planner",
        icon: "list-check",
        description: "Plan the novel's scenes",
        outputField: "scene_plans",
        aiBrainOutputTypeCode: AiBrainOutputTypes.Text,
    },
    {
        id: 12,
        name: "Dialogue Planner",
        icon: "comments",
        description: "Plan character dialogue",
        outputField: "dialogue_plans",
        aiBrainOutputTypeCode: AiBrainOutputTypes.Text,
    },
    {
        id: 13,
        name: "Chapter Planner",
        icon: "book-open",
        description: "Plan the novel's chapters",
        outputField: "chapter_plan",
        aiBrainOutputTypeCode: AiBrainOutputTypes.Text,
    },
    {
        id: 14,
        name: "Page Planner",
        icon: "file-lines",
        description: "Plan the novel's pages",
        outputField: "page_plan",
        aiBrainOutputTypeCode: AiBrainOutputTypes.Text,
    },
    {
        id: 15,
        name: "Complete Novel Generator",
        icon: "wand-magic-sparkles",
        description: "Generate the complete novel",
        outputField: null,
        aiBrainOutputTypeCode: AiBrainOutputTypes.Text,
    },
];

const getStepDefinition = (stepId) => {
    return STEP_DEFINITIONS.find((item) => item.id === stepId);
};

const activeStep = ref(1);
const completedSteps = ref(new Set());
const submittingStep = ref(null);

const plotGeneratorSaveForm = useForm({
    additional_information: novel?.additional_information ?? null,
    language_id: novel?.language_id ?? null,
    genre_ids: novel?.genres?.map((genre) => genre.id) ?? [],
    novel_type_id: novel?.novel_type_id ?? null,
    audience_id: novel?.audience_id ?? null,
    ai_brain_id: novel?.ai_brain_id ?? null,
});

const charactersForm = useForm({
    ai_brain_id: null,
});

const worldBibleForm = useForm({
    ai_brain_id: null,
});

const locationForm = useForm({
    ai_brain_id: null,
});

const factionForm = useForm({
    ai_brain_id: null,
});

const creatureForm = useForm({
    ai_brain_id: null,
});

const systemForm = useForm({
    ai_brain_id: null,
});

const timelineForm = useForm({
    ai_brain_id: null,
});

const storyStructureForm = useForm({
    ai_brain_id: null,
});

const twistsAndForeshadowingForm = useForm({
    ai_brain_id: null,
});

const scenePlannerForm = useForm({
    ai_brain_id: null,
});

const dialoguePlannerForm = useForm({
    ai_brain_id: null,
});

const chapterPlannerForm = useForm({
    ai_brain_id: null,
});

const pagePlannerForm = useForm({
    ai_brain_id: null,
});

const completeNovelForm = useForm({
    ai_brain_id: null,
});

const STEP_FORMS = {
    1: plotGeneratorSaveForm,
    2: charactersForm,
    3: worldBibleForm,
    4: locationForm,
    5: factionForm,
    6: creatureForm,
    7: systemForm,
    8: timelineForm,
    9: storyStructureForm,
    10: twistsAndForeshadowingForm,
    11: scenePlannerForm,
    12: dialoguePlannerForm,
    13: chapterPlannerForm,
    14: pagePlannerForm,
    15: completeNovelForm,
};

const activeStepDefinition = computed(() => {
    return getStepDefinition(activeStep.value);
});

const activeStepForm = computed(() => {
    return STEP_FORMS[activeStep.value];
});

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

        plotGeneratorSaveForm.language_id = null;
        plotGeneratorSaveForm.genre_ids = [];
        plotGeneratorSaveForm.novel_type_id = null;
        plotGeneratorSaveForm.additional_information = null;

        plotGeneratorSaveForm.clearErrors(
            "language_id",
            "genre_ids",
            "novel_type_id",
            "additional_information",
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

function buildAiBrainSearchUrl(outputTypeCode) {
    if (!outputTypeCode) {
        return route("search.ai-brains");
    }

    return route("search.ai-brains", {
        ai_brain_output_type_code: outputTypeCode,
    });
}

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

    plotGeneratorSaveForm.post(
        route("back-office.novels.generate.foundation"),
        {
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
        },
    );
}

function submitFutureStep() {
    const stepId = activeStep.value;
    const stepForm = STEP_FORMS[stepId];

    if (!stepForm || stepForm.processing) {
        return;
    }

    if (!validateStep(stepId)) {
        return;
    }

    submittingStep.value = stepId;

    completedSteps.value.add(stepId);

    if (stepId < STEP_DEFINITIONS.length) {
        activeStep.value = stepId + 1;
    }

    stepForm.clearErrors();
    submittingStep.value = null;
}

const goToStep = (stepId) => {
    if (isStepAccessible(stepId) || isStepCompleted(stepId)) {
        activeStep.value = stepId;
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
                    <nav class="hidden md:grid md:grid-cols-3 lg:grid-cols-6 gap-x-2 pb-px">
                        <button
                            v-for="step in STEP_DEFINITIONS"
                            :key="step.id"
                            type="button"
                            @click="goToStep(step.id)"
                            :disabled="
                                !isStepAccessible(step.id) &&
                                !isStepCompleted(step.id)
                            "
                            class="flex items-center justify-center gap-2 px-3 py-3 text-sm font-medium text-center border-b-2 transition disabled:opacity-40 disabled:cursor-not-allowed"
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
                                    icon="lightbulb"
                                    class="text-blue-600"
                                />
                                Foundation Configuration
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
                                        :selectedItem="novel?.audience"
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
                                                : novel?.language
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
                                                : novel?.genres
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
                                                : novel?.novel_type
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
                                    :selectedItem="novel?.ai_brain"
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

                    <div v-if="activeStep > 1" class="space-y-6">
                        <div
                            class="bg-white border rounded-xl p-5 shadow-sm space-y-4"
                        >
                            <h3
                                class="text-base font-semibold flex items-center gap-2"
                            >
                                <FontAwesomeIcon
                                    :icon="activeStepDefinition.icon"
                                    class="text-blue-600"
                                />
                                {{ activeStepDefinition.name }}
                            </h3>

                            <p class="text-sm text-gray-500">
                                {{ activeStepDefinition.description }}
                            </p>

                            <div
                                class="border-2 border-dashed border-purple-200 rounded-xl p-4 bg-gradient-to-br from-purple-50 to-blue-50"
                            >
                                <InfiniteScrollApiSelect
                                    :key="activeStep"
                                    :form="activeStepForm"
                                    fieldName="ai_brain_id"
                                    :selectedItem="activeStepForm.ai_brain_id"
                                    :apiUrl="
                                        buildAiBrainSearchUrl(
                                            activeStepDefinition.aiBrainOutputTypeCode,
                                        )
                                    "
                                    :multiple="false"
                                    placeholder="Select AI Brain"
                                    :error="activeStepForm.errors.ai_brain_id"
                                    class="ai-brain-select"
                                />
                            </div>

                            <p
                                v-if="activeStepForm.errors.ai_brain_id"
                                class="text-red-500 text-sm"
                            >
                                {{ activeStepForm.errors.ai_brain_id }}
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
                            <FontAwesomeIcon v-else icon="wand-magic-sparkles" />

                            {{
                                submittingStep === 1
                                    ? "Generating..."
                                    : "Generate Foundation"
                            }}
                        </button>

                        <button
                            v-else
                            type="button"
                            @click="submitFutureStep"
                            :disabled="activeStepForm.processing"
                            class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md flex items-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <FontAwesomeIcon
                                v-if="submittingStep === activeStep"
                                icon="spinner"
                                spin
                            />
                            <FontAwesomeIcon v-else icon="check" />

                            {{
                                submittingStep === activeStep
                                    ? "Saving..."
                                    : activeStep === STEP_DEFINITIONS.length
                                      ? "Complete"
                                      : "Continue"
                            }}
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

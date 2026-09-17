<script setup>
import Layout from "@/pages/layouts/AuthLayout.vue";

import NovelStep1FoundationForm from "@/components/back-office/novel/NovelStep1FoundationForm.vue";
import NovelStep2CharactersForm from "@/components/back-office/novel/NovelStep2CharactersForm.vue";
import NovelStep3WorldBibleForm from "@/components/back-office/novel/NovelStep3WorldBibleForm.vue";
import NovelStep4LocationsForm from "@/components/back-office/novel/NovelStep4LocationsForm.vue";
import NovelStep5FactionsForm from "@/components/back-office/novel/NovelStep5FactionsForm.vue";
import NovelStep6CreatureForm from "@/components/back-office/novel/NovelStep6CreatureForm.vue";
import NovelStep7SystemForm from "@/components/back-office/novel/NovelStep7SystemForm.vue";
import NovelStep8TimelineForm from "@/components/back-office/novel/NovelStep8TimelineForm.vue";
import NovelStep9StoryStructureForm from "@/components/back-office/novel/NovelStep9StoryStructureForm.vue";
import NovelStep10TwistsAndForeshadowingForm from "@/components/back-office/novel/NovelStep10TwistsAndForeshadowingForm.vue";
import NovelStep11ScenePlannerForm from "@/components/back-office/novel/NovelStep11ScenePlannerForm.vue";
import NovelStep12DialoguePlannerForm from "@/components/back-office/novel/NovelStep12DialoguePlannerForm.vue";
import NovelStep13ChapterPlannerForm from "@/components/back-office/novel/NovelStep13ChapterPlannerForm.vue";
import NovelStep14PagePlannerForm from "@/components/back-office/novel/NovelStep14PagePlannerForm.vue";
import NovelStep15CompleteNovelForm from "@/components/back-office/novel/NovelStep15CompleteNovelForm.vue";

import { ref, computed, onMounted, nextTick } from "vue";
import { Head } from "@inertiajs/vue3";

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library as FontAwesomeLibrary } from "@fortawesome/fontawesome-svg-core";
import {
    faSpinner,
    faCheck,
    faLock,
    faArrowLeft,
    faWandMagicSparkles,
} from "@fortawesome/free-solid-svg-icons";

FontAwesomeLibrary.add(
    faSpinner,
    faCheck,
    faLock,
    faArrowLeft,
    faWandMagicSparkles,
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
    { id: 1, name: "Foundation Generator" },
    { id: 2, name: "Character Development" },
    { id: 3, name: "World Bible Generator" },
    { id: 4, name: "Location Generator" },
    { id: 5, name: "Faction Generator" },
    { id: 6, name: "Creature / Being Generator" },
    { id: 7, name: "Dynamic System Generator" },
    { id: 8, name: "Timeline Generator" },
    { id: 9, name: "Story Structure Generator" },
    { id: 10, name: "Twist & Foreshadowing Generator" },
    { id: 11, name: "Scene Planner" },
    { id: 12, name: "Dialogue Planner" },
    { id: 13, name: "Chapter Planner" },
    { id: 14, name: "Page Planner" },
    { id: 15, name: "Complete Novel Generator" },
];

const activeStep = ref(1);
const completedSteps = ref(new Set());
const submittingStep = ref(null);
const activeStepComponentRef = ref(null);

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

const handleStepCompleted = (stepId) => {
    completedSteps.value.add(stepId);

    if (stepId < STEP_DEFINITIONS.length) {
        activeStep.value = stepId + 1;
    }
};

const handleSubmitting = (stepId) => {
    submittingStep.value = stepId;
};

const handleFinished = () => {
    submittingStep.value = null;
};

const submitActiveStep = () => {
    activeStepComponentRef.value?.submit();
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
                    <nav
                        class="hidden md:grid md:grid-cols-3 lg:grid-cols-6 gap-x-2 pb-px"
                    >
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
                    <NovelStep1FoundationForm
                        v-if="activeStep === 1"
                        ref="activeStepComponentRef"
                        :novel="novel"
                        @submitting="handleSubmitting(1)"
                        @completed="handleStepCompleted(1)"
                        @finished="handleFinished"
                    />

                    <NovelStep2CharactersForm
                        v-else-if="activeStep === 2"
                        ref="activeStepComponentRef"
                        :novel="novel"
                        @completed="handleStepCompleted(2)"
                    />

                    <NovelStep3WorldBibleForm
                        v-else-if="activeStep === 3"
                        ref="activeStepComponentRef"
                        :novel="novel"
                        @completed="handleStepCompleted(3)"
                    />

                    <NovelStep4LocationsForm
                        v-else-if="activeStep === 4"
                        ref="activeStepComponentRef"
                        :novel="novel"
                        @completed="handleStepCompleted(4)"
                    />

                    <NovelStep5FactionsForm
                        v-else-if="activeStep === 5"
                        ref="activeStepComponentRef"
                        :novel="novel"
                        @completed="handleStepCompleted(5)"
                    />

                    <NovelStep6CreatureForm
                        v-else-if="activeStep === 6"
                        ref="activeStepComponentRef"
                        :novel="novel"
                        @completed="handleStepCompleted(6)"
                    />

                    <NovelStep7SystemForm
                        v-else-if="activeStep === 7"
                        ref="activeStepComponentRef"
                        :novel="novel"
                        @completed="handleStepCompleted(7)"
                    />

                    <NovelStep8TimelineForm
                        v-else-if="activeStep === 8"
                        ref="activeStepComponentRef"
                        :novel="novel"
                        @completed="handleStepCompleted(8)"
                    />

                    <NovelStep9StoryStructureForm
                        v-else-if="activeStep === 9"
                        ref="activeStepComponentRef"
                        :novel="novel"
                        @completed="handleStepCompleted(9)"
                    />

                    <NovelStep10TwistsAndForeshadowingForm
                        v-else-if="activeStep === 10"
                        ref="activeStepComponentRef"
                        :novel="novel"
                        @completed="handleStepCompleted(10)"
                    />

                    <NovelStep11ScenePlannerForm
                        v-else-if="activeStep === 11"
                        ref="activeStepComponentRef"
                        :novel="novel"
                        @completed="handleStepCompleted(11)"
                    />

                    <NovelStep12DialoguePlannerForm
                        v-else-if="activeStep === 12"
                        ref="activeStepComponentRef"
                        :novel="novel"
                        @completed="handleStepCompleted(12)"
                    />

                    <NovelStep13ChapterPlannerForm
                        v-else-if="activeStep === 13"
                        ref="activeStepComponentRef"
                        :novel="novel"
                        @completed="handleStepCompleted(13)"
                    />

                    <NovelStep14PagePlannerForm
                        v-else-if="activeStep === 14"
                        ref="activeStepComponentRef"
                        :novel="novel"
                        @completed="handleStepCompleted(14)"
                    />

                    <NovelStep15CompleteNovelForm
                        v-else-if="activeStep === 15"
                        ref="activeStepComponentRef"
                        :novel="novel"
                        @completed="handleStepCompleted(15)"
                    />
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
                            @click="submitActiveStep"
                            :disabled="submittingStep === 1"
                            class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md flex items-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <FontAwesomeIcon
                                v-if="submittingStep === 1"
                                icon="spinner"
                                spin
                            />
                            <FontAwesomeIcon
                                v-else
                                icon="wand-magic-sparkles"
                            />

                            {{
                                submittingStep === 1
                                    ? "Generating..."
                                    : "Generate Foundation"
                            }}
                        </button>

                        <button
                            v-else
                            type="button"
                            @click="submitActiveStep"
                            :disabled="submittingStep === activeStep"
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

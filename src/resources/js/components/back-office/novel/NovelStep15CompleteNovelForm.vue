<script setup>
import InfiniteScrollApiSelect from "@/components/common/multi-select/InfiniteScrollApiSelect.vue";
import { AiBrainOutputTypes } from "@/composables/useAiBrain";

import { computed } from "vue";
import { useForm, router as inertiaRoute } from "@inertiajs/vue3";

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library as FontAwesomeLibrary } from "@fortawesome/fontawesome-svg-core";
import {
    faBrain,
    faSpinner,
    faWandMagicSparkles,
} from "@fortawesome/free-solid-svg-icons";

FontAwesomeLibrary.add(faBrain, faSpinner, faWandMagicSparkles);

const emit = defineEmits(["completed"]);

const { novel } = defineProps({
    novel: {
        type: Object,
        required: true,
    },
});

const isUpdate = computed(() => !!novel?.id);

const serializeField = (name) => {
    const value = novel?.[name];
    return value
        ? typeof value === "string"
            ? value
            : JSON.stringify(value)
        : null;
};

const completeNovelForm = useForm({
    foundation: serializeField("foundation"),
    characters: serializeField("characters"),
    world_bible: serializeField("world_bible"),
    locations: serializeField("locations"),
    factions: serializeField("factions"),
    creatures: serializeField("creatures"),
    systems: serializeField("systems"),
    timeline: serializeField("timeline"),
    story_structure: serializeField("story_structure"),
    twists_and_foreshadowing: serializeField("twists_and_foreshadowing"),
    scene_plans: serializeField("scene_plans"),
    dialogue_plans: serializeField("dialogue_plans"),
    chapter_plan: serializeField("chapter_plan"),
    page_plan: serializeField("page_plan"),
    additional_information: null,
    ai_brain_id: null,
});

function buildAiBrainSearchUrl() {
    return route("search.ai-brains", {
        ai_brain_output_type_code: AiBrainOutputTypes.Text,
    });
}

const dependencyFields = [
    "foundation",
    "characters",
    "world_bible",
    "locations",
    "factions",
    "creatures",
    "systems",
    "timeline",
    "story_structure",
    "twists_and_foreshadowing",
    "scene_plans",
    "dialogue_plans",
    "chapter_plan",
    "page_plan",
];

const validate = () => {
    completeNovelForm.clearErrors();

    let valid = true;

    if (!completeNovelForm.ai_brain_id) {
        completeNovelForm.setError(
            "ai_brain_id",
            "AI Brain selection is required",
        );
        valid = false;
    }

    dependencyFields.forEach((field) => {
        if (!completeNovelForm[field]) {
            const label = field
                .split("_")
                .map((word) => word[0].toUpperCase() + word.slice(1))
                .join(" ");
            completeNovelForm.setError(field, `${label} is required`);
            valid = false;
        }
    });

    return valid;
};

const handleSuccess = () => {
    completeNovelForm.clearErrors();
    emit("completed", novel);
};

const submit = () => {
    if (!isUpdate.value || completeNovelForm.processing || !validate()) {
        return;
    }

    const requestConfig = {
        preserveScroll: true,
        preserveState: true,
        onSuccess: handleSuccess,
    };

    inertiaRoute.patch(
        route("back-office.novels.generate.complete-novel", {
            slug: novel?.slug,
        }),
        {
            ...completeNovelForm.data(),
            _method: "patch",
        },
        requestConfig,
    );
};

defineExpose({ submit });
</script>

<template>
    <div class="space-y-6">
        <div class="bg-white border rounded-xl p-5 shadow-sm space-y-4">
            <h3 class="text-base font-semibold flex items-center gap-2">
                <FontAwesomeIcon icon="wand-magic-sparkles" class="text-blue-600" />
                Complete Novel Generator
            </h3>

            <p class="text-sm text-gray-500">
                Generate the complete novel
            </p>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Complete Novel Additional Information
                </label>

                <textarea v-model="completeNovelForm.additional_information" rows="3"
                    placeholder="Any additional context or instructions for the AI..."
                    class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none border-gray-300"></textarea>

                <p v-if="completeNovelForm.errors.additional_information">
                    {{ completeNovelForm.errors.additional_information }}
                </p>
            </div>

            <p v-for="field in dependencyFields" :key="field"
                v-if="completeNovelForm.errors[field]" class="text-red-500 text-sm">
                {{ completeNovelForm.errors[field] }}
            </p>
        </div>

        <div class="bg-white border rounded-xl p-5 shadow-sm space-y-4">
            <div class="flex items-center gap-2">
                <FontAwesomeIcon icon="brain" class="text-purple-600" />
                <h3 class="text-base font-semibold">AI Brain Configuration</h3>
            </div>

            <p class="text-sm text-gray-500">
                Select the AI model that will generate the complete novel.
            </p>

            <div class="border-2 border-dashed border-purple-200 rounded-xl p-4 bg-gradient-to-br from-purple-50 to-blue-50" >
                <InfiniteScrollApiSelect :form="completeNovelForm" fieldName="ai_brain_id"
                    :selectedItem="null" :apiUrl="buildAiBrainSearchUrl()" :multiple="false"
                    placeholder="Select AI Brain" :error="completeNovelForm.errors.ai_brain_id"
                    class="ai-brain-select"/>
            </div>

            <p v-if="completeNovelForm.errors.ai_brain_id" class="text-red-500 text-sm">
                {{ completeNovelForm.errors.ai_brain_id }}
            </p>
        </div>

        <div class="flex justify-end">
            <button type="button" @click="submit" :disabled="!isUpdate || completeNovelForm.processing"
                class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md flex items-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed">
                <FontAwesomeIcon v-if="completeNovelForm.processing" icon="spinner" spin/>
                <FontAwesomeIcon v-else icon="wand-magic-sparkles" />
                {{ completeNovelForm.processing ? "Generating..." : "Generate Complete Novel" }}
            </button>
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
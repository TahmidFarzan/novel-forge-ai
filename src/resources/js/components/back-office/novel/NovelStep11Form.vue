<script setup>
import InfiniteScrollApiSelect from "@/components/common/multi-select/InfiniteScrollApiSelect.vue";
import { AiBrainOutputTypes } from "@/composables/useAiBrain";

import { computed } from "vue";
import { useForm, router as inertiaRoute } from "@inertiajs/vue3";

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library as FontAwesomeLibrary } from "@fortawesome/fontawesome-svg-core";
import {
    faBrain,
    faListCheck,
    faSpinner,
    faWandMagicSparkles,
} from "@fortawesome/free-solid-svg-icons";

FontAwesomeLibrary.add(faBrain, faListCheck, faSpinner, faWandMagicSparkles);

const emit = defineEmits(["completed"]);

const { novel } = defineProps({
    novel: {
        type: Object,
        required: true,
    },
});

const isUpdate = computed(() => !!novel?.id);

const scenePlansForm = useForm({
    story_structure: novel?.story_structure
        ? typeof novel.story_structure === "string"
            ? novel.story_structure
            : JSON.stringify(novel.story_structure)
        : null,
    twists_and_foreshadowing: novel?.twists_and_foreshadowing
        ? typeof novel.twists_and_foreshadowing === "string"
            ? novel.twists_and_foreshadowing
            : JSON.stringify(novel.twists_and_foreshadowing)
        : null,
    locations: novel?.locations
        ? typeof novel.locations === "string"
            ? novel.locations
            : JSON.stringify(novel.locations)
        : null,
    ai_brain_id: null,
});

function buildAiBrainSearchUrl() {
    return route("search.ai-brains", {
        ai_brain_output_type_code: AiBrainOutputTypes.Text,
    });
}

const validate = () => {
    scenePlansForm.clearErrors();

    let valid = true;

    if (!scenePlansForm.ai_brain_id) {
        scenePlansForm.setError(
            "ai_brain_id",
            "AI Brain selection is required",
        );
        valid = false;
    }

    if (!scenePlansForm.story_structure) {
        scenePlansForm.setError("story_structure", "Story Structure is required");
        valid = false;
    }

    if (!scenePlansForm.twists_and_foreshadowing) {
        scenePlansForm.setError("twists_and_foreshadowing", "Twists and Foreshadowing is required");
        valid = false;
    }

    if (!scenePlansForm.locations) {
        scenePlansForm.setError("locations", "Locations are required");
        valid = false;
    }

    return valid;
};

const handleSuccess = () => {
    scenePlansForm.clearErrors();
    emit("completed", novel);
};

const submit = () => {
    if (!isUpdate.value || scenePlansForm.processing || !validate()) {
        return;
    }

    const requestConfig = {
        preserveScroll: true,
        preserveState: true,
        onSuccess: handleSuccess,
    };

    inertiaRoute.patch(
        route("back-office.novels.generate.scene-planner", {
            slug: novel?.slug,
        }),
        {
            ...scenePlansForm.data(),
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
                <FontAwesomeIcon icon="list-check" class="text-blue-600" />
                Scene Planner
            </h3>

            <p class="text-sm text-gray-500">
                Plan the novel's scenes
            </p>

            <p v-if="scenePlansForm.errors.story_structure" class="text-red-500 text-sm">
                {{ scenePlansForm.errors.story_structure }}
            </p>

            <p v-if="scenePlansForm.errors.twists_and_foreshadowing" class="text-red-500 text-sm">
                {{ scenePlansForm.errors.twists_and_foreshadowing }}
            </p>

            <p v-if="scenePlansForm.errors.locations" class="text-red-500 text-sm">
                {{ scenePlansForm.errors.locations }}
            </p>
        </div>

        <div class="bg-white border rounded-xl p-5 shadow-sm space-y-4">
            <div class="flex items-center gap-2">
                <FontAwesomeIcon icon="brain" class="text-purple-600" />
                <h3 class="text-base font-semibold">AI Brain Configuration</h3>
            </div>

            <p class="text-sm text-gray-500">
                Select the AI model that will generate the scene plans.
            </p>

            <div class="border-2 border-dashed border-purple-200 rounded-xl p-4 bg-gradient-to-br from-purple-50 to-blue-50" >
                <InfiniteScrollApiSelect :form="scenePlansForm" fieldName="ai_brain_id"
                    :selectedItem="null" :apiUrl="buildAiBrainSearchUrl()" :multiple="false"
                    placeholder="Select AI Brain" :error="scenePlansForm.errors.ai_brain_id"
                    class="ai-brain-select"/>
            </div>

            <p v-if="scenePlansForm.errors.ai_brain_id" class="text-red-500 text-sm">
                {{ scenePlansForm.errors.ai_brain_id }}
            </p>
        </div>

        <div class="flex justify-end">
            <button type="button" @click="submit" :disabled="!isUpdate || scenePlansForm.processing"
                class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md flex items-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed">
                <FontAwesomeIcon v-if="scenePlansForm.processing" icon="spinner" spin/>
                <FontAwesomeIcon v-else icon="wand-magic-sparkles" />
                {{ scenePlansForm.processing ? "Generating..." : "Generate Scene Plans" }}
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
<script setup>
import InfiniteScrollApiSelect from "@/components/common/multi-select/InfiniteScrollApiSelect.vue";
import { AiBrainOutputTypes } from "@/composables/useAiBrain";

import { computed } from "vue";
import { useForm, router as inertiaRoute } from "@inertiajs/vue3";

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library as FontAwesomeLibrary } from "@fortawesome/fontawesome-svg-core";
import {
    faBookOpen,
    faBrain,
    faSpinner,
    faWandMagicSparkles,
} from "@fortawesome/free-solid-svg-icons";

FontAwesomeLibrary.add(faBookOpen, faBrain, faSpinner, faWandMagicSparkles);

const emit = defineEmits(["completed"]);

const { novel } = defineProps({
    novel: {
        type: Object,
        required: true,
    },
});

const isUpdate = computed(() => !!novel?.id);

const chapterPlanForm = useForm({
    scene_plans: novel?.scene_plans
        ? typeof novel.scene_plans === "string"
            ? novel.scene_plans
            : JSON.stringify(novel.scene_plans)
        : null,
    story_structure: novel?.story_structure
        ? typeof novel.story_structure === "string"
            ? novel.story_structure
            : JSON.stringify(novel.story_structure)
        : null,
    ai_brain_id: null,
});

function buildAiBrainSearchUrl() {
    return route("search.ai-brains", {
        ai_brain_output_type_code: AiBrainOutputTypes.Text,
    });
}

const validate = () => {
    chapterPlanForm.clearErrors();

    let valid = true;

    if (!chapterPlanForm.ai_brain_id) {
        chapterPlanForm.setError(
            "ai_brain_id",
            "AI Brain selection is required",
        );
        valid = false;
    }

    if (!chapterPlanForm.scene_plans) {
        chapterPlanForm.setError("scene_plans", "Scene Plans are required");
        valid = false;
    }

    if (!chapterPlanForm.story_structure) {
        chapterPlanForm.setError("story_structure", "Story Structure is required");
        valid = false;
    }

    return valid;
};

const handleSuccess = () => {
    chapterPlanForm.clearErrors();
    emit("completed", novel);
};

const submit = () => {
    if (!isUpdate.value || chapterPlanForm.processing || !validate()) {
        return;
    }

    const requestConfig = {
        preserveScroll: true,
        preserveState: true,
        onSuccess: handleSuccess,
    };

    inertiaRoute.patch(
        route("back-office.novels.generate.chapter-planner", {
            slug: novel?.slug,
        }),
        {
            ...chapterPlanForm.data(),
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
                <FontAwesomeIcon icon="book-open" class="text-blue-600" />
                Chapter Planner
            </h3>

            <p class="text-sm text-gray-500">
                Plan the novel's chapters
            </p>

            <p v-if="chapterPlanForm.errors.scene_plans" class="text-red-500 text-sm">
                {{ chapterPlanForm.errors.scene_plans }}
            </p>

            <p v-if="chapterPlanForm.errors.story_structure" class="text-red-500 text-sm">
                {{ chapterPlanForm.errors.story_structure }}
            </p>
        </div>

        <div class="bg-white border rounded-xl p-5 shadow-sm space-y-4">
            <div class="flex items-center gap-2">
                <FontAwesomeIcon icon="brain" class="text-purple-600" />
                <h3 class="text-base font-semibold">AI Brain Configuration</h3>
            </div>

            <p class="text-sm text-gray-500">
                Select the AI model that will generate the chapter plan.
            </p>

            <div class="border-2 border-dashed border-purple-200 rounded-xl p-4 bg-gradient-to-br from-purple-50 to-blue-50" >
                <InfiniteScrollApiSelect :form="chapterPlanForm" fieldName="ai_brain_id"
                    :selectedItem="null" :apiUrl="buildAiBrainSearchUrl()" :multiple="false"
                    placeholder="Select AI Brain" :error="chapterPlanForm.errors.ai_brain_id"
                    class="ai-brain-select"/>
            </div>

            <p v-if="chapterPlanForm.errors.ai_brain_id" class="text-red-500 text-sm">
                {{ chapterPlanForm.errors.ai_brain_id }}
            </p>
        </div>

        <div class="flex justify-end">
            <button type="button" @click="submit" :disabled="!isUpdate || chapterPlanForm.processing"
                class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md flex items-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed">
                <FontAwesomeIcon v-if="chapterPlanForm.processing" icon="spinner" spin/>
                <FontAwesomeIcon v-else icon="wand-magic-sparkles" />
                {{ chapterPlanForm.processing ? "Generating..." : "Generate Chapter Plan" }}
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
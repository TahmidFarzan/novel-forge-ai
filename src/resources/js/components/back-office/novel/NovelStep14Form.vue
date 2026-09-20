<script setup>
import InfiniteScrollApiSelect from "@/components/common/multi-select/InfiniteScrollApiSelect.vue";
import { AiBrainOutputTypes } from "@/composables/useAiBrain";

import { computed } from "vue";
import { useForm, router as inertiaRoute } from "@inertiajs/vue3";

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library as FontAwesomeLibrary } from "@fortawesome/fontawesome-svg-core";
import {
    faBrain,
    faFileLines,
    faSpinner,
    faWandMagicSparkles,
} from "@fortawesome/free-solid-svg-icons";

FontAwesomeLibrary.add(faBrain, faFileLines, faSpinner, faWandMagicSparkles);

const emit = defineEmits(["completed"]);

const { novel } = defineProps({
    novel: {
        type: Object,
        required: true,
    },
});

const isUpdate = computed(() => !!novel?.id);

const pagePlanForm = useForm({
    chapter_plan: novel?.chapter_plan
        ? typeof novel.chapter_plan === "string"
            ? novel.chapter_plan
            : JSON.stringify(novel.chapter_plan)
        : null,
    scene_plans: novel?.scene_plans
        ? typeof novel.scene_plans === "string"
            ? novel.scene_plans
            : JSON.stringify(novel.scene_plans)
        : null,
    additional_information: null,
    ai_brain_id: null,
});

function buildAiBrainSearchUrl() {
    return route("search.ai-brains", {
        ai_brain_output_type_code: AiBrainOutputTypes.Text,
    });
}

const validate = () => {
    pagePlanForm.clearErrors();

    let valid = true;

    if (!pagePlanForm.ai_brain_id) {
        pagePlanForm.setError(
            "ai_brain_id",
            "AI Brain selection is required",
        );
        valid = false;
    }

    if (!pagePlanForm.chapter_plan) {
        pagePlanForm.setError("chapter_plan", "Chapter Plan is required");
        valid = false;
    }

    if (!pagePlanForm.scene_plans) {
        pagePlanForm.setError("scene_plans", "Scene Plans are required");
        valid = false;
    }

    return valid;
};

const handleSuccess = () => {
    pagePlanForm.clearErrors();
    emit("completed", novel);
};

const submit = () => {
    if (!isUpdate.value || pagePlanForm.processing || !validate()) {
        return;
    }

    const requestConfig = {
        preserveScroll: true,
        preserveState: true,
        onSuccess: handleSuccess,
    };

    inertiaRoute.patch(
        route("back-office.novels.generate.page-planner", {
            slug: novel?.slug,
        }),
        {
            ...pagePlanForm.data(),
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
                <FontAwesomeIcon icon="file-lines" class="text-blue-600" />
                Page Planner
            </h3>

            <p class="text-sm text-gray-500">
                Plan the novel's pages
            </p>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Page Plan Additional Information
                </label>

                <textarea v-model="pagePlanForm.additional_information" rows="3"
                    placeholder="Any additional context or instructions for the AI..."
                    class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none border-gray-300"></textarea>

                <p v-if="pagePlanForm.errors.additional_information">
                    {{ pagePlanForm.errors.additional_information }}
                </p>
            </div>

            <p v-if="pagePlanForm.errors.chapter_plan" class="text-red-500 text-sm">
                {{ pagePlanForm.errors.chapter_plan }}
            </p>

            <p v-if="pagePlanForm.errors.scene_plans" class="text-red-500 text-sm">
                {{ pagePlanForm.errors.scene_plans }}
            </p>
        </div>

        <div class="bg-white border rounded-xl p-5 shadow-sm space-y-4">
            <div class="flex items-center gap-2">
                <FontAwesomeIcon icon="brain" class="text-purple-600" />
                <h3 class="text-base font-semibold">AI Brain Configuration</h3>
            </div>

            <p class="text-sm text-gray-500">
                Select the AI model that will generate the page plan.
            </p>

            <div class="border-2 border-dashed border-purple-200 rounded-xl p-4 bg-gradient-to-br from-purple-50 to-blue-50" >
                <InfiniteScrollApiSelect :form="pagePlanForm" fieldName="ai_brain_id"
                    :selectedItem="null" :apiUrl="buildAiBrainSearchUrl()" :multiple="false"
                    placeholder="Select AI Brain" :error="pagePlanForm.errors.ai_brain_id"
                    class="ai-brain-select"/>
            </div>

            <p v-if="pagePlanForm.errors.ai_brain_id" class="text-red-500 text-sm">
                {{ pagePlanForm.errors.ai_brain_id }}
            </p>
        </div>

        <div class="flex justify-end">
            <button type="button" @click="submit" :disabled="!isUpdate || pagePlanForm.processing"
                class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md flex items-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed">
                <FontAwesomeIcon v-if="pagePlanForm.processing" icon="spinner" spin/>
                <FontAwesomeIcon v-else icon="wand-magic-sparkles" />
                {{ pagePlanForm.processing ? "Generating..." : "Generate Page Plan" }}
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
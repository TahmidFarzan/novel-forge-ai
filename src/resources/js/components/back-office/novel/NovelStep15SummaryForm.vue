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

const chapters = computed(() => {
    return Array.isArray(novel?.novel_chapters) ? novel.novel_chapters : [];
});

const chapterPlanEntries = computed(() => {
    return Array.isArray(novel?.chapter_plan) ? novel.chapter_plan : [];
});

const totalChapters = computed(() => {
    return chapterPlanEntries.value.length;
});

const chaptersWithSummary = computed(() => {
    return chapters.value.filter((chapter) => !!chapter?.summery && String(chapter.summery).trim() !== "");
});

const summaryProgressPercent = computed(() => {
    if (totalChapters.value === 0) {
        return 0;
    }

    return Math.round((chaptersWithSummary.value.length / totalChapters.value) * 100);
});

const summariesComplete = computed(() => {
    return totalChapters.value > 0 && chaptersWithSummary.value.length === totalChapters.value;
});

const serializeField = (name) => {
    const value = novel?.[name];
    return value
        ? typeof value === "string"
            ? value
            : JSON.stringify(value)
        : null;
};

const chapterSummaryForm = useForm({
    chapter_plan: serializeField("chapter_plan"),
    ai_brain_id: null,
});

function buildAiBrainSearchUrl() {
    return route("search.ai-brains", {
        ai_brain_output_type_code: AiBrainOutputTypes.Text,
    });
}

const dependencyFields = ["chapter_plan"];

const validate = () => {
    chapterSummaryForm.clearErrors();

    let valid = true;

    if (!chapterSummaryForm.ai_brain_id) {
        chapterSummaryForm.setError(
            "ai_brain_id",
            "AI Brain selection is required",
        );
        valid = false;
    }

    dependencyFields.forEach((field) => {
        if (!chapterSummaryForm[field]) {
            const label = field
                .split("_")
                .map((word) => word[0].toUpperCase() + word.slice(1))
                .join(" ");
            chapterSummaryForm.setError(field, `${label} is required`);
            valid = false;
        }
    });

    return valid;
};

const handleSuccess = () => {
    chapterSummaryForm.clearErrors();
    emit("completed", novel);
};

const submit = () => {
    if (!isUpdate.value || chapterSummaryForm.processing || !validate()) {
        return;
    }

    const requestConfig = {
        preserveScroll: true,
        preserveState: true,
        onSuccess: handleSuccess,
    };

    inertiaRoute.patch(
        route("back-office.novels.generate.chapter-summaries", {
            slug: novel?.slug,
        }),
        {
            ...chapterSummaryForm.data(),
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
                Complete Novel - Chapter Summary Generator
            </h3>

            <p class="text-sm text-gray-500">
                Generate a summary for every planned chapter of the novel. Each summary is saved to its corresponding novel chapter record.
            </p>

            <div class="rounded-md border border-gray-200 p-4 space-y-2">
                <div class="flex items-center justify-between text-sm">
                    <p class="text-gray-500">
                        Chapter Summaries
                        <span class="font-semibold text-blue-600">{{ chaptersWithSummary.length }}</span>
                        /
                        {{ totalChapters }} completed
                    </p>

                    <p class="text-gray-400">
                        {{ totalChapters - chaptersWithSummary.length }} remaining
                    </p>
                </div>

                <div class="w-full h-2 rounded-full bg-gray-200 overflow-hidden">
                    <div class="h-full bg-blue-600 transition-all"
                        :style="{ width: summaryProgressPercent + '%' }"></div>
                </div>

                <p v-if="summariesComplete" class="text-xs text-emerald-600">
                    All chapter summaries have been generated. You can now proceed to Chapter Content Generation.
                </p>
            </div>

            <p v-for="field in dependencyFields" :key="field"
                v-if="chapterSummaryForm.errors[field]" class="text-red-500 text-sm">
                {{ chapterSummaryForm.errors[field] }}
            </p>
        </div>

        <div class="bg-white border rounded-xl p-5 shadow-sm space-y-4">
            <div class="flex items-center gap-2">
                <FontAwesomeIcon icon="brain" class="text-purple-600" />
                <h3 class="text-base font-semibold">AI Brain Configuration</h3>
            </div>

            <p class="text-sm text-gray-500">
                Select the AI model that will generate the chapter summaries.
            </p>

            <div class="border-2 border-dashed border-purple-200 rounded-xl p-4 bg-gradient-to-br from-purple-50 to-blue-50" >
                <InfiniteScrollApiSelect :form="chapterSummaryForm" fieldName="ai_brain_id"
                    :selectedItem="null" :apiUrl="buildAiBrainSearchUrl()" :multiple="false"
                    placeholder="Select AI Brain" :error="chapterSummaryForm.errors.ai_brain_id"
                    class="ai-brain-select"/>
            </div>

            <p v-if="chapterSummaryForm.errors.ai_brain_id" class="text-red-500 text-sm">
                {{ chapterSummaryForm.errors.ai_brain_id }}
            </p>
        </div>

        <div class="flex justify-end">
            <button type="button" @click="submit" :disabled="!isUpdate || chapterSummaryForm.processing"
                class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md flex items-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed">
                <FontAwesomeIcon v-if="chapterSummaryForm.processing" icon="spinner" spin/>
                <FontAwesomeIcon v-else icon="wand-magic-sparkles" />
                {{ chapterSummaryForm.processing ? "Generating Chapter Summaries..." : "Generate Chapter Summaries" }}
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
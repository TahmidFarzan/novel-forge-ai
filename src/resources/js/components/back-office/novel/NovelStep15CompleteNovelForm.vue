<script setup>
import NovelStep15ChapterSummaryForm from "@/components/back-office/novel/NovelStep15ChapterSummaryForm.vue";
import NovelStep15ChapterContentForm from "@/components/back-office/novel/NovelStep15ChapterContentForm.vue";

import { ref, computed } from "vue";

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library as FontAwesomeLibrary } from "@fortawesome/fontawesome-svg-core";
import {
    faBookOpen,
    faCheck,
    faFeatherPointed,
} from "@fortawesome/free-solid-svg-icons";

FontAwesomeLibrary.add(faBookOpen, faCheck, faFeatherPointed);

const { novel } = defineProps({
    novel: {
        type: Object,
        required: true,
    },
});

const currentTab = ref("summaries");
const tabRef = ref(null);

const hasStringValue = (value) => {
    return !!value && typeof value === "string" && value.trim() !== "";
};

const chapters = computed(() => {
    return Array.isArray(novel?.novel_chapters) ? novel.novel_chapters : [];
});

const isNovelCompleted = computed(() => {
    return novel?.status === "Complete";
});

const summariesGenerated = computed(() => {
    return chapters.value.every((chapter) => hasStringValue(chapter?.summery));
});

const contentsGenerated = computed(() => {
    return chapters.value.every((chapter) => hasStringValue(chapter?.content));
});

const isContentsAccessible = computed(() => {
    return summariesGenerated.value || contentsGenerated.value;
});

const setTab = (tab) => {
    if (tab === "contents" && !isContentsAccessible.value) {
        return;
    }

    currentTab.value = tab;
};

const handleSummariesCompleted = () => {
    if (summariesGenerated.value) {
        currentTab.value = "contents";
    }
};

const submit = () => {
    tabRef.value?.submit?.();
};

defineExpose({ submit });
</script>

<template>
    <div class="space-y-6">
        <div class="bg-white border rounded-xl p-5 shadow-sm space-y-4">
            <h3 class="text-base font-semibold flex items-center gap-2">
                <FontAwesomeIcon icon="book-open" class="text-blue-600" />
                Complete Novel
            </h3>

            <p class="text-sm text-gray-500">
                Build the novel one chapter at a time: generate chapter summaries first, then generate each chapter content independently until the final novel is completed.
            </p>

            <div v-if="isNovelCompleted"
                class="flex items-center gap-2 rounded-md border border-green-200 bg-green-50 px-3 py-2 text-sm text-green-700">
                <FontAwesomeIcon icon="check" />
                Final novel completed. The novel is composed of its ordered chapter records.
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <button type="button" @click="setTab('summaries')"
                    class="flex items-center gap-3 rounded-xl border p-4 text-left transition"
                    :class="currentTab === 'summaries'
                        ? 'border-blue-600 bg-blue-50'
                        : 'border-gray-200 hover:border-gray-300'">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold shrink-0"
                        :class="currentTab === 'summaries' ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-600'">
                        <FontAwesomeIcon v-if="summariesGenerated" icon="check" class="text-xs" />
                        <span v-else>1</span>
                    </span>

                    <span class="flex-1">
                        <span class="block text-sm font-semibold text-gray-800">Chapter Summary Generation</span>
                        <span class="block text-xs text-gray-500">Generate a summary for every planned chapter.</span>
                    </span>
                </button>

                <button type="button" @click="setTab('contents')" :disabled="!isContentsAccessible"
                    class="flex items-center gap-3 rounded-xl border p-4 text-left transition disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="currentTab === 'contents'
                        ? 'border-emerald-600 bg-emerald-50'
                        : 'border-gray-200 hover:border-gray-300'">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold shrink-0"
                        :class="currentTab === 'contents' ? 'bg-emerald-600 text-white' : 'bg-gray-200 text-gray-600'">
                        <FontAwesomeIcon v-if="contentsGenerated" icon="check" class="text-xs" />
                        <span v-else>2</span>
                    </span>

                    <span class="flex-1">
                        <span class="block text-sm font-semibold text-gray-800">Chapter Content Generation</span>
                        <span class="block text-xs text-gray-500">Generate the prose for every chapter independently.</span>
                    </span>
                </button>
            </div>

            <p v-if="!isContentsAccessible" class="text-xs text-gray-400">
                Generate all chapter summaries first to unlock Chapter Content Generation.
            </p>
        </div>

        <NovelStep15ChapterSummaryForm
            v-if="currentTab === 'summaries'"
            ref="tabRef"
            :novel="novel"
            @completed="handleSummariesCompleted"
        />

        <NovelStep15ChapterContentForm
            v-else
            ref="tabRef"
            :novel="novel"
        />
    </div>
</template>
<script setup>
import InfiniteScrollApiSelect from "@/components/common/multi-select/InfiniteScrollApiSelect.vue";
import { AiBrainOutputTypes } from "@/composables/useAiBrain";

import { computed, onBeforeUnmount, ref } from "vue";
import { useForm, usePage, router as inertiaRoute } from "@inertiajs/vue3";

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library as FontAwesomeLibrary } from "@fortawesome/fontawesome-svg-core";
import {
    faBrain,
    faCheck,
    faClipboardCheck,
    faFeatherPointed,
    faForward,
    faHourglassHalf,
    faRotateRight,
    faSpinner,
    faXmark,
} from "@fortawesome/free-solid-svg-icons";

FontAwesomeLibrary.add(faBrain, faCheck, faClipboardCheck, faFeatherPointed, faForward, faHourglassHalf, faRotateRight, faSpinner, faXmark);

const page = usePage();

const { novel } = defineProps({
    novel: {
        type: Object,
        required: true,
    },
});

const isUpdate = computed(() => !!novel?.id);

const AUTO_NEXT_SECONDS = 50;

const chapterContentForm = useForm({
    additional_information: null,
    ai_brain_id: null,
});

function buildAiBrainSearchUrl() {
    return route("search.ai-brains", {
        ai_brain_output_type_code: AiBrainOutputTypes.Text,
    });
}

const hasStringValue = (value) => {
    return !!value && typeof value === "string" && value.trim() !== "";
};

const chapters = computed(() => {
    const source = Array.isArray(novel?.novel_chapters) ? novel.novel_chapters : [];

    return [...source].sort(
        (a, b) => parseInt(a?.no ?? 0, 10) - parseInt(b?.no ?? 0, 10)
    );
});

const chaptersWithSummary = computed(() => {
    return chapters.value.filter((chapter) => hasStringValue(chapter?.summery));
});

const chaptersWithContent = computed(() => {
    return chapters.value.filter((chapter) => hasStringValue(chapter?.content));
});

const totalChapters = computed(() => chapters.value.length);

const progressPercent = computed(() => {
    if (totalChapters.value === 0) {
        return 0;
    }

    return Math.round((chaptersWithContent.value.length / totalChapters.value) * 100);
});

const currentChapter = computed(() => {
    return chapters.value.find((chapter) => !hasStringValue(chapter?.content)) || null;
});

const allContentsComplete = computed(() => {
    return totalChapters.value > 0 && chaptersWithContent.value.length === totalChapters.value;
});

const processing = ref(false);
const failedChapterNo = ref(null);
const finalizing = ref(false);

const countdown = ref(0);
let countdownInterval = null;

const stopCountdown = () => {
    if (countdownInterval) {
        clearInterval(countdownInterval);
        countdownInterval = null;
    }

    countdown.value = 0;
};

const startCountdown = () => {
    stopCountdown();

    if (allContentsComplete.value) {
        return;
    }

    countdown.value = AUTO_NEXT_SECONDS;

    countdownInterval = setInterval(() => {
        countdown.value -= 1;

        if (countdown.value <= 0) {
            stopCountdown();
            generateCurrent();
        }
    }, 1000);
};

const validate = () => {
    chapterContentForm.clearErrors();

    let valid = true;

    if (!chapterContentForm.ai_brain_id) {
        chapterContentForm.setError(
            "ai_brain_id",
            "AI Brain selection is required",
        );
        valid = false;
    }

    if (chaptersWithSummary.value.length === 0) {
        chapterContentForm.setError(
            "additional_information",
            "Generate chapter summaries before generating chapter contents.",
        );
        valid = false;
    }

    if (!currentChapter.value) {
        chapterContentForm.setError(
            "additional_information",
            "All chapter contents have already been generated.",
        );
        valid = false;
    }

    return valid;
};

const flashMessageStatus = () => {
    return page?.props?.flashMessage?.status ?? null;
};

const generateCurrent = () => {
    if (processing.value || finalizing.value || !isUpdate.value) {
        return;
    }

    if (allContentsComplete.value) {
        return;
    }

    if (!validate()) {
        return;
    }

    stopCountdown();
    failedChapterNo.value = null;
    processing.value = true;

    const chapterNo = currentChapter.value?.no;

    inertiaRoute.patch(
        route("back-office.novels.generate.chapter-content", {
            slug: novel?.slug,
        }),
        {
            ...chapterContentForm.data(),
            chapter_no: chapterNo,
            _method: "patch",
        },
        {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                processing.value = false;

                if (flashMessageStatus() === "success") {
                    failedChapterNo.value = null;
                    startCountdown();
                } else {
                    failedChapterNo.value = chapterNo;
                }
            },
            onError: () => {
                processing.value = false;
                failedChapterNo.value = chapterNo;
            },
        },
    );
};

const retryCurrent = () => {
    if (processing.value || finalizing.value) {
        return;
    }

    generateCurrent();
};

const goToNext = () => {
    if (processing.value || finalizing.value || countdown.value === 0) {
        return;
    }

    stopCountdown();
    generateCurrent();
};

const finalizeNovel = () => {
    if (finalizing.value || processing.value || !isUpdate.value) {
        return;
    }

    if (!allContentsComplete.value) {
        return;
    }

    finalizing.value = true;

    inertiaRoute.patch(
        route("back-office.novels.review", {
            slug: novel?.slug,
        }),
        {
            _method: "patch",
        },
        {
            preserveScroll: true,
            preserveState: true,
            onFinish: () => {
                finalizing.value = false;
            },
        },
    );
};

const isChapterCurrent = (chapter) => {
    return currentChapter.value?.id === chapter?.id;
};

const isChapterFailed = (chapter) => {
    return String(chapter?.no) === String(failedChapterNo.value);
};

const submit = () => {
    if (allContentsComplete.value) {
        finalizeNovel();

        return;
    }

    generateCurrent();
};

onBeforeUnmount(stopCountdown);

defineExpose({ submit });
</script>

<template>
    <div class="space-y-6">
        <div class="bg-white border rounded-xl p-5 shadow-sm space-y-4">
            <h3 class="text-base font-semibold flex items-center gap-2">
                <FontAwesomeIcon icon="feather-pointed" class="text-emerald-600" />
                Complete Novel - Chapter Content Generator
            </h3>

            <p class="text-sm text-gray-500">
                Generate the complete prose for each novel chapter independently. Each chapter is written from its own generated summary.
            </p>

            <div class="rounded-md border border-gray-200 p-4 space-y-2">
                <div class="flex items-center justify-between text-sm">
                    <p class="text-gray-500">
                        Chapter Contents
                        <span class="font-semibold text-emerald-600">{{ chaptersWithContent.length }}</span>
                        /
                        {{ totalChapters }} completed
                    </p>

                    <p class="text-gray-400">
                        {{ totalChapters - chaptersWithContent.length }} remaining
                    </p>
                </div>

                <div class="w-full h-2 rounded-full bg-gray-200 overflow-hidden">
                    <div class="h-full bg-emerald-600 transition-all"
                        :style="{ width: progressPercent + '%' }"></div>
                </div>
            </div>

            <div v-if="allContentsComplete"
                class="flex items-center gap-2 rounded-md border border-teal-200 bg-teal-50 px-3 py-2 text-sm text-teal-700">
                <FontAwesomeIcon icon="check" />
                All chapter contents have been generated. You can now complete the novel.
            </div>

            <div v-else-if="processing"
                class="flex items-center gap-2 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-2 text-sm text-emerald-700">
                <FontAwesomeIcon icon="spinner" spin />
                Generating Chapter {{ currentChapter?.no }} content...
            </div>

            <div v-else-if="failedChapterNo"
                class="flex items-center gap-2 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700">
                <FontAwesomeIcon icon="xmark" />
                Chapter {{ failedChapterNo }} content generation failed. Review the details and retry.
            </div>
        </div>

        <div class="bg-white border rounded-xl p-5 shadow-sm space-y-4">
            <div class="grid grid-cols-1 gap-2">
                <div v-for="chapter in chapters" :key="chapter.id"
                    class="flex items-center justify-between rounded-md border px-3 py-2 text-sm"
                    :class="isChapterCurrent(chapter)
                        ? 'border-emerald-300 bg-emerald-50'
                        : isChapterFailed(chapter)
                            ? 'border-red-200 bg-red-50'
                            : 'border-gray-200'">
                    <span class="text-gray-700">
                        Chapter {{ chapter.no }} - {{ chapter.title }}
                    </span>

                    <span class="flex items-center gap-1 text-xs"
                        :class="hasStringValue(chapter?.content)
                            ? 'text-emerald-600'
                            : isChapterCurrent(chapter)
                                ? 'text-blue-600'
                                : 'text-gray-400'">
                        <FontAwesomeIcon v-if="processing && isChapterCurrent(chapter)" icon="spinner" spin class="text-xs" />
                        <FontAwesomeIcon v-else-if="hasStringValue(chapter?.content)" icon="check" class="text-xs" />
                        <FontAwesomeIcon v-else-if="isChapterFailed(chapter)" icon="xmark" class="text-xs" />
                        <FontAwesomeIcon v-else icon="feather-pointed" class="text-xs" />

                        {{ hasStringValue(chapter?.content)
                            ? "Content generated"
                            : isChapterFailed(chapter)
                                ? "Generation failed"
                                : isChapterCurrent(chapter) && processing
                                    ? "Generating..."
                                    : allContentsComplete || !isChapterCurrent(chapter)
                                        ? "Pending"
                                        : "Ready to generate" }}
                    </span>
                </div>
            </div>

            <div v-if="currentChapter && !allContentsComplete"
                class="rounded-md border border-emerald-200 bg-emerald-50/50 px-3 py-2 text-sm text-gray-700">
                <span class="font-medium text-emerald-700">
                    Next up
                </span>
                : Chapter {{ currentChapter.no }} - {{ currentChapter.title }}
            </div>
        </div>

        <div class="bg-white border rounded-xl p-5 shadow-sm space-y-4">
            <div class="flex items-center gap-2">
                <FontAwesomeIcon icon="brain" class="text-purple-600" />
                <h3 class="text-base font-semibold">AI Brain Configuration</h3>
            </div>

            <p class="text-sm text-gray-500">
                Select the AI model that will generate the chapter contents.
            </p>

            <div class="border-2 border-dashed border-purple-200 rounded-xl p-4 bg-gradient-to-br from-purple-50 to-blue-50" >
                <InfiniteScrollApiSelect :form="chapterContentForm" fieldName="ai_brain_id"
                    :selectedItem="null" :apiUrl="buildAiBrainSearchUrl()" :multiple="false"
                    placeholder="Select AI Brain" :error="chapterContentForm.errors.ai_brain_id"
                    class="ai-brain-select"/>
            </div>

            <p v-if="chapterContentForm.errors.ai_brain_id" class="text-red-500 text-sm">
                {{ chapterContentForm.errors.ai_brain_id }}
            </p>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Chapter Content Additional Information
                </label>

                <textarea v-model="chapterContentForm.additional_information" rows="3"
                    placeholder="Any additional context or instructions for the AI..."
                    class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none border-gray-300"></textarea>

                <p v-if="chapterContentForm.errors.additional_information">
                    {{ chapterContentForm.errors.additional_information }}
                </p>
            </div>
        </div>

        <div class="bg-white border rounded-xl p-5 shadow-sm" v-if="allContentsComplete">
            <div class="flex items-center gap-2 mb-2">
                <FontAwesomeIcon icon="clipboard-check" class="text-teal-600" />
                <h3 class="text-base font-semibold">Complete Novel</h3>
            </div>

            <p class="text-sm text-gray-500 mb-4">
                Every planned chapter has a summary and generated content. Completing the novel deterministically verifies all chapters and marks it as complete.
            </p>

            <div class="flex justify-end">
                <button type="button" @click="finalizeNovel" :disabled="!isUpdate || finalizing || processing"
                    class="px-5 py-2 text-sm bg-teal-600 hover:bg-teal-700 text-white rounded-md flex items-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed">
                    <FontAwesomeIcon v-if="finalizing" icon="spinner" spin />
                    <FontAwesomeIcon v-else icon="clipboard-check" />
                    {{ finalizing ? "Completing Novel..." : "Complete Novel" }}
                </button>
            </div>
        </div>

        <div v-else class="bg-white border rounded-xl p-5 shadow-sm">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div class="flex items-center gap-2 text-sm text-gray-500">
                    <FontAwesomeIcon icon="hourglass-half" class="text-emerald-600" />
                    <span v-if="countdown > 0">
                        Auto-generate next chapter in {{ countdown }}s
                    </span>
                    <span v-else>
                        Generate each chapter one at a time
                    </span>
                </div>

                <div class="flex items-center gap-2 justify-end">
                    <button type="button" @click="goToNext" :disabled="processing || finalizing || countdown === 0"
                        class="px-4 py-2 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-md flex items-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed">
                        <FontAwesomeIcon icon="forward" />
                        Next
                    </button>

                    <button type="button" @click="failedChapterNo ? retryCurrent() : generateCurrent()"
                        :disabled="!isUpdate || processing || finalizing"
                        class="px-5 py-2 text-sm bg-emerald-600 hover:bg-emerald-700 text-white rounded-md flex items-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed">
                        <FontAwesomeIcon v-if="processing" icon="spinner" spin />
                        <FontAwesomeIcon v-else-if="failedChapterNo" icon="rotate-right" />
                        <FontAwesomeIcon v-else icon="feather-pointed" />
                        {{ processing
                            ? "Generating Chapter " + (currentChapter?.no ?? "") + "..."
                            : failedChapterNo
                                ? "Retry Chapter " + failedChapterNo
                                : "Generate Chapter " + (currentChapter?.no ?? "") }}
                    </button>
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
<script setup>
import { computed } from "vue";
import { hasData } from "@/composables/useNovel";

const { chapters } = defineProps({
    chapters: {
        type: Array,
        default: () => [],
    },
});

const writtenChapters = computed(() =>
    (chapters ?? []).filter((chapter) => hasData(chapter?.content)),
);

const chapterLabel = (chapter) => {
    const no = chapter?.no ?? chapter?.chapter_number;

    if (no === null || no === undefined || no === "") {
        return chapter?.title ? chapter.title : "Chapter";
    }

    return `Chapter ${no}`;
};
</script>

<template>
    <div class="space-y-4">
        <article
            v-for="chapter in writtenChapters"
            :key="chapter.id ?? chapter.no"
            class="border border-gray-200 rounded-xl bg-white shadow-sm overflow-hidden"
        >
            <header
                class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-200 bg-gray-50 px-4 py-3"
            >
                <div class="min-w-0">
                    <h3 class="text-sm font-semibold text-gray-900 truncate">
                        {{ chapterLabel(chapter) }}
                    </h3>
                    <p
                        v-if="chapter.title"
                        class="text-xs text-gray-500 truncate"
                    >
                        {{ chapter.title }}
                    </p>
                </div>

                <span
                    v-if="chapter.word_count"
                    class="text-xs font-medium text-gray-500 whitespace-nowrap"
                >
                    {{ chapter.word_count }} words
                </span>
            </header>

            <div class="px-4 py-3">
                <p class="text-sm text-gray-700 whitespace-pre-line">
                    {{ chapter.content }}
                </p>
            </div>
        </article>
    </div>
</template>

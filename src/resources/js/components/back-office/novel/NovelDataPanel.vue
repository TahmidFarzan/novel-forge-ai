<script setup>
import { computed } from "vue";
import { hasData } from "@/composables/useNovel";

const { sections, nested = false } = defineProps({
    sections: {
        type: [Object, Array, String, Number, Boolean],
        default: null,
    },
    nested: {
        type: Boolean,
        default: false,
    },
});

const isScalar = (value) => {
    return (
        typeof value === "string" ||
        typeof value === "number" ||
        typeof value === "boolean"
    );
};

const humanize = (key) => {
    return String(key)
        .replace(/[_-]+/g, " ")
        .replace(/\s+/g, " ")
        .trim()
        .replace(/\b\w/g, (character) => character.toUpperCase());
};

const scalarList = computed(() => {
    if (!Array.isArray(sections)) {
        return [];
    }

    return sections.filter((item) => isScalar(item) && hasData(item));
});

const objectList = computed(() => {
    if (!Array.isArray(sections)) {
        return [];
    }

    return sections.filter((item) => typeof item === "object" && item !== null);
});

const entries = computed(() => {
    if (!sections || typeof sections !== "object" || Array.isArray(sections)) {
        return [];
    }

    return Object.entries(sections).filter(([, value]) => hasData(value));
});
</script>

<template>
    <p v-if="isScalar(sections)" class="text-sm text-gray-700 whitespace-pre-line">
        {{ sections }}
    </p>

    <ul v-else-if="scalarList.length" class="space-y-1.5">
        <li
            v-for="(item, index) in scalarList"
            :key="index"
            class="flex gap-2 text-sm text-gray-700"
        >
            <span
                aria-hidden="true"
                class="mt-2 w-1 h-1 rounded-full bg-gray-400 flex-shrink-0"
            ></span>
            <span class="whitespace-pre-line">{{ item }}</span>
        </li>
    </ul>

    <div v-else-if="objectList.length" class="space-y-2">
        <div
            v-for="(item, index) in objectList"
            :key="index"
            class="rounded-lg border border-gray-200 bg-gray-50 p-3"
        >
            <NovelDataPanel :sections="item" :nested="true" />
        </div>
    </div>

    <div v-else-if="!nested" class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div
            v-for="entry in entries"
            :key="entry[0]"
            class="border border-gray-200 rounded-xl bg-white p-4 shadow-sm"
        >
            <h4
                class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-2"
            >
                {{ humanize(entry[0]) }}
            </h4>

            <NovelDataPanel :sections="entry[1]" :nested="true" />
        </div>
    </div>

    <dl v-else class="space-y-2.5">
        <div v-for="entry in entries" :key="entry[0]">
            <dt class="text-xs font-medium text-gray-500">
                {{ humanize(entry[0]) }}
            </dt>
            <dd class="mt-1">
                <NovelDataPanel :sections="entry[1]" :nested="true" />
            </dd>
        </div>
    </dl>
</template>

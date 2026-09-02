<script setup>
import Layout from "@/pages/layouts/AuthLayout.vue";

import { computed, onMounted, nextTick } from "vue";
import { Head } from "@inertiajs/vue3";

import { formatDateTime } from "@/composables/useDateTime";
import { titleFormat } from "@/composables/useStringFormat";

defineOptions({ layout: Layout });

const { documentStyle } = defineProps({
    documentStyle: Object,
});

const pageTitle = computed(
    () => `${formatSectionName(documentStyle?.section_name)} Details`,
);

const settingsObject = computed(() => {
    if (!documentStyle?.settings || typeof documentStyle.settings !== "object")
        return {};
    return documentStyle.settings;
});

const settingEntries = computed(() => {
    return Object.entries(settingsObject.value);
});

const settingCount = computed(() => settingEntries.value.length);

const formattedJson = computed(() => {
    try {
        return JSON.stringify(settingsObject.value, null, 4);
    } catch {
        return "N/A";
    }
});

function formatSectionName(name) {
    return name ? titleFormat(name) : "N/A";
}

function formatSettingLabel(key) {
    return titleFormat(String(key).replace(/[_]+/g, " "));
}

function isBoolean(value) {
    return typeof value === "boolean";
}

function isNumber(value) {
    return typeof value === "number";
}

function formatSettingValue(value) {
    if (isBoolean(value)) {
        return value ? "Yes" : "No";
    }
    if (isNumber(value)) {
        return value;
    }
    if (value === null || value === undefined || value === "") {
        return "N/A";
    }
    return String(value)
        .replace(/_/g, " ")
        .replace(/\b\w/g, (c) => c.toUpperCase());
}

onMounted(async () => {
    await nextTick();

    window.dispatchEvent(
        new CustomEvent("set-breadcrumb", {
            detail: [
                {
                    text: "Document Styles",
                    href: route("document-styles.index"),
                },
                { text: pageTitle.value, active: true },
            ],
        }),
    );
});
</script>

<template>
    <Head :title="pageTitle" />

    <div class="w-full space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold">Document Style Details</h2>
        </div>

        <div
            class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4"
        >
            <h3 class="text-base font-semibold border-b pb-2">
                Basic Information
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="border border-gray-200 rounded-lg p-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Section Name</span>
                        <span class="font-medium">{{
                            formatSectionName(documentStyle?.section_name)
                        }}</span>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg p-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Style Settings</span>
                        <span class="font-medium">{{ settingCount }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden"
        >
            <div class="p-5 border-b border-gray-200">
                <h3 class="text-base font-semibold">Style Configuration</h3>
            </div>

            <div v-if="settingEntries.length">
                <div
                    v-for="([key, value], index) in settingEntries"
                    :key="key"
                    class="border-b border-gray-200 last:border-b-0"
                >
                    <div class="grid grid-cols-1 md:grid-cols-12">
                        <div
                            class="md:col-span-4 bg-gray-50 p-5 border-b md:border-b-0 md:border-r border-gray-200"
                        >
                            <div
                                class="font-semibold text-gray-900 break-words"
                            >
                                {{ formatSettingLabel(key) }}
                            </div>
                        </div>

                        <div class="md:col-span-8 p-5">
                            <span
                                v-if="isBoolean(value)"
                                class="inline-flex px-2.5 py-1 rounded-md text-xs font-medium"
                                :class="
                                    value
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-red-100 text-red-700'
                                "
                            >
                                {{ value ? "Yes" : "No" }}
                            </span>

                            <span
                                v-else
                                class="font-medium text-gray-900 break-all whitespace-pre-wrap"
                            >
                                {{ formatSettingValue(value) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="px-5 py-10 text-center text-gray-500">N/A</div>
        </div>

        <div
            class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4"
        >
            <h3 class="text-base font-semibold border-b pb-2">
                Raw Configuration (JSON)
            </h3>

            <pre
                class="bg-gray-50 border border-gray-200 rounded-md p-3 text-xs overflow-x-auto whitespace-pre-wrap break-words"
                >{{ formattedJson }}
            </pre>
        </div>

        <div
            class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4"
        >
            <h3 class="text-base font-semibold border-b pb-2">
                System Information
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="border border-gray-200 rounded-lg p-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Created At</span>
                        <span class="font-medium">
                            {{
                                documentStyle?.created_at
                                    ? formatDateTime(documentStyle.created_at)
                                    : "N/A"
                            }}
                        </span>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg p-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Updated At</span>
                        <span class="font-medium">
                            {{
                                documentStyle?.updated_at
                                    ? formatDateTime(documentStyle.updated_at)
                                    : "N/A"
                            }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

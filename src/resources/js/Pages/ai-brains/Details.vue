<script setup>
import Layout from "@/pages/layouts/AuthLayout.vue";
import RecentActivities from "@/components/activity-log/RecentModelActivityLogs.vue";

import { computed, onMounted, nextTick, inject } from "vue";
import { Head, router as intertiaJsRoute } from "@inertiajs/vue3";

import { formatDateTime } from "@/composables/useDateTime";

defineOptions({ layout: Layout });

const { aiBrain } = defineProps({
    aiBrain: Object,
});

const pageTitle = computed(() => `Details of ${aiBrain?.name}`);

onMounted(async () => {
    await nextTick();

    window.dispatchEvent(
        new CustomEvent("set-breadcrumb", {
            detail: [
                { text: "Ai Brains", href: route("ai-brains.index") },
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
            <h2 class="text-lg font-semibold">Ai Brain Details</h2>
        </div>

        <div
            class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4"
        >
            <h3 class="text-base font-semibold border-b pb-2">
                Basic Information
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-1 gap-4 text-sm">
                <div class="border border-gray-200 rounded-lg p-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Name</span>
                        <span class="font-medium">
                            {{ aiBrain?.name || "N/A" }}
                        </span>
                    </div>
                    <div>
                        <div class="text-gray-500 mb-1">Brief</div>
                        <div class="text-gray-700"
                            v-html="aiBrain?.brief || 'N/A'"></div>
                    </div>
                </div>
            </div>
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
                                aiBrain?.created_at
                                    ? formatDateTime(aiBrain.created_at)
                                    : "N/A"
                            }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Created By</span>
                        <span class="font-medium">
                            {{ aiBrain?.created_by?.name || "N/A" }}
                        </span>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg p-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Updated At</span>
                        <span class="font-medium">
                            {{
                                aiBrain?.updated_at
                                    ? formatDateTime(aiBrain.updated_at)
                                    : "N/A"
                            }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Updated By</span>
                        <span class="font-medium">
                            {{
                                aiBrain?.latest_activity_log?.causer?.name ||
                                "N/A"
                            }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4"
        >
            <h3 class="text-base font-semibold border-b pb-2">Activity Logs</h3>

            <RecentActivities :model-slug="'ai-brain'" :model="aiBrain" />
        </div>
    </div>
</template>

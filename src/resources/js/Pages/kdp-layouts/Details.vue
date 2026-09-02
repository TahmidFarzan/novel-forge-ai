<script setup>
import Layout from '@/pages/layouts/AuthLayout.vue'
import RecentActivities from '@/components/activity-log/RecentModelActivityLogs.vue'

import { computed, onMounted, nextTick } from 'vue'
import { Head } from '@inertiajs/vue3'

import { formatDateTime } from '@/composables/useDateTime'

defineOptions({ layout: Layout })

const { kdpLayout } = defineProps({
    kdpLayout: Object,
})

const pageTitle = computed(() => `Details of ${kdpLayout?.name}`)

const pageCount = computed(() => {
    const min = kdpLayout?.minimum_page_count
    const max = kdpLayout?.maximum_page_count

    if (min != null && max != null) return `${min} - ${max}`
    if (min != null) return `${min}+`
    if (max != null) return `Up to ${max}`
    return 'N/A'
})

onMounted(async () => {
    await nextTick()

    window.dispatchEvent(
        new CustomEvent('set-breadcrumb', {
            detail: [
                { text: 'Layouts', href: route('kdp-layouts.index') },
                { text: pageTitle.value, active: true }
            ],
        })
    )
})
</script>

<template>
    <Head :title="pageTitle" />

    <div class="w-full space-y-6">

        <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold">
                Layout Details
            </h2>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4">
            <h3 class="text-base font-semibold border-b pb-2">
                Basic Information
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

                <div class="border border-gray-200 rounded-lg p-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Name</span>
                        <span class="font-medium">{{ kdpLayout?.name || 'N/A' }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Binding Type</span>
                        <span class="font-medium">{{ kdpLayout?.binding_type || 'N/A' }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Page Size</span>
                        <span class="font-medium">{{ kdpLayout?.page_size || 'N/A' }}</span>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg p-4 space-y-2">
                    <div>
                        <div class="text-gray-500 mb-1">Description</div>
                        <div class="text-gray-700">{{ kdpLayout?.description || 'N/A' }}</div>
                    </div>
                </div>

            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4">
            <h3 class="text-base font-semibold border-b pb-2">
                Dimensions & Margins
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

                <div class="border border-gray-200 rounded-lg p-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Width (in)</span>
                        <span class="font-medium">{{ kdpLayout?.width ?? 'N/A' }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Height (in)</span>
                        <span class="font-medium">{{ kdpLayout?.height ?? 'N/A' }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Trim Size</span>
                        <span class="font-medium">{{ kdpLayout?.trim_size || 'N/A' }}</span>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg p-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Top Margin (in)</span>
                        <span class="font-medium">{{ kdpLayout?.top_margin ?? 'N/A' }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Bottom Margin (in)</span>
                        <span class="font-medium">{{ kdpLayout?.bottom_margin ?? 'N/A' }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Outside Margin (in)</span>
                        <span class="font-medium">{{ kdpLayout?.outside_margin ?? 'N/A' }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Gutter (in)</span>
                        <span class="font-medium">{{ kdpLayout?.gutter ?? 'N/A' }}</span>
                    </div>
                </div>

            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4">
            <h3 class="text-base font-semibold border-b pb-2">
                Layout & Print Settings
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

                <div class="border border-gray-200 rounded-lg p-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Bleed</span>
                        <span class="font-medium">{{ kdpLayout?.bleed || 'N/A' }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Interior Type</span>
                        <span class="font-medium">{{ kdpLayout?.interior_type || 'N/A' }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Paper Color</span>
                        <span class="font-medium">{{ kdpLayout?.paper_color || 'N/A' }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Page Count</span>
                        <span class="font-medium">{{ pageCount }}</span>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg p-4 space-y-2">
                    <div>
                        <div class="text-gray-500 mb-1">Font Settings</div>
                        <div class="text-gray-700">{{ kdpLayout?.font_settings || 'N/A' }}</div>
                    </div>
                </div>

            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4">
            <h3 class="text-base font-semibold border-b pb-2">
                System Information
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">

                <div class="border border-gray-200 rounded-lg p-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Created At</span>
                        <span class="font-medium">
                            {{ kdpLayout?.created_at ? formatDateTime(kdpLayout.created_at) : 'N/A' }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Created By</span>
                        <span class="font-medium">
                            {{ kdpLayout?.created_by?.name || 'N/A' }}
                        </span>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg p-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Updated At</span>
                        <span class="font-medium">
                            {{ kdpLayout?.updated_at ? formatDateTime(kdpLayout.updated_at) : 'N/A' }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Updated By</span>
                        <span class="font-medium">
                            {{ kdpLayout?.latest_activity_log?.causer?.name || 'N/A' }}
                        </span>
                    </div>
                </div>

            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4">
            <h3 class="text-base font-semibold border-b pb-2">
                Activity Logs
            </h3>

            <RecentActivities :model-slug="'kdp-layout'" :model="kdpLayout" />
        </div>
    </div>
</template>

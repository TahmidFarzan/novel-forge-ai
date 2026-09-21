<script setup>
import Layout from '@/pages/layouts/AuthLayout.vue'
import RecentActivities from '@/components/back-office/activity-log/RecentModelActivityLogs.vue'

import { computed, onMounted, nextTick } from 'vue'
import { Head } from '@inertiajs/vue3'

import { formatDateTime } from '@/composables/useDateTime'

defineOptions({ layout: Layout })

const { novelGeneratorStep } = defineProps({
    novelGeneratorStep: Object,
})

const pageTitle = computed(() => `Details of ${novelGeneratorStep?.name}`)

onMounted(async () => {
    await nextTick()

    window.dispatchEvent(
        new CustomEvent('set-breadcrumb', {
            detail: [
                { text: 'Novel Generator Steps', href: route('back-office.novel-generator-steps.index') },
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
                Novel Generator Step Details
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
                        <span class="font-medium">{{ novelGeneratorStep?.name || 'N/A' }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Slug</span>
                        <span class="font-medium">{{ novelGeneratorStep?.slug || 'N/A' }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">AI Prompt</span>
                        <span class="font-medium">{{ novelGeneratorStep?.ai_prompt?.name || 'N/A' }}</span>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg p-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Previous Step</span>
                        <span class="font-medium">{{ novelGeneratorStep?.previous_step?.name || '—' }}</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Next Step</span>
                        <span class="font-medium">{{ novelGeneratorStep?.next_step?.name || '—' }}</span>
                    </div>
                </div>

            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4">
            <h3 class="text-base font-semibold border-b pb-2">
                Dependency Steps
            </h3>

            <div v-if="novelGeneratorStep?.dependency_steps?.length" class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div v-for="dependency in novelGeneratorStep.dependency_steps" :key="dependency.id"
                    class="border border-gray-200 rounded-lg p-4">
                    <div class="flex justify-between">
                        <span class="text-gray-500">{{ dependency.name }}</span>
                        <span class="text-gray-500">{{ dependency.ai_prompt?.name || 'N/A' }}</span>
                    </div>
                </div>
            </div>

            <div v-else class="text-sm text-gray-500">
                This step has no generator dependencies.
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
                            {{ novelGeneratorStep?.created_at ? formatDateTime(novelGeneratorStep.created_at) : 'N/A' }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Created By</span>
                        <span class="font-medium">
                            {{ novelGeneratorStep?.created_by?.name || 'N/A' }}
                        </span>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg p-4 space-y-2">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Updated At</span>
                        <span class="font-medium">
                            {{ novelGeneratorStep?.updated_at ? formatDateTime(novelGeneratorStep.updated_at) : 'N/A' }}
                        </span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-gray-500">Updated By</span>
                        <span class="font-medium">
                            {{ novelGeneratorStep?.latest_activity_log?.causer?.name || 'N/A' }}
                        </span>
                    </div>
                </div>

            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4">
            <h3 class="text-base font-semibold border-b pb-2">
                Activity Logs
            </h3>

            <RecentActivities :model-slug="'novel-generator-step'" :model="novelGeneratorStep" />
        </div>
    </div>
</template>

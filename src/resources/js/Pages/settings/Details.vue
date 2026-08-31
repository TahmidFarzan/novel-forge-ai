<script setup>
import Layout from "@/pages/layouts/AuthLayout.vue";
import RecentActivities from "@/components/activity-log/RecentModelActivityLogs.vue";
import { computed, inject, onMounted, nextTick, ref } from "vue";
import { Head } from "@inertiajs/vue3";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library as FontAwesomeLibrary } from "@fortawesome/fontawesome-svg-core";
import { faPen } from "@fortawesome/free-solid-svg-icons";
import { formatDateTime } from "@/composables/useDateTime";
import { canUpdateSetting } from "@/composables/useUserPermissions";
import { useSetting, settingOptions as  settingOptionsData } from "@/composables/useSetting";
import { titleFormat } from "@/composables/useStringFormat";
import { fetchFromApi } from "@/composables/useApiClient";

FontAwesomeLibrary.add(faPen);

defineOptions({
    layout: Layout,
});

const { setting } = defineProps({
    setting: {
        type: Object,
        required: true,
    },
});

const authUser = inject("authUser");

const { settingOptionValueTypes, isTruthyValue, formatSettingValue } =
    useSetting();

const OPTION_AI_BRAIN_RUNNER = settingOptionsData.OPTION_AI_BRAIN_RUNNER;
const OPTION_AI_BRAIN = settingOptionsData.OPTION_AI_BRAIN;

const relatedOptionNames = ref({});
const relatedOptionLoading = ref({});

const pageTitle = computed(() => {
    return `${setting?.name || "N/A"} Details`;
});

const canUpdate = (setting) => {
    return canUpdateSetting(authUser?.value, setting);
};

const settingOptions = computed(() => {
    if (
        !setting?.options ||
        typeof setting.options !== "object" ||
        Array.isArray(setting.options)
    ) {
        return {};
    }

    return setting.options;
});

const optionCount = computed(() => {
    return Object.keys(settingOptions.value).length;
});

const formatOptionLabel = (key) => {
    return titleFormat(String(key).replace(/[-_]+/g, " "));
};

const hasOptionValue = (value) => {
    return value !== null && value !== undefined && value !== "";
};

const isStringOrInteger = (option) => {
    return (
        option?.valueType === settingOptionValueTypes.STRING ||
        option?.valueType === settingOptionValueTypes.INTEGER ||
        option?.valueType === "string" ||
        option?.valueType === "integer" ||
        option?.valueType === "int"
    );
};

const isAiBrainRunnerOption = (key, option) => {
    return (
        key === OPTION_AI_BRAIN_RUNNER &&
        isStringOrInteger(option) &&
        hasOptionValue(option?.value)
    );
};

const isAiBrainOption = (key, option) => {
    return (
        key === OPTION_AI_BRAIN &&
        isStringOrInteger(option) &&
        hasOptionValue(option?.value)
    );
};

const loadAiBrainRunnerName = async (key, option) => {
    relatedOptionLoading.value[key] = true;

    const response = await fetchFromApi(
        route("search.ai-brain-runner", {
            slugOrId: option?.value,
        }),
    );

    relatedOptionNames.value[key] = response?.name || option?.value;
    relatedOptionLoading.value[key] = false;
};

const loadAiBrainName = async (key, option) => {
    relatedOptionLoading.value[key] = true;

    const response = await fetchFromApi(
        route("search.ai-brain", {
            slugOrId: option?.value,
        }),
    );

    relatedOptionNames.value[key] = response?.name || option?.value;
    relatedOptionLoading.value[key] = false;
};

const loadRelatedOptionNames = async () => {
    const requests = [];

    Object.entries(settingOptions.value).forEach(([key, option]) => {
        if (isAiBrainRunnerOption(key, option)) {
            requests.push(loadAiBrainRunnerName(key, option));
        }

        if (isAiBrainOption(key, option)) {
            requests.push(loadAiBrainName(key, option));
        }
    });

    await Promise.all(requests);
};

onMounted(async () => {
    await loadRelatedOptionNames();
    await nextTick();

    window.dispatchEvent(
        new CustomEvent("set-breadcrumb", {
            detail: [
                {
                    text: "Settings",
                    href: route("settings.index"),
                },
                {
                    text: pageTitle.value,
                    active: true,
                },
            ],
        }),
    );
});
</script>

<template>
    <Head :title="pageTitle" />

    <div class="w-full space-y-6">
        <div class="flex justify-between items-center">
            <h2 class="text-lg font-semibold">Setting Details</h2>

            <div class="flex gap-2">
                <a
                    v-if="canUpdate(setting)"
                    :href="
                        route('settings.edit', {
                            slug: setting?.slug,
                        })
                    "
                    class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-md flex items-center gap-2 transition"
                >
                    <FontAwesomeIcon icon="pen" />
                    Edit
                </a>
            </div>
        </div>

        <div
            class="bg-white border border-gray-200 rounded-xl shadow-sm p-5 space-y-4"
        >
            <h3 class="text-base font-semibold border-b pb-2">
                Basic Information
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">Name</span>

                        <span class="font-medium text-right">
                            {{ setting?.name || "N/A" }}
                        </span>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg p-4 space-y-3">
                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">Options</span>

                        <span class="font-medium">
                            {{ optionCount }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div
            class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden"
        >
            <div class="p-5 border-b border-gray-200">
                <h3 class="text-base font-semibold">Options</h3>
            </div>

            <div v-if="optionCount">
                <div
                    v-for="(option, key) in settingOptions"
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
                                {{ formatOptionLabel(key) }}
                            </div>
                        </div>

                        <div class="md:col-span-8 p-5">
                            <span
                                v-if="
                                    option?.valueType ===
                                    settingOptionValueTypes.BOOLEAN
                                "
                                class="inline-flex px-2.5 py-1 rounded-md text-xs font-medium"
                                :class="
                                    isTruthyValue(option?.value)
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-red-100 text-red-700'
                                "
                            >
                                {{
                                    isTruthyValue(option?.value)
                                        ? "True"
                                        : "False"
                                }}
                            </span>

                            <span
                                v-else-if="
                                    isStringOrInteger(option) &&
                                    key === OPTION_AI_BRAIN_RUNNER &&
                                    hasOptionValue(option?.value)
                                "
                                class="font-medium text-gray-900 break-all whitespace-pre-wrap"
                            >
                                {{
                                    relatedOptionLoading[key]
                                        ? "Loading..."
                                        : relatedOptionNames[key] ||
                                          option?.value
                                }}
                            </span>

                            <span
                                v-else-if="
                                    isStringOrInteger(option) &&
                                    key === OPTION_AI_BRAIN &&
                                    hasOptionValue(option?.value)
                                "
                                class="font-medium text-gray-900 break-all whitespace-pre-wrap"
                            >
                                {{
                                    relatedOptionLoading[key]
                                        ? "Loading..."
                                        : relatedOptionNames[key] ||
                                          option?.value
                                }}
                            </span>

                            <a
                                v-else-if="
                                    option?.valueType ===
                                        settingOptionValueTypes.URL &&
                                    hasOptionValue(option?.value)
                                "
                                :href="option.value"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="text-blue-600 hover:underline break-all"
                            >
                                {{ option.value }}
                            </a>

                            <div
                                v-else-if="
                                    option?.valueType ===
                                        settingOptionValueTypes.IMAGE &&
                                    hasOptionValue(option?.value)
                                "
                                class="space-y-2"
                            >
                                <img
                                    :src="option.value"
                                    :alt="formatOptionLabel(key)"
                                    class="max-w-xs max-h-40 object-contain rounded-md border border-gray-200"
                                />

                                <div class="text-xs text-gray-500 break-all">
                                    {{ option.value }}
                                </div>
                            </div>

                            <div
                                v-else-if="
                                    option?.valueType ===
                                        settingOptionValueTypes.COLOR &&
                                    hasOptionValue(option?.value)
                                "
                                class="flex items-center gap-3"
                            >
                                <span
                                    class="w-8 h-8 rounded-md border border-gray-300"
                                    :style="{
                                        backgroundColor: option.value,
                                    }"
                                ></span>

                                <span class="font-medium break-all">
                                    {{ option.value }}
                                </span>
                            </div>

                            <pre
                                v-else-if="
                                    option?.valueType ===
                                        settingOptionValueTypes.JSON ||
                                    option?.valueType ===
                                        settingOptionValueTypes.ARRAY
                                "
                                class="bg-gray-50 border border-gray-200 rounded-md p-3 text-xs overflow-x-auto whitespace-pre-wrap break-words"
                                >{{ formatSettingValue(option?.value) }}</pre
                            >

                            <span
                                v-else-if="hasOptionValue(option?.value)"
                                class="font-medium text-gray-900 break-all whitespace-pre-wrap"
                            >
                                {{ option.value }}
                            </span>

                            <span v-else class="text-gray-400 italic">
                                N/A
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
                System Information
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                <div class="border border-gray-200 rounded-lg p-4 space-y-2">
                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">Created At</span>

                        <span class="font-medium text-right">
                            {{
                                setting?.created_at
                                    ? formatDateTime(setting.created_at)
                                    : "N/A"
                            }}
                        </span>
                    </div>
                </div>

                <div class="border border-gray-200 rounded-lg p-4 space-y-2">
                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">Updated At</span>

                        <span class="font-medium text-right">
                            {{
                                setting?.updated_at
                                    ? formatDateTime(setting.updated_at)
                                    : "N/A"
                            }}
                        </span>
                    </div>

                    <div class="flex justify-between gap-4">
                        <span class="text-gray-500">Updated By</span>

                        <span class="font-medium text-right">
                            {{
                                setting?.latest_activity_log?.causer?.name ||
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

            <RecentActivities :model-slug="'setting'" :model="setting" />
        </div>
    </div>
</template>

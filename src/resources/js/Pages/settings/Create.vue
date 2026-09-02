<script setup>
import Layout from "@/pages/layouts/AuthLayout.vue";
import InfiniteScrollApiSelect from "@/components/multi-select/InfiniteScrollApiSelect.vue";

import { computed, inject, onMounted, nextTick } from "vue";
import { Head, useForm } from "@inertiajs/vue3";

import {
    useSetting,
    settingOptions as settingOptionsData,
} from "@/composables/useSetting";
import { titleFormat } from "@/composables/useStringFormat";

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library as FontAwesomeLibrary } from "@fortawesome/fontawesome-svg-core";
import { faSave, faSpinner } from "@fortawesome/free-solid-svg-icons";

FontAwesomeLibrary.add(faSave, faSpinner);

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

const { settingOptionValueTypes, getDefaultValueByType, isTruthyValue } =
    useSetting();

const OPTION_AI_BRAIN =
    settingOptionsData?.OPTION_AI_BRAIN;

const canUpdate = computed(() => {
    return !!authUser?.value;
});

const pageTitle = computed(() => {
    return `${setting?.name || "N/A"} Edit`;
});

const normalizeOptions = (options) => {
    if (!options || typeof options !== "object" || Array.isArray(options)) {
        return {};
    }

    return Object.fromEntries(
        Object.entries(options).map(([key, option]) => [
            key,
            {
                valueType: option?.valueType ?? settingOptionValueTypes.STRING,
                value:
                    option?.valueType === settingOptionValueTypes.BOOLEAN
                        ? isTruthyValue(option?.value)
                        : (option?.value ??
                          getDefaultValueByType(option?.valueType)),
            },
        ]),
    );
};

const saveForm = useForm({
    name: setting?.name ?? "",
    options: normalizeOptions(setting?.options),
});

const settingOptions = computed(() => {
    return saveForm.options &&
        typeof saveForm.options === "object" &&
        !Array.isArray(saveForm.options)
        ? saveForm.options
        : {};
});

const optionCount = computed(() => {
    return Object.keys(settingOptions.value).length;
});

const formatOptionName = (key) => {
    return String(key)
        .replace(/[-\s]+/g, "_")
        .replace(/([a-z])([A-Z])/g, "$1_$2")
        .toLowerCase();
};

const formatJsonValueForForm = (value) => {
    if (value === null || value === undefined) {
        return "";
    }

    if (typeof value === "string") {
        return value;
    }

    return JSON.stringify(value, null, 2);
};

const getOptionError = (key) => {
    return (
        saveForm.errors[`options.${key}`] ||
        saveForm.errors[`options.${key}.value`] ||
        null
    );
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

const isAiBrainOption = (key, option) => {
    return isStringOrInteger(option) && key === OPTION_AI_BRAIN;
};

const handleImageChange = (event, key) => {
    const file = event.target.files?.[0] ?? null;

    if (saveForm.options[key]) {
        saveForm.options[key].value = file;
    }
};

const prepareOptionsForSubmit = () => {
    return Object.fromEntries(
        Object.entries(settingOptions.value).map(([key, option]) => {
            let value = option.value;

            if (
                option.valueType === settingOptionValueTypes.JSON ||
                option.valueType === settingOptionValueTypes.ARRAY
            ) {
                if (typeof value === "string") {
                    try {
                        value = JSON.parse(value);
                    } catch {
                        value = option.value;
                    }
                }
            }

            if (option.valueType === settingOptionValueTypes.BOOLEAN) {
                value = isTruthyValue(value);
            }

            return [
                key,
                {
                    valueType: option.valueType,
                    value,
                },
            ];
        }),
    );
};

const validateOptions = () => {
    let valid = true;

    Object.entries(settingOptions.value).forEach(([key, option]) => {
        const errorKey = `options.${key}`;

        if (option.valueType === settingOptionValueTypes.BOOLEAN) {
            return;
        }

        if (
            option?.value === null ||
            option?.value === undefined ||
            option?.value === ""
        ) {
            saveForm.setError(errorKey, "Value is required.");

            valid = false;

            return;
        }

        if (option.valueType === settingOptionValueTypes.INTEGER) {
            if (!Number.isInteger(Number(option.value))) {
                saveForm.setError(errorKey, "Value must be an integer.");

                valid = false;
            }
        }

        if (
            option.valueType === settingOptionValueTypes.FLOAT ||
            option.valueType === settingOptionValueTypes.DECIMAL
        ) {
            if (Number.isNaN(Number(option.value))) {
                saveForm.setError(errorKey, "Value must be a valid number.");

                valid = false;
            }
        }

        if (
            option.valueType === settingOptionValueTypes.JSON ||
            option.valueType === settingOptionValueTypes.ARRAY
        ) {
            try {
                const parsed =
                    typeof option.value === "string"
                        ? JSON.parse(option.value)
                        : option.value;

                if (
                    option.valueType === settingOptionValueTypes.ARRAY &&
                    !Array.isArray(parsed)
                ) {
                    throw new Error();
                }

                if (
                    option.valueType === settingOptionValueTypes.JSON &&
                    (typeof parsed !== "object" ||
                        parsed === null ||
                        Array.isArray(parsed))
                ) {
                    throw new Error();
                }
            } catch {
                saveForm.setError(errorKey, "Value must be valid JSON.");

                valid = false;
            }
        }

        if (option.valueType === settingOptionValueTypes.URL) {
            try {
                new URL(option.value);
            } catch {
                saveForm.setError(errorKey, "Value must be a valid URL.");

                valid = false;
            }
        }
    });

    return valid;
};

const handleSave = () => {
    if (saveForm.processing) {
        return;
    }

    saveForm.clearErrors();

    if (!validateOptions()) {
        return;
    }

    saveForm
        .transform((data) => ({
            name: data.name,
            options: prepareOptionsForSubmit(),
            _method: "patch",
        }))
        .post(
            route("settings.update", {
                slug: setting?.slug,
            }),
            {
                preserveScroll: true,
                preserveState: true,
            },
        );
};

onMounted(async () => {
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

    <div class="w-full">
        <div
            class="bg-white border border-gray-200 rounded-2xl shadow-sm p-4 md:p-6"
        >
            <form @submit.prevent="handleSave" class="space-y-6">
                <div class="bg-white border rounded-xl p-5 shadow-sm space-y-4">
                    <h3 class="text-base font-semibold">Basic Information</h3>

                    <div>
                        <label class="block text-sm font-medium mb-1">
                            Name
                        </label>

                        <input
                            v-model="saveForm.name"
                            type="text"
                            readonly
                            class="w-full border border-gray-300 bg-gray-50 rounded-md px-3 py-2 text-sm cursor-not-allowed"
                        />

                        <p
                            v-if="saveForm.errors.name"
                            class="text-red-500 text-sm mt-1"
                        >
                            {{ saveForm.errors.name }}
                        </p>
                    </div>
                </div>

                <div class="bg-white border rounded-xl p-5 shadow-sm space-y-5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold">Options</h3>

                        <span class="text-sm text-gray-500">
                            {{ optionCount }}
                        </span>
                    </div>

                    <div v-if="optionCount" class="space-y-5">
                        <div
                            v-for="(option, key) in settingOptions"
                            :key="key"
                            class="border border-gray-200 rounded-xl p-5 space-y-4"
                        >
                            <div>
                                <label
                                    :for="formatOptionName(key)"
                                    class="block text-sm font-semibold text-gray-900 mb-1"
                                >
                                    {{ titleFormat(key) }}
                                </label>
                            </div>

                            <InfiniteScrollApiSelect
                                v-if="isAiBrainOption(key, option)"
                                :form="option"
                                fieldName="value"
                                :selectedItem="option.value"
                                :apiUrl="route('search.ai-brains')"
                                :multiple="false"
                                placeholder="Ai Brain"
                            />

                            <textarea
                                v-else-if="
                                    option.valueType ===
                                    settingOptionValueTypes.TEXT
                                "
                                :id="formatOptionName(key)"
                                v-model="option.value"
                                rows="4"
                                class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                :class="
                                    getOptionError(key)
                                        ? 'border-red-500'
                                        : 'border-gray-300'
                                "
                            ></textarea>

                            <input
                                v-else-if="
                                    option.valueType ===
                                    settingOptionValueTypes.STRING
                                "
                                :id="formatOptionName(key)"
                                v-model="option.value"
                                type="text"
                                class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                :class="
                                    getOptionError(key)
                                        ? 'border-red-500'
                                        : 'border-gray-300'
                                "
                            />

                            <div
                                v-else-if="
                                    option.valueType ===
                                    settingOptionValueTypes.BOOLEAN
                                "
                                class="flex items-center gap-3"
                            >
                                <button
                                    type="button"
                                    role="switch"
                                    :aria-checked="isTruthyValue(option.value)"
                                    :id="formatOptionName(key)"
                                    @click="
                                        option.value = !isTruthyValue(
                                            option.value,
                                        )
                                    "
                                    class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
                                    :class="
                                        isTruthyValue(option.value)
                                            ? 'bg-green-600'
                                            : 'bg-gray-300'
                                    "
                                >
                                    <span
                                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                        :class="
                                            isTruthyValue(option.value)
                                                ? 'translate-x-5'
                                                : 'translate-x-0'
                                        "
                                    ></span>
                                </button>

                                <span class="text-sm font-medium text-gray-700">
                                    {{
                                        isTruthyValue(option.value)
                                            ? "True"
                                            : "False"
                                    }}
                                </span>
                            </div>

                            <input
                                v-else-if="
                                    option.valueType ===
                                    settingOptionValueTypes.INTEGER
                                "
                                :id="formatOptionName(key)"
                                v-model="option.value"
                                type="number"
                                step="1"
                                class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                :class="
                                    getOptionError(key)
                                        ? 'border-red-500'
                                        : 'border-gray-300'
                                "
                            />

                            <input
                                v-else-if="
                                    option.valueType ===
                                        settingOptionValueTypes.FLOAT ||
                                    option.valueType ===
                                        settingOptionValueTypes.DECIMAL
                                "
                                :id="formatOptionName(key)"
                                v-model="option.value"
                                type="number"
                                step="any"
                                class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                :class="
                                    getOptionError(key)
                                        ? 'border-red-500'
                                        : 'border-gray-300'
                                "
                            />

                            <textarea
                                v-else-if="
                                    option.valueType ===
                                        settingOptionValueTypes.JSON ||
                                    option.valueType ===
                                        settingOptionValueTypes.ARRAY
                                "
                                :id="formatOptionName(key)"
                                :value="formatJsonValueForForm(option.value)"
                                @input="option.value = $event.target.value"
                                rows="6"
                                class="w-full border rounded-md px-3 py-2 text-sm font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                :class="
                                    getOptionError(key)
                                        ? 'border-red-500'
                                        : 'border-gray-300'
                                "
                            ></textarea>

                            <input
                                v-else-if="
                                    option.valueType ===
                                    settingOptionValueTypes.URL
                                "
                                :id="formatOptionName(key)"
                                v-model="option.value"
                                type="url"
                                class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                :class="
                                    getOptionError(key)
                                        ? 'border-red-500'
                                        : 'border-gray-300'
                                "
                            />

                            <div
                                v-else-if="
                                    option.valueType ===
                                    settingOptionValueTypes.IMAGE
                                "
                                class="space-y-3"
                            >
                                <img
                                    v-if="
                                        typeof option.value === 'string' &&
                                        option.value
                                    "
                                    :src="option.value"
                                    :alt="titleFormat(key)"
                                    class="max-w-xs max-h-40 object-contain rounded-md border border-gray-200"
                                />

                                <input
                                    :id="formatOptionName(key)"
                                    type="file"
                                    accept="image/*"
                                    @change="handleImageChange($event, key)"
                                    class="w-full border rounded-md px-3 py-2 text-sm"
                                />
                            </div>

                            <div
                                v-else-if="
                                    option.valueType ===
                                    settingOptionValueTypes.COLOR
                                "
                                class="flex gap-3"
                            >
                                <input
                                    v-model="option.value"
                                    type="color"
                                    class="w-16 h-10 border rounded-md"
                                />

                                <input
                                    v-model="option.value"
                                    type="text"
                                    class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                />
                            </div>

                            <input
                                v-else
                                :id="formatOptionName(key)"
                                v-model="option.value"
                                type="text"
                                class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                            />

                            <p
                                v-if="getOptionError(key)"
                                class="text-red-500 text-sm"
                            >
                                {{ getOptionError(key) }}
                            </p>
                        </div>
                    </div>

                    <div v-else class="py-10 text-center text-gray-500">
                        N/A
                    </div>
                </div>

                <div class="flex justify-center">
                    <button
                        type="submit"
                        :disabled="saveForm.processing || !optionCount"
                        class="text-white px-6 py-2 rounded-md flex items-center gap-2 transition"
                        :class="
                            saveForm.processing || !optionCount
                                ? 'bg-gray-400 cursor-not-allowed'
                                : 'bg-green-600 hover:bg-green-700'
                        "
                    >
                        <FontAwesomeIcon
                            v-if="saveForm.processing"
                            icon="spinner"
                            spin
                        />

                        <FontAwesomeIcon v-else icon="save" />

                        {{ saveForm.processing ? "Saving..." : "Save" }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>

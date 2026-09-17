<script setup>
import InfiniteScrollApiSelect from "@/components/common/multi-select/InfiniteScrollApiSelect.vue";
import { AiBrainOutputTypes } from "@/composables/useAiBrain";

import { ref, computed, watch } from "vue";
import { useForm, router as intertiaJsRoute } from "@inertiajs/vue3";

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library as FontAwesomeLibrary } from "@fortawesome/fontawesome-svg-core";
import { faLightbulb, faBrain } from "@fortawesome/free-solid-svg-icons";

FontAwesomeLibrary.add(faLightbulb, faBrain);

const emit = defineEmits(["completed", "submitting", "finished"]);

const { novel } = defineProps({
    novel: {
        type: Object,
        default: null,
    },
});

const isUpdate = computed(() => !!novel?.slug);

const foundationGeneratorForm = useForm({
    additional_information: novel?.additional_information ?? null,
    language_id: novel?.language_id ?? null,
    genre_ids: novel?.genres?.map((genre) => genre.id) ?? [],
    novel_type_id: novel?.novel_type_id ?? null,
    audience_id: novel?.audience_id ?? null,
    ai_brain_id: null,
});

const genresApiUrl = computed(() => {
    const audienceId = foundationGeneratorForm.audience_id;

    if (!audienceId) {
        return route("search.genres");
    }

    return `${route("search.genres")}?audience_id=${encodeURIComponent(
        audienceId,
    )}`;
});

const audienceDependentFieldsReset = ref(false);
const audienceDependentFieldsKey = ref(0);

watch(
    () => foundationGeneratorForm.audience_id,
    (newAudienceId, oldAudienceId) => {
        if (newAudienceId === oldAudienceId) {
            return;
        }

        audienceDependentFieldsReset.value = true;

        foundationGeneratorForm.language_id = null;
        foundationGeneratorForm.genre_ids = [];
        foundationGeneratorForm.novel_type_id = null;
        foundationGeneratorForm.additional_information = null;

        foundationGeneratorForm.clearErrors(
            "language_id",
            "genre_ids",
            "novel_type_id",
            "additional_information",
        );

        audienceDependentFieldsKey.value++;
    },
);

function buildAiBrainSearchUrl() {
    return route("search.ai-brains", {
        ai_brain_output_type_code: AiBrainOutputTypes.Text,
    });
}

const validateFoundation = () => {
    foundationGeneratorForm.clearErrors();

    let valid = true;

    if (!foundationGeneratorForm.language_id) {
        foundationGeneratorForm.setError("language_id", "Language is required");
        valid = false;
    }

    if (
        !Array.isArray(foundationGeneratorForm.genre_ids) ||
        foundationGeneratorForm.genre_ids.length === 0
    ) {
        foundationGeneratorForm.setError("genre_ids", "Genres is required");
        valid = false;
    }

    if (!foundationGeneratorForm.novel_type_id) {
        foundationGeneratorForm.setError(
            "novel_type_id",
            "Novel type is required",
        );
        valid = false;
    }

    if (!foundationGeneratorForm.audience_id) {
        foundationGeneratorForm.setError("audience_id", "Audience is required");
        valid = false;
    }

    return valid;
};

function submit() {
    if (foundationGeneratorForm.processing) {
        return;
    }

    if (!validateFoundation()) {
        return;
    }

    emit("submitting");

    const requestConfig = {
        preserveScroll: true,
        preserveState: true,
        onSuccess: () => {
            foundationGeneratorForm.clearErrors();
            emit("completed");
        },
        onError: (errors) => {
            foundationGeneratorForm.clearErrors();
            foundationGeneratorForm.setError(errors);
        },
        onFinish: () => {
            emit("finished");
        },
    };

    if (isUpdate.value) {
        intertiaJsRoute.post(
            route("back-office.novels.regenerate.foundation", {
                slug: novel?.slug,
            }),
            { ...foundationGeneratorForm.data(), _method: "patch" },
            requestConfig,
        );
    } else {
        foundationGeneratorForm.post(
            route("back-office.novels.create.foundation"),
            requestConfig,
        );
    }
}

defineExpose({ submit });
</script>

<template>
    <div class="space-y-6">
        <div class="bg-white border rounded-xl p-5 shadow-sm space-y-4">
            <h3 class="text-base font-semibold flex items-center gap-2">
                <FontAwesomeIcon icon="lightbulb" class="text-blue-600" />
                Foundation Configuration
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">
                        Audience
                        <span class="text-red-500">*</span>
                    </label>

                    <InfiniteScrollApiSelect
                        :form="foundationGeneratorForm"
                        fieldName="audience_id"
                        :selectedItem="novel?.audience"
                        :apiUrl="route('search.audiences')"
                        :multiple="false"
                        placeholder="Select audiences"
                        :error="foundationGeneratorForm.errors.audience_id"
                    />

                    <p
                        v-if="foundationGeneratorForm.errors.audience_id"
                        class="text-red-500 text-sm mt-1"
                    >
                        {{ foundationGeneratorForm.errors.audience_id }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Language
                        <span class="text-red-500">*</span>
                    </label>

                    <InfiniteScrollApiSelect
                        :key="`language-${audienceDependentFieldsKey}`"
                        :form="foundationGeneratorForm"
                        fieldName="language_id"
                        :selectedItem="
                            audienceDependentFieldsReset
                                ? null
                                : novel?.language
                        "
                        :apiUrl="route('search.languages')"
                        :multiple="false"
                        placeholder="Select languages"
                        :error="foundationGeneratorForm.errors.language_id"
                    />

                    <p v-if="foundationGeneratorForm.errors.language_id">
                        {{ foundationGeneratorForm.errors.language_id }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Genres
                        <span class="text-red-500">*</span>
                    </label>

                    <InfiniteScrollApiSelect
                        :key="`genres-${audienceDependentFieldsKey}`"
                        :form="foundationGeneratorForm"
                        fieldName="genre_ids"
                        :selectedItem="
                            audienceDependentFieldsReset ? null : novel?.genres
                        "
                        :apiUrl="genresApiUrl"
                        :multiple="true"
                        placeholder="Select genres"
                        :error="foundationGeneratorForm.errors.genre_ids"
                    />

                    <p
                        v-if="foundationGeneratorForm.errors.genre_ids"
                        class="text-red-500 text-sm mt-1"
                    >
                        {{ foundationGeneratorForm.errors.genre_ids }}
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">
                        Novel Type
                        <span class="text-red-500">*</span>
                    </label>

                    <InfiniteScrollApiSelect
                        :key="`novel-type-${audienceDependentFieldsKey}`"
                        :form="foundationGeneratorForm"
                        fieldName="novel_type_id"
                        :selectedItem="
                            audienceDependentFieldsReset
                                ? null
                                : novel?.novel_type
                        "
                        :apiUrl="route('search.novel-types')"
                        :multiple="false"
                        placeholder="Select novel types"
                        :error="foundationGeneratorForm.errors.novel_type_id"
                    />

                    <p
                        v-if="foundationGeneratorForm.errors.novel_type_id"
                        class="text-red-500 text-sm mt-1"
                    >
                        {{ foundationGeneratorForm.errors.novel_type_id }}
                    </p>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">
                        Additional Information
                    </label>

                    <textarea
                        v-model="foundationGeneratorForm.additional_information"
                        rows="3"
                        placeholder="Any additional context or instructions for the AI..."
                        class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none border-gray-300"
                    ></textarea>
                </div>
            </div>
        </div>

        <div class="bg-white border rounded-xl p-5 shadow-sm space-y-4">
            <h3 class="text-base font-semibold flex items-center gap-2">
                <FontAwesomeIcon icon="brain" class="text-purple-600" />
                AI Brain Configuration
            </h3>

            <p class="text-sm text-gray-500">
                Select the AI model that will be used for generating your novel.
            </p>

            <div
                class="border-2 border-dashed border-purple-200 rounded-xl p-4 bg-gradient-to-br from-purple-50 to-blue-50"
            >
                <InfiniteScrollApiSelect
                    :form="foundationGeneratorForm"
                    fieldName="ai_brain_id"
                    :selectedItem="novel?.ai_brain"
                    :apiUrl="buildAiBrainSearchUrl()"
                    :multiple="false"
                    placeholder="Select AI Brain"
                    :error="foundationGeneratorForm.errors.ai_brain_id"
                    class="ai-brain-select"
                />
            </div>

            <p
                v-if="foundationGeneratorForm.errors.ai_brain_id"
                class="text-red-500 text-sm"
            >
                {{ foundationGeneratorForm.errors.ai_brain_id }}
            </p>
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

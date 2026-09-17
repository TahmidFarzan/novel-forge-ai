<script setup>
import InfiniteScrollApiSelect from "@/components/common/multi-select/InfiniteScrollApiSelect.vue";
import { AiBrainOutputTypes } from "@/composables/useAiBrain";

import { computed } from "vue";
import { useForm, router as inertiaRoute } from "@inertiajs/vue3";

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library as FontAwesomeLibrary } from "@fortawesome/fontawesome-svg-core";
import {
    faBrain,
    faGlobe,
    faSpinner,
    faWandMagicSparkles,
} from "@fortawesome/free-solid-svg-icons";

FontAwesomeLibrary.add(faBrain, faGlobe, faSpinner, faWandMagicSparkles);

const emit = defineEmits(["completed"]);

const { novel } = defineProps({
    novel: {
        type: Object,
        required: true,
    },
});

const isUpdate = computed(() => !!novel?.id);

const worldBibleForm = useForm({
    foundation: novel?.foundation
        ? typeof novel.foundation === "string"
            ? novel.foundation
            : JSON.stringify(novel.foundation)
        : null,
    characters: novel?.characters
        ? typeof novel.characters === "string"
            ? novel.characters
            : JSON.stringify(novel.characters)
        : null,
    additional_information: null,
    ai_brain_id: null,
});

function buildAiBrainSearchUrl() {
    return route("search.ai-brains", {
        ai_brain_output_type_code: AiBrainOutputTypes.Text,
    });
}

const validate = () => {
    worldBibleForm.clearErrors();

    let valid = true;

    if (!worldBibleForm.ai_brain_id) {
        worldBibleForm.setError(
            "ai_brain_id",
            "AI Brain selection is required",
        );
        valid = false;
    }

    if (!worldBibleForm.foundation) {
        worldBibleForm.setError("foundation", "Foundation is required");
        valid = false;
    }

    if (!worldBibleForm.characters) {
        worldBibleForm.setError("characters", "Characters are required");
        valid = false;
    }

    return valid;
};

const handleSuccess = () => {
    worldBibleForm.clearErrors();
    emit("completed", novel);
};

const submit = () => {
    if (!isUpdate.value || worldBibleForm.processing || !validate()) {
        return;
    }

    const requestConfig = {
        preserveScroll: true,
        preserveState: true,
        onSuccess: handleSuccess,
    };

    inertiaRoute.patch(
        route("back-office.novels.regenerate.world-bible", {
            slug: novel?.slug,
        }),
        {
            ...worldBibleForm.data(),
            _method: "patch",
        },
        requestConfig,
    );
};

defineExpose({ submit });
</script>

<template>
    <div class="space-y-6">
        <div class="bg-white border rounded-xl p-5 shadow-sm space-y-4">
            <h3 class="text-base font-semibold flex items-center gap-2">
                <FontAwesomeIcon icon="globe" class="text-blue-600" />
                World Bible Generator
            </h3>

            <p class="text-sm text-gray-500">
                Build the world bible and setting rules
            </p>

            <div>
                <label class="block text-sm font-medium mb-1">
                    World Bible Additional Information
                </label>

                <textarea v-model="worldBibleForm.additional_information" rows="3"
                    placeholder="Any additional context or instructions for the AI..."
                    class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none border-gray-300"></textarea>

                <p v-if="worldBibleForm.errors.additional_information">
                    {{ worldBibleForm.errors.additional_information }}
                </p>
            </div>

            <p v-if="worldBibleForm.errors.foundation" class="text-red-500 text-sm">
                {{ worldBibleForm.errors.foundation }}
            </p>

            <p v-if="worldBibleForm.errors.characters" class="text-red-500 text-sm">
                {{ worldBibleForm.errors.characters }}
            </p>
        </div>

        <div class="bg-white border rounded-xl p-5 shadow-sm space-y-4">
            <div class="flex items-center gap-2">
                <FontAwesomeIcon icon="brain" class="text-purple-600" />
                <h3 class="text-base font-semibold">AI Brain Configuration</h3>
            </div>

            <p class="text-sm text-gray-500">
                Select the AI model that will generate the world bible.
            </p>

            <div class="border-2 border-dashed border-purple-200 rounded-xl p-4 bg-gradient-to-br from-purple-50 to-blue-50" >
                <InfiniteScrollApiSelect :form="worldBibleForm" fieldName="ai_brain_id"
                    :selectedItem="null" :apiUrl="buildAiBrainSearchUrl()" :multiple="false"
                    placeholder="Select AI Brain" :error="worldBibleForm.errors.ai_brain_id"
                    class="ai-brain-select"/>
            </div>

            <p v-if="worldBibleForm.errors.ai_brain_id" class="text-red-500 text-sm">
                {{ worldBibleForm.errors.ai_brain_id }}
            </p>
        </div>

        <div class="flex justify-end">
            <button type="button" @click="submit" :disabled="!isUpdate || worldBibleForm.processing"
                class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md flex items-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed">
                <FontAwesomeIcon v-if="worldBibleForm.processing" icon="spinner" spin/>
                <FontAwesomeIcon v-else icon="wand-magic-sparkles" />
                {{ worldBibleForm.processing ? "Generating..." : "Generate World Bible" }}
            </button>
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
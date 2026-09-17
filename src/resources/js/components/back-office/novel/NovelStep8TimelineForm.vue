<script setup>
import InfiniteScrollApiSelect from "@/components/common/multi-select/InfiniteScrollApiSelect.vue";
import { AiBrainOutputTypes } from "@/composables/useAiBrain";

import { computed } from "vue";
import { useForm, router as inertiaRoute } from "@inertiajs/vue3";

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library as FontAwesomeLibrary } from "@fortawesome/fontawesome-svg-core";
import {
    faBrain,
    faSpinner,
    faTimeline,
    faWandMagicSparkles,
} from "@fortawesome/free-solid-svg-icons";

FontAwesomeLibrary.add(faBrain, faSpinner, faTimeline, faWandMagicSparkles);

const emit = defineEmits(["completed"]);

const { novel } = defineProps({
    novel: {
        type: Object,
        required: true,
    },
});

const isUpdate = computed(() => !!novel?.id);

const timelineForm = useForm({
    world_bible: novel?.world_bible
        ? typeof novel.world_bible === "string"
            ? novel.world_bible
            : JSON.stringify(novel.world_bible)
        : null,
    factions: novel?.factions
        ? typeof novel.factions === "string"
            ? novel.factions
            : JSON.stringify(novel.factions)
        : null,
    foundation: novel?.foundation
        ? typeof novel.foundation === "string"
            ? novel.foundation
            : JSON.stringify(novel.foundation)
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
    timelineForm.clearErrors();

    let valid = true;

    if (!timelineForm.ai_brain_id) {
        timelineForm.setError(
            "ai_brain_id",
            "AI Brain selection is required",
        );
        valid = false;
    }

    if (!timelineForm.world_bible) {
        timelineForm.setError("world_bible", "World Bible is required");
        valid = false;
    }

    if (!timelineForm.factions) {
        timelineForm.setError("factions", "Factions are required");
        valid = false;
    }

    if (!timelineForm.foundation) {
        timelineForm.setError("foundation", "Foundation is required");
        valid = false;
    }

    return valid;
};

const handleSuccess = () => {
    timelineForm.clearErrors();
    emit("completed", novel);
};

const submit = () => {
    if (!isUpdate.value || timelineForm.processing || !validate()) {
        return;
    }

    const requestConfig = {
        preserveScroll: true,
        preserveState: true,
        onSuccess: handleSuccess,
    };

    inertiaRoute.patch(
        route("back-office.novels.regenerate.timeline", {
            slug: novel?.slug,
        }),
        {
            ...timelineForm.data(),
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
                <FontAwesomeIcon icon="timeline" class="text-blue-600" />
                Timeline Generator
            </h3>

            <p class="text-sm text-gray-500">
                Generate the story timeline
            </p>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Timeline Additional Information
                </label>

                <textarea v-model="timelineForm.additional_information" rows="3"
                    placeholder="Any additional context or instructions for the AI..."
                    class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none border-gray-300"></textarea>

                <p v-if="timelineForm.errors.additional_information">
                    {{ timelineForm.errors.additional_information }}
                </p>
            </div>

            <p v-if="timelineForm.errors.world_bible" class="text-red-500 text-sm">
                {{ timelineForm.errors.world_bible }}
            </p>

            <p v-if="timelineForm.errors.factions" class="text-red-500 text-sm">
                {{ timelineForm.errors.factions }}
            </p>

            <p v-if="timelineForm.errors.foundation" class="text-red-500 text-sm">
                {{ timelineForm.errors.foundation }}
            </p>
        </div>

        <div class="bg-white border rounded-xl p-5 shadow-sm space-y-4">
            <div class="flex items-center gap-2">
                <FontAwesomeIcon icon="brain" class="text-purple-600" />
                <h3 class="text-base font-semibold">AI Brain Configuration</h3>
            </div>

            <p class="text-sm text-gray-500">
                Select the AI model that will generate the timeline.
            </p>

            <div class="border-2 border-dashed border-purple-200 rounded-xl p-4 bg-gradient-to-br from-purple-50 to-blue-50" >
                <InfiniteScrollApiSelect :form="timelineForm" fieldName="ai_brain_id"
                    :selectedItem="null" :apiUrl="buildAiBrainSearchUrl()" :multiple="false"
                    placeholder="Select AI Brain" :error="timelineForm.errors.ai_brain_id"
                    class="ai-brain-select"/>
            </div>

            <p v-if="timelineForm.errors.ai_brain_id" class="text-red-500 text-sm">
                {{ timelineForm.errors.ai_brain_id }}
            </p>
        </div>

        <div class="flex justify-end">
            <button type="button" @click="submit" :disabled="!isUpdate || timelineForm.processing"
                class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md flex items-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed">
                <FontAwesomeIcon v-if="timelineForm.processing" icon="spinner" spin/>
                <FontAwesomeIcon v-else icon="wand-magic-sparkles" />
                {{ timelineForm.processing ? "Generating..." : "Generate Timeline" }}
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
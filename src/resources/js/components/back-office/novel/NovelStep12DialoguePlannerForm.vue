<script setup>
import InfiniteScrollApiSelect from "@/components/common/multi-select/InfiniteScrollApiSelect.vue";
import { AiBrainOutputTypes } from "@/composables/useAiBrain";

import { useForm } from "@inertiajs/vue3";

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library as FontAwesomeLibrary } from "@fortawesome/fontawesome-svg-core";
import { faComments } from "@fortawesome/free-solid-svg-icons";

FontAwesomeLibrary.add(faComments);

const emit = defineEmits(["completed"]);

const { novel } = defineProps({
    novel: {
        type: Object,
        default: null,
    },
});

const dialoguePlansForm = useForm({
    ai_brain_id: null,
});

function buildAiBrainSearchUrl() {
    return route("search.ai-brains", {
        ai_brain_output_type_code: AiBrainOutputTypes.Text,
    });
}

function submit() {
    if (dialoguePlansForm.processing) {
        return;
    }

    dialoguePlansForm.clearErrors();

    if (!dialoguePlansForm.ai_brain_id) {
        dialoguePlansForm.setError(
            "ai_brain_id",
            "AI Brain selection is required",
        );
        return;
    }

    emit("completed");
}

defineExpose({ submit });
</script>

<template>
    <div class="space-y-6">
        <div
            class="bg-white border rounded-xl p-5 shadow-sm space-y-4"
        >
            <h3
                class="text-base font-semibold flex items-center gap-2"
            >
                <FontAwesomeIcon
                    icon="comments"
                    class="text-blue-600"
                />
                Dialogue Planner
            </h3>

            <p class="text-sm text-gray-500">
                Plan character dialogue
            </p>

            <div
                class="border-2 border-dashed border-purple-200 rounded-xl p-4 bg-gradient-to-br from-purple-50 to-blue-50"
            >
                <InfiniteScrollApiSelect
                    :form="dialoguePlansForm"
                    fieldName="ai_brain_id"
                    :selectedItem="dialoguePlansForm.ai_brain_id"
                    :apiUrl="buildAiBrainSearchUrl()"
                    :multiple="false"
                    placeholder="Select AI Brain"
                    :error="dialoguePlansForm.errors.ai_brain_id"
                    class="ai-brain-select"
                />
            </div>

            <p
                v-if="dialoguePlansForm.errors.ai_brain_id"
                class="text-red-500 text-sm"
            >
                {{ dialoguePlansForm.errors.ai_brain_id }}
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
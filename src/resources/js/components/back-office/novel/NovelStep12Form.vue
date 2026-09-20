<script setup>
import InfiniteScrollApiSelect from "@/components/common/multi-select/InfiniteScrollApiSelect.vue";
import { AiBrainOutputTypes } from "@/composables/useAiBrain";

import { computed } from "vue";
import { useForm, router as inertiaRoute } from "@inertiajs/vue3";

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library as FontAwesomeLibrary } from "@fortawesome/fontawesome-svg-core";
import {
    faBrain,
    faComments,
    faSpinner,
    faWandMagicSparkles,
} from "@fortawesome/free-solid-svg-icons";

FontAwesomeLibrary.add(faBrain, faComments, faSpinner, faWandMagicSparkles);

const emit = defineEmits(["completed"]);

const { novel } = defineProps({
    novel: {
        type: Object,
        required: true,
    },
});

const isUpdate = computed(() => !!novel?.id);

const dialoguePlansForm = useForm({
    characters: novel?.characters
        ? typeof novel.characters === "string"
            ? novel.characters
            : JSON.stringify(novel.characters)
        : null,
    scene_plans: novel?.scene_plans
        ? typeof novel.scene_plans === "string"
            ? novel.scene_plans
            : JSON.stringify(novel.scene_plans)
        : null,
    ai_brain_id: null,
});

function buildAiBrainSearchUrl() {
    return route("search.ai-brains", {
        ai_brain_output_type_code: AiBrainOutputTypes.Text,
    });
}

const validate = () => {
    dialoguePlansForm.clearErrors();

    let valid = true;

    if (!dialoguePlansForm.ai_brain_id) {
        dialoguePlansForm.setError(
            "ai_brain_id",
            "AI Brain selection is required",
        );
        valid = false;
    }

    if (!dialoguePlansForm.characters) {
        dialoguePlansForm.setError("characters", "Characters are required");
        valid = false;
    }

    if (!dialoguePlansForm.scene_plans) {
        dialoguePlansForm.setError("scene_plans", "Scene Plans are required");
        valid = false;
    }

    return valid;
};

const handleSuccess = () => {
    dialoguePlansForm.clearErrors();
    emit("completed", novel);
};

const submit = () => {
    if (!isUpdate.value || dialoguePlansForm.processing || !validate()) {
        return;
    }

    const requestConfig = {
        preserveScroll: true,
        preserveState: true,
        onSuccess: handleSuccess,
    };

    inertiaRoute.patch(
        route("back-office.novels.generate.dialogue-planner", {
            slug: novel?.slug,
        }),
        {
            ...dialoguePlansForm.data(),
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
                <FontAwesomeIcon icon="comments" class="text-blue-600" />
                Dialogue Planner
            </h3>

            <p class="text-sm text-gray-500">
                Plan character dialogue
            </p>

            <p v-if="dialoguePlansForm.errors.characters" class="text-red-500 text-sm">
                {{ dialoguePlansForm.errors.characters }}
            </p>

            <p v-if="dialoguePlansForm.errors.scene_plans" class="text-red-500 text-sm">
                {{ dialoguePlansForm.errors.scene_plans }}
            </p>
        </div>

        <div class="bg-white border rounded-xl p-5 shadow-sm space-y-4">
            <div class="flex items-center gap-2">
                <FontAwesomeIcon icon="brain" class="text-purple-600" />
                <h3 class="text-base font-semibold">AI Brain Configuration</h3>
            </div>

            <p class="text-sm text-gray-500">
                Select the AI model that will generate the dialogue plans.
            </p>

            <div class="border-2 border-dashed border-purple-200 rounded-xl p-4 bg-gradient-to-br from-purple-50 to-blue-50" >
                <InfiniteScrollApiSelect :form="dialoguePlansForm" fieldName="ai_brain_id"
                    :selectedItem="null" :apiUrl="buildAiBrainSearchUrl()" :multiple="false"
                    placeholder="Select AI Brain" :error="dialoguePlansForm.errors.ai_brain_id"
                    class="ai-brain-select"/>
            </div>

            <p v-if="dialoguePlansForm.errors.ai_brain_id" class="text-red-500 text-sm">
                {{ dialoguePlansForm.errors.ai_brain_id }}
            </p>
        </div>

        <div class="flex justify-end">
            <button type="button" @click="submit" :disabled="!isUpdate || dialoguePlansForm.processing"
                class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md flex items-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed">
                <FontAwesomeIcon v-if="dialoguePlansForm.processing" icon="spinner" spin/>
                <FontAwesomeIcon v-else icon="wand-magic-sparkles" />
                {{ dialoguePlansForm.processing ? "Generating..." : "Generate Dialogue Plans" }}
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
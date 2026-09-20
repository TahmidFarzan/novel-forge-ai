<script setup>
import InfiniteScrollApiSelect from "@/components/common/multi-select/InfiniteScrollApiSelect.vue";
import { AiBrainOutputTypes } from "@/composables/useAiBrain";

import { computed } from "vue";
import { useForm, router as inertiaRoute } from "@inertiajs/vue3";

import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";
import { library as FontAwesomeLibrary } from "@fortawesome/fontawesome-svg-core";
import {
    faBrain,
    faPaw,
    faSpinner,
    faWandMagicSparkles,
} from "@fortawesome/free-solid-svg-icons";

FontAwesomeLibrary.add(faBrain, faPaw, faSpinner, faWandMagicSparkles);

const emit = defineEmits(["completed"]);

const { novel } = defineProps({
    novel: {
        type: Object,
        required: true,
    },
});

const isUpdate = computed(() => !!novel?.id);

const creaturesForm = useForm({
    world_bible: novel?.world_bible
        ? typeof novel.world_bible === "string"
            ? novel.world_bible
            : JSON.stringify(novel.world_bible)
        : null,
    locations: novel?.locations
        ? typeof novel.locations === "string"
            ? novel.locations
            : JSON.stringify(novel.locations)
        : null,
    factions: novel?.factions
        ? typeof novel.factions === "string"
            ? novel.factions
            : JSON.stringify(novel.factions)
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
    creaturesForm.clearErrors();

    let valid = true;

    if (!creaturesForm.ai_brain_id) {
        creaturesForm.setError(
            "ai_brain_id",
            "AI Brain selection is required",
        );
        valid = false;
    }

    if (!creaturesForm.world_bible) {
        creaturesForm.setError("world_bible", "World Bible is required");
        valid = false;
    }

    if (!creaturesForm.locations) {
        creaturesForm.setError("locations", "Locations are required");
        valid = false;
    }

    if (!creaturesForm.factions) {
        creaturesForm.setError("factions", "Factions are required");
        valid = false;
    }

    return valid;
};

const handleSuccess = () => {
    creaturesForm.clearErrors();
    emit("completed", novel);
};

const submit = () => {
    if (!isUpdate.value || creaturesForm.processing || !validate()) {
        return;
    }

    const requestConfig = {
        preserveScroll: true,
        preserveState: true,
        onSuccess: handleSuccess,
    };

    inertiaRoute.patch(
        route("back-office.novels.generate.creature", {
            slug: novel?.slug,
        }),
        {
            ...creaturesForm.data(),
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
                <FontAwesomeIcon icon="paw" class="text-blue-600" />
                Creature / Being Generator
            </h3>

            <p class="text-sm text-gray-500">
                Generate creatures and other beings
            </p>

            <div>
                <label class="block text-sm font-medium mb-1">
                    Creatures Additional Information
                </label>

                <textarea v-model="creaturesForm.additional_information" rows="3"
                    placeholder="Any additional context or instructions for the AI..."
                    class="w-full border rounded-md px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none border-gray-300"></textarea>

                <p v-if="creaturesForm.errors.additional_information">
                    {{ creaturesForm.errors.additional_information }}
                </p>
            </div>

            <p v-if="creaturesForm.errors.world_bible" class="text-red-500 text-sm">
                {{ creaturesForm.errors.world_bible }}
            </p>

            <p v-if="creaturesForm.errors.locations" class="text-red-500 text-sm">
                {{ creaturesForm.errors.locations }}
            </p>

            <p v-if="creaturesForm.errors.factions" class="text-red-500 text-sm">
                {{ creaturesForm.errors.factions }}
            </p>
        </div>

        <div class="bg-white border rounded-xl p-5 shadow-sm space-y-4">
            <div class="flex items-center gap-2">
                <FontAwesomeIcon icon="brain" class="text-purple-600" />
                <h3 class="text-base font-semibold">AI Brain Configuration</h3>
            </div>

            <p class="text-sm text-gray-500">
                Select the AI model that will generate the creatures.
            </p>

            <div class="border-2 border-dashed border-purple-200 rounded-xl p-4 bg-gradient-to-br from-purple-50 to-blue-50" >
                <InfiniteScrollApiSelect :form="creaturesForm" fieldName="ai_brain_id"
                    :selectedItem="null" :apiUrl="buildAiBrainSearchUrl()" :multiple="false"
                    placeholder="Select AI Brain" :error="creaturesForm.errors.ai_brain_id"
                    class="ai-brain-select"/>
            </div>

            <p v-if="creaturesForm.errors.ai_brain_id" class="text-red-500 text-sm">
                {{ creaturesForm.errors.ai_brain_id }}
            </p>
        </div>

        <div class="flex justify-end">
            <button type="button" @click="submit" :disabled="!isUpdate || creaturesForm.processing"
                class="px-5 py-2 text-sm bg-blue-600 hover:bg-blue-700 text-white rounded-md flex items-center gap-2 transition disabled:opacity-60 disabled:cursor-not-allowed">
                <FontAwesomeIcon v-if="creaturesForm.processing" icon="spinner" spin/>
                <FontAwesomeIcon v-else icon="wand-magic-sparkles" />
                {{ creaturesForm.processing ? "Generating..." : "Generate Creatures" }}
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

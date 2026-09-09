<script setup>
import { ref, computed } from "vue";
import { usePage } from "@inertiajs/vue3";

import { library } from "@fortawesome/fontawesome-svg-core";
import { FontAwesomeIcon } from "@fortawesome/vue-fontawesome";

import {
    faUser,
    faUsers,
    faChevronDown,
    faChevronUp,
    faGauge,
    faPhotoFilm,
    faBrain,
    faBookOpen,
    faGears,
    faTableColumns,
    faFileLines,
    faClipboardList,
    faLanguage,
} from "@fortawesome/free-solid-svg-icons";

library.add(
    faUser,
    faUsers,
    faChevronDown,
    faChevronUp,
    faGauge,
    faPhotoFilm,
    faBrain,
    faBookOpen,
    faGears,
    faTableColumns,
    faFileLines,
    faClipboardList,
    faLanguage,
);

import {
    canAccessUser,
    canAccessGenre,
    canAccessLanguage,
    canAccessAiBrain,
    canAccessKdpLayout,
    canAccessDocumentStyle,
    canAccessAiPrompt,
} from "@/composables/useUserPermissions";

const { authUser } = defineProps({
    authUser: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(["navigate"]);

const page = usePage();

const subMenus = ref({
    UserManagement: false,
    Reports: false,
    AiAttributes: false,
    NovelAttributes: false,
    Configuration: false,
});

const routeMap = {
    UserManagement: ["/users/*"],
    AiAttributes: ["/ai-brains/*"],
    NovelAttributes: ["/genres/*", "/languages/*", "/kdp-layouts/*"],
    Configuration: ["/document-styles/*"],
    Reports: ["/reports/*"],
};

const canAccessUserComputed = computed(() => {
    return canAccessUser(authUser);
});

const canAccessAiBrainComputed = computed(() => {
    return canAccessAiBrain(authUser);
});

const canAccessGenreComputed = computed(() => {
    return canAccessGenre(authUser);
});

const canAccessLanguageComputed = computed(() => {
    return canAccessLanguage(authUser);
});

const canAccessKdpLayoutComputed = computed(() => {
    return canAccessKdpLayout(authUser);
});

const canAccessDocumentStyleComputed = computed(() => {
    return canAccessDocumentStyle(authUser);
});

const canAccessAiPromptComputed = computed(() => {
    return canAccessAiPrompt(authUser);
});

const toggleShowSubMenu = (key) => {
    subMenus.value[key] = !subMenus.value[key];
};

const handleNavigate = () => {
    emit("navigate");
};

const isCurrentPage = (url) => {
    const currentUrl =
        typeof page.url === "string"
            ? page.url.split("?")[0].replace(/\/+$/, "")
            : "";

    const cleanUrl = url.replace(/\/+$/, "");

    if (cleanUrl.endsWith("/*")) {
        const basePattern = cleanUrl.slice(0, -2);

        return (
            currentUrl === basePattern ||
            currentUrl.startsWith(`${basePattern}/`)
        );
    }

    return currentUrl === cleanUrl;
};

const isAnyCurrentPage = (urls = []) => {
    return urls.some((url) => isCurrentPage(url));
};

const isSubMenuVisible = (key) => {
    const routes = routeMap[key] || [];
    const inRoute = isAnyCurrentPage(routes);

    return subMenus.value[key] || inRoute;
};
</script>

<template>
    <div class="flex flex-col space-y-1 text-sm">
        <a
            :href="route('auth-user.dashboard.index')"
            class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100"
            :class="
                isCurrentPage('/auth-user/dashboard/*')
                    ? 'bg-gray-200 font-medium'
                    : ''
            "
        >
            <FontAwesomeIcon icon="gauge" />
            Dashboard
        </a>

        <a
            :href="route('medias.index')"
            class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100"
            :class="isCurrentPage('/medias/*') ? 'bg-gray-200 font-medium' : ''"
        >
            <FontAwesomeIcon icon="photo-film" />
            Media
        </a>

        <a
            :href="route('novel-generators.index')"
            class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100"
            :class="
                isCurrentPage('/auth-user/novel-generators/*')
                    ? 'bg-gray-200 font-medium'
                    : ''
            "
        >
            <!-- <FontAwesomeIcon icon="gauge" /> -->
            Novel generators
        </a>

        <button
            @click="toggleShowSubMenu('NovelAttributes')"
            class="flex items-center justify-between w-full px-3 py-2 rounded hover:bg-gray-100"
        >
            <span class="flex items-center gap-2">
                <FontAwesomeIcon icon="book-open" />
                Novel Attribute
            </span>

            <FontAwesomeIcon
                :icon="
                    isSubMenuVisible('NovelAttributes')
                        ? 'chevron-up'
                        : 'chevron-down'
                "
            />
        </button>

        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 max-h-0"
            enter-to-class="opacity-100 max-h-60"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 max-h-60"
            leave-to-class="opacity-0 max-h-0"
        >
            <div
                v-if="isSubMenuVisible('NovelAttributes')"
                class="ml-4 flex flex-col space-y-1 overflow-hidden"
            >
                <a
                    v-if="canAccessGenreComputed"
                    :href="route('genres.index')"
                    class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100"
                    :class="
                        isCurrentPage('/genres/*')
                            ? 'bg-gray-200 font-medium'
                            : ''
                    "
                >
                    <FontAwesomeIcon icon="book-open" />
                    Genre
                </a>

                <a
                    v-if="canAccessLanguageComputed"
                    :href="route('languages.index')"
                    class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100"
                    :class="
                        isCurrentPage('/languages/*')
                            ? 'bg-gray-200 font-medium'
                            : ''
                    "
                >
                    <FontAwesomeIcon icon="language" />
                    Languages
                </a>

                <a
                    v-if="canAccessKdpLayoutComputed"
                    :href="route('kdp-layouts.index')"
                    class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100"
                    :class="
                        isCurrentPage('/kdp-layouts/*')
                            ? 'bg-gray-200 font-medium'
                            : ''
                    "
                >
                    <FontAwesomeIcon icon="table-columns" />
                    Layout
                </a>
            </div>
        </Transition>

        <button
            @click="toggleShowSubMenu('AiAttributes')"
            class="flex items-center justify-between w-full px-3 py-2 rounded hover:bg-gray-100"
        >
            <span class="flex items-center gap-2">
                <FontAwesomeIcon icon="brain" />
                Ai Attributes
            </span>

            <FontAwesomeIcon
                :icon="
                    isSubMenuVisible('AiAttributes')
                        ? 'chevron-up'
                        : 'chevron-down'
                "
            />
        </button>

        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 max-h-0"
            enter-to-class="opacity-100 max-h-40"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 max-h-40"
            leave-to-class="opacity-0 max-h-0"
        >
            <div
                v-if="isSubMenuVisible('AiAttributes')"
                class="ml-4 flex flex-col space-y-1 overflow-hidden"
            >
                <a
                    v-if="canAccessAiBrainComputed"
                    :href="route('ai-brains.index')"
                    class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100"
                    :class="
                        isCurrentPage('/ai-brains/*')
                            ? 'bg-gray-200 font-medium'
                            : ''
                    "
                >
                    <FontAwesomeIcon icon="brain" />
                    Ai Brain
                </a>

                <a
                    v-if="canAccessAiPromptComputed"
                    :href="route('ai-prompts.index')"
                    class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100"
                    :class="
                        isCurrentPage('/ai-prompts/*')
                            ? 'bg-gray-200 font-medium'
                            : ''
                    "
                >
                    <FontAwesomeIcon icon="clipboard-list" />
                    Ai Prompt
                </a>
            </div>
        </Transition>

        <button
            @click="toggleShowSubMenu('UserManagement')"
            class="flex items-center justify-between w-full px-3 py-2 rounded hover:bg-gray-100"
        >
            <span class="flex items-center gap-2">
                <FontAwesomeIcon icon="users" />
                User Management
            </span>

            <FontAwesomeIcon
                :icon="
                    isSubMenuVisible('UserManagement')
                        ? 'chevron-up'
                        : 'chevron-down'
                "
            />
        </button>

        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 max-h-0"
            enter-to-class="opacity-100 max-h-40"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 max-h-40"
            leave-to-class="opacity-0 max-h-0"
        >
            <div
                v-if="
                    isSubMenuVisible('UserManagement') && canAccessUserComputed
                "
                class="ml-4 flex flex-col space-y-1 overflow-hidden"
            >
                <a
                    :href="route('users.index')"
                    class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100"
                    :class="
                        isAnyCurrentPage(routeMap.UserManagement)
                            ? 'bg-gray-200 font-medium'
                            : ''
                    "
                >
                    <FontAwesomeIcon icon="user" />
                    Users
                </a>
            </div>
        </Transition>

        <button
            @click="toggleShowSubMenu('Configuration')"
            class="flex items-center justify-between w-full px-3 py-2 rounded hover:bg-gray-100"
        >
            <span class="flex items-center gap-2">
                <FontAwesomeIcon icon="file-lines" />
                Configuration
            </span>

            <FontAwesomeIcon
                :icon="
                    isSubMenuVisible('Configuration')
                        ? 'chevron-up'
                        : 'chevron-down'
                "
            />
        </button>

        <Transition
            enter-active-class="transition-all duration-300 ease-out"
            enter-from-class="opacity-0 max-h-0"
            enter-to-class="opacity-100 max-h-40"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 max-h-40"
            leave-to-class="opacity-0 max-h-0"
        >
            <div
                v-if="isSubMenuVisible('Configuration')"
                class="ml-4 flex flex-col space-y-1 overflow-hidden"
            >
                <a
                    v-if="canAccessDocumentStyleComputed"
                    :href="route('document-styles.index')"
                    class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100"
                    :class="
                        isCurrentPage('/document-styles/*')
                            ? 'bg-gray-200 font-medium'
                            : ''
                    "
                >
                    <FontAwesomeIcon icon="file-lines" />
                    Document Style
                </a>
            </div>
        </Transition>
    </div>
</template>

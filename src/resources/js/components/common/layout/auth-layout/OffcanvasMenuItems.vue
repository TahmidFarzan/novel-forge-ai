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
    faBook,
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
    faBook
);

import {
    canAccessUser,
    canAccessGenre,
    canAccessAudience,
    canAccessNovelType,
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
    UserManagement: ["/back-office/users/*"],
    AiAttributes: ["/back-office/ai-brains/*"],
    NovelAttributes: ["/back-office/genres/*", "/back-office/audiences/*", "/back-office/novel-types/*", "/back-office/languages/*", "/back-office/kdp-layouts/*"],
    Configuration: ["/back-office/document-styles/*"],
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

const canAccessAudienceComputed = computed(() => {
    return canAccessAudience(authUser);
});

const canAccessNovelTypeComputed = computed(() => {
    return canAccessNovelType(authUser);
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
            class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-[var(--novel-forge-ai-ink-soft)] transition-colors duration-150 hover:bg-[var(--novel-forge-ai-primary-soft)] hover:text-[var(--novel-forge-ai-primary-strong)]"
            :class="isCurrentPage('/auth-user/dashboard/*') ? 'bg-[var(--novel-forge-ai-primary-soft)] font-semibold text-[var(--novel-forge-ai-primary-strong)]' : ''"
        >
            <FontAwesomeIcon icon="gauge" class="w-4" />
            Dashboard
        </a>

        <a
            :href="route('back-office.medias.index')"
            class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-[var(--novel-forge-ai-ink-soft)] transition-colors duration-150 hover:bg-[var(--novel-forge-ai-primary-soft)] hover:text-[var(--novel-forge-ai-primary-strong)]"
            :class="isCurrentPage('/back-office/medias/*') ? 'bg-[var(--novel-forge-ai-primary-soft)] font-semibold text-[var(--novel-forge-ai-primary-strong)]' : ''"
        >
            <FontAwesomeIcon icon="photo-film" class="w-4" />
            Media
        </a>

        <a
            :href="route('back-office.novels.index')"
            class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-[var(--novel-forge-ai-ink-soft)] transition-colors duration-150 hover:bg-[var(--novel-forge-ai-primary-soft)] hover:text-[var(--novel-forge-ai-primary-strong)]"
            :class="isCurrentPage('/back-office/novel/*') ? 'bg-[var(--novel-forge-ai-primary-soft)] font-semibold text-[var(--novel-forge-ai-primary-strong)]' : ''"
        >
            <FontAwesomeIcon icon="book" />
            Novels
        </a>

        <button
            @click="toggleShowSubMenu('NovelAttributes')"
            class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 font-medium text-[var(--novel-forge-ai-ink-soft)] transition-colors duration-150 hover:bg-[var(--novel-forge-ai-primary-soft)] hover:text-[var(--novel-forge-ai-primary-strong)]"
        >
            <span class="flex items-center gap-3">
                <FontAwesomeIcon icon="book-open" class="w-4" />
                Novel Attribute
            </span>

            <FontAwesomeIcon
                :icon="isSubMenuVisible('NovelAttributes') ? 'chevron-up' : 'chevron-down'"
                class="text-xs text-[var(--novel-forge-ai-muted)]"
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
                class="ml-2 flex flex-col space-y-1 overflow-hidden border-l border-[var(--novel-forge-ai-border)] pl-2"
            >
                <a
                    v-if="canAccessGenreComputed"
                    :href="route('back-office.genres.index')"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-[var(--novel-forge-ai-ink-soft)] transition-colors duration-150 hover:bg-[var(--novel-forge-ai-primary-soft)] hover:text-[var(--novel-forge-ai-primary-strong)]"
                    :class="isCurrentPage('/back-office/genres/*') ? 'bg-[var(--novel-forge-ai-primary-soft)] font-semibold text-[var(--novel-forge-ai-primary-strong)]' : ''"
                >
                    <FontAwesomeIcon icon="book-open" class="w-4" />
                    Genre
                </a>

                <a
                    v-if="canAccessAudienceComputed"
                    :href="route('back-office.audiences.index')"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-[var(--novel-forge-ai-ink-soft)] transition-colors duration-150 hover:bg-[var(--novel-forge-ai-primary-soft)] hover:text-[var(--novel-forge-ai-primary-strong)]"
                    :class="isCurrentPage('/back-office/audiences/*') ? 'bg-[var(--novel-forge-ai-primary-soft)] font-semibold text-[var(--novel-forge-ai-primary-strong)]' : ''"
                >
                    <FontAwesomeIcon icon="book-open" class="w-4" />
                    Audience
                </a>

                <a
                    v-if="canAccessNovelTypeComputed"
                    :href="route('back-office.novel-types.index')"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-[var(--novel-forge-ai-ink-soft)] transition-colors duration-150 hover:bg-[var(--novel-forge-ai-primary-soft)] hover:text-[var(--novel-forge-ai-primary-strong)]"
                    :class="isCurrentPage('/back-office/novel-types/*') ? 'bg-[var(--novel-forge-ai-primary-soft)] font-semibold text-[var(--novel-forge-ai-primary-strong)]' : ''"
                >
                    <FontAwesomeIcon icon="book-open" class="w-4" />
                    Novel Type
                </a>

                <a
                    v-if="canAccessLanguageComputed"
                    :href="route('back-office.languages.index')"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-[var(--novel-forge-ai-ink-soft)] transition-colors duration-150 hover:bg-[var(--novel-forge-ai-primary-soft)] hover:text-[var(--novel-forge-ai-primary-strong)]"
                    :class="isCurrentPage('/back-office/languages/*') ? 'bg-[var(--novel-forge-ai-primary-soft)] font-semibold text-[var(--novel-forge-ai-primary-strong)]' : ''"
                >
                    <FontAwesomeIcon icon="language" class="w-4" />
                    Languages
                </a>

                <a
                    v-if="canAccessKdpLayoutComputed"
                    :href="route('back-office.kdp-layouts.index')"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-[var(--novel-forge-ai-ink-soft)] transition-colors duration-150 hover:bg-[var(--novel-forge-ai-primary-soft)] hover:text-[var(--novel-forge-ai-primary-strong)]"
                    :class="isCurrentPage('/back-office/kdp-layouts/*') ? 'bg-[var(--novel-forge-ai-primary-soft)] font-semibold text-[var(--novel-forge-ai-primary-strong)]' : ''"
                >
                    <FontAwesomeIcon icon="table-columns" class="w-4" />
                    Layout
                </a>
            </div>
        </Transition>

        <button
            @click="toggleShowSubMenu('AiAttributes')"
            class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 font-medium text-[var(--novel-forge-ai-ink-soft)] transition-colors duration-150 hover:bg-[var(--novel-forge-ai-primary-soft)] hover:text-[var(--novel-forge-ai-primary-strong)]"
        >
            <span class="flex items-center gap-3">
                <FontAwesomeIcon icon="brain" class="w-4" />
                Ai Attributes
            </span>

            <FontAwesomeIcon
                :icon="isSubMenuVisible('AiAttributes') ? 'chevron-up' : 'chevron-down'"
                class="text-xs text-[var(--novel-forge-ai-muted)]"
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
                class="ml-2 flex flex-col space-y-1 overflow-hidden border-l border-[var(--novel-forge-ai-border)] pl-2"
            >
                <a
                    v-if="canAccessAiBrainComputed"
                    :href="route('back-office.ai-brains.index')"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-[var(--novel-forge-ai-ink-soft)] transition-colors duration-150 hover:bg-[var(--novel-forge-ai-primary-soft)] hover:text-[var(--novel-forge-ai-primary-strong)]"
                    :class="isCurrentPage('/back-office/ai-brains/*') ? 'bg-[var(--novel-forge-ai-primary-soft)] font-semibold text-[var(--novel-forge-ai-primary-strong)]' : ''"
                >
                    <FontAwesomeIcon icon="brain" class="w-4" />
                    Ai Brain
                </a>

                <a
                    v-if="canAccessAiPromptComputed"
                    :href="route('back-office.ai-prompts.index')"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-[var(--novel-forge-ai-ink-soft)] transition-colors duration-150 hover:bg-[var(--novel-forge-ai-primary-soft)] hover:text-[var(--novel-forge-ai-primary-strong)]"
                    :class="isCurrentPage('/back-office/ai-prompts/*') ? 'bg-[var(--novel-forge-ai-primary-soft)] font-semibold text-[var(--novel-forge-ai-primary-strong)]' : ''"
                >
                    <FontAwesomeIcon icon="clipboard-list" class="w-4" />
                    Ai Prompt
                </a>
            </div>
        </Transition>

        <button
            @click="toggleShowSubMenu('UserManagement')"
            class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 font-medium text-[var(--novel-forge-ai-ink-soft)] transition-colors duration-150 hover:bg-[var(--novel-forge-ai-primary-soft)] hover:text-[var(--novel-forge-ai-primary-strong)]"
        >
            <span class="flex items-center gap-3">
                <FontAwesomeIcon icon="users" class="w-4" />
                User Management
            </span>

            <FontAwesomeIcon
                :icon="isSubMenuVisible('UserManagement') ? 'chevron-up' : 'chevron-down'"
                class="text-xs text-[var(--novel-forge-ai-muted)]"
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
                v-if="isSubMenuVisible('UserManagement') && canAccessUserComputed"
                class="ml-2 flex flex-col space-y-1 overflow-hidden border-l border-[var(--novel-forge-ai-border)] pl-2"
            >
                <a
                    :href="route('back-office.users.index')"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-[var(--novel-forge-ai-ink-soft)] transition-colors duration-150 hover:bg-[var(--novel-forge-ai-primary-soft)] hover:text-[var(--novel-forge-ai-primary-strong)]"
                    :class="isAnyCurrentPage(routeMap.UserManagement) ? 'bg-[var(--novel-forge-ai-primary-soft)] font-semibold text-[var(--novel-forge-ai-primary-strong)]' : ''"
                >
                    <FontAwesomeIcon icon="user" class="w-4" />
                    Users
                </a>
            </div>
        </Transition>

        <button
            @click="toggleShowSubMenu('Configuration')"
            class="flex w-full items-center justify-between gap-2 rounded-lg px-3 py-2 font-medium text-[var(--novel-forge-ai-ink-soft)] transition-colors duration-150 hover:bg-[var(--novel-forge-ai-primary-soft)] hover:text-[var(--novel-forge-ai-primary-strong)]"
        >
            <span class="flex items-center gap-3">
                <FontAwesomeIcon icon="file-lines" class="w-4" />
                Configuration
            </span>

            <FontAwesomeIcon
                :icon="isSubMenuVisible('Configuration') ? 'chevron-up' : 'chevron-down'"
                class="text-xs text-[var(--novel-forge-ai-muted)]"
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
                class="ml-2 flex flex-col space-y-1 overflow-hidden border-l border-[var(--novel-forge-ai-border)] pl-2"
            >
                <a
                    v-if="canAccessDocumentStyleComputed"
                    :href="route('back-office.document-styles.index')"
                    class="flex items-center gap-3 rounded-lg px-3 py-2 font-medium text-[var(--novel-forge-ai-ink-soft)] transition-colors duration-150 hover:bg-[var(--novel-forge-ai-primary-soft)] hover:text-[var(--novel-forge-ai-primary-strong)]"
                    :class="isCurrentPage('/back-office/document-styles/*') ? 'bg-[var(--novel-forge-ai-primary-soft)] font-semibold text-[var(--novel-forge-ai-primary-strong)]' : ''"
                >
                    <FontAwesomeIcon icon="file-lines" class="w-4" />
                    Document Style
                </a>
            </div>
        </Transition>
    </div>
</template>

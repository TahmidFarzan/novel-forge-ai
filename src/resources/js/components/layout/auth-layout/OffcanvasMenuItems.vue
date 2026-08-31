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
    faRobot,
    faBookOpen,
    faGears,
} from "@fortawesome/free-solid-svg-icons";

library.add(
    faUser,
    faUsers,
    faChevronDown,
    faChevronUp,
    faGauge,
    faPhotoFilm,
    faBrain,
    faRobot,
    faBookOpen,
    faGears,
);

import {
    canAccessUser,
    canAccessGenre,
    canAccessAiBrain,
    canAccessAiBrainRunner,
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
});

const routeMap = {
    UserManagement: ["/users/*"],
    AiAttributes: ["/ai-brains/*", "/ai-brain-runners/*"],
    NovelAttributes: ["/genres/*"],
    Reports: ["/reports/*"],
};

const canAccessUserComputed = computed(() => {
    return canAccessUser(authUser);
});

const canAccessAiBrainComputed = computed(() => {
    return canAccessAiBrain(authUser);
});

const canAccessAiBrainRunnerComputed = computed(() => {
    return canAccessAiBrainRunner(authUser);
});

const canAccessGenreComputed = computed(() => {
    return canAccessGenre(authUser);
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
            enter-to-class="opacity-100 max-h-40"
            leave-active-class="transition-all duration-200 ease-in"
            leave-from-class="opacity-100 max-h-40"
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
                    v-if="canAccessAiBrainRunnerComputed"
                    :href="route('ai-brain-runners.index')"
                    class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100"
                    :class="
                        isCurrentPage('/ai-brain-runners/*')
                            ? 'bg-gray-200 font-medium'
                            : ''
                    "
                >
                    <FontAwesomeIcon icon="robot" />
                    Ai Brain Runners
                </a>

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

        <a
            :href="route('settings.index')"
            class="flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100"
            :class="
                isCurrentPage('/settings/*') ? 'bg-gray-200 font-medium' : ''
            "
        >
            <FontAwesomeIcon icon="gears" />
            Settings
        </a>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import OffCanvasMenuItems from '@/components/common/layout/auth-layout/OffCanvasMenuItems.vue'
import { useOffcanvasMenu } from '@/composables/useOffcanvasMenu'

import { library } from '@fortawesome/fontawesome-svg-core'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import {
    faBars,
    faXmark
} from '@fortawesome/free-solid-svg-icons'

library.add(
    faBars,
    faXmark
)

const {
    authUser,
    mode = 'trigger'
} = defineProps({
    authUser: {
        type: Object,
        default: null
    },
    mode: {
        type: String,
        default: 'trigger',
        validator: (value) => ['trigger', 'sidebar'].includes(value)
    }
})

const { isMobile, isMobileOpen, isDesktopCollapsed } = useOffcanvasMenu()

const isTriggerMode = computed(() => mode === 'trigger')
const isSidebarMode = computed(() => mode === 'sidebar')

const isOpen = computed(() => (isMobile.value ? isMobileOpen.value : !isDesktopCollapsed.value))

const toggleMenu = () => {
    if (isMobile.value) {
        isMobileOpen.value = !isMobileOpen.value
    } else {
        isDesktopCollapsed.value = !isDesktopCollapsed.value
    }
}

const closeMobileMenu = () => {
    isMobileOpen.value = false
}
</script>

<template>
    <template v-if="isTriggerMode">
        <button type="button" @click="toggleMenu"
            class="inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-xl border border-[var(--novel-forge-ai-border)] bg-[var(--novel-forge-ai-surface)] text-[var(--novel-forge-ai-ink-soft)] shadow-[var(--novel-forge-ai-shadow-sm)] transition-colors duration-150 hover:border-[var(--novel-forge-ai-primary)] hover:text-[var(--novel-forge-ai-primary-strong)]"
            :aria-expanded="isOpen ? 'true' : 'false'"
            :aria-label="(isMobile && isMobileOpen) ? 'Close sidebar menu' : 'Toggle sidebar menu'">
            <FontAwesomeIcon :icon="isMobile && isMobileOpen ? 'xmark' : 'bars'" />
        </button>

        <Teleport to="body">
            <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0"
                enter-to-class="opacity-100" leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="isMobileOpen" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 md:hidden"
                    @click="closeMobileMenu" />
            </Transition>

            <Transition enter-active-class="transition transform duration-300 ease-out"
                enter-from-class="-translate-x-full" enter-to-class="translate-x-0"
                leave-active-class="transition transform duration-200 ease-in" leave-from-class="translate-x-0"
                leave-to-class="-translate-x-full">
                <aside v-if="isMobileOpen"
                    class="fixed top-0 left-0 z-50 flex h-full w-72 max-w-[85vw] flex-col overflow-hidden bg-[var(--novel-forge-ai-surface)] p-3 shadow-[var(--novel-forge-ai-shadow-lg)] md:hidden">
                    <div class="mb-3 flex items-center justify-end">
                        <button type="button" @click="closeMobileMenu"
                            class="flex h-8 w-8 items-center justify-center rounded-lg text-[var(--novel-forge-ai-muted)] transition-colors duration-150 hover:bg-[var(--novel-forge-ai-primary-soft)] hover:text-[var(--novel-forge-ai-primary-strong)]"
                            aria-label="Close sidebar menu">
                            <FontAwesomeIcon icon="xmark" />
                        </button>
                    </div>

                    <div class="min-h-0 flex-1 overflow-y-auto">
                        <OffCanvasMenuItems :auth-user="authUser" @navigate="closeMobileMenu" />
                    </div>
                </aside>
            </Transition>
        </Teleport>
    </template>

    <aside v-if="isSidebarMode"
        class="hidden md:block flex-shrink-0 overflow-hidden border-r border-[var(--novel-forge-ai-border)] bg-[var(--novel-forge-ai-surface)] transition-all duration-300 ease-in-out"
        :class="isDesktopCollapsed ? 'w-0' : 'w-64'"
        :aria-expanded="isDesktopCollapsed ? 'false' : 'true'"
        aria-label="Sidebar menu">
        <div class="w-64 sticky top-14 max-h-[calc(100vh-3.5rem)] overflow-y-auto p-3">
            <OffCanvasMenuItems :auth-user="authUser" />
        </div>
    </aside>
</template>
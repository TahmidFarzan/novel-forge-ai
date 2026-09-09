import { ref, onMounted, onBeforeUnmount } from 'vue';

const isMobile = ref(false);
const isMobileOpen = ref(false);
const isDesktopCollapsed = ref(false);

let listenersBound = false;

const syncBreakpoint = () => {
    if (typeof window === 'undefined') {
        return;
    }

    const mobile = window.innerWidth < 768;

    isMobile.value = mobile;

    if (mobile) {
        isDesktopCollapsed.value = false;
    } else {
        isMobileOpen.value = false;
    }
};

export function useOffcanvasMenu() {
    if (!listenersBound) {
        listenersBound = true;

        onMounted(() => {
            syncBreakpoint();
            window.addEventListener('resize', syncBreakpoint);
        });

        onBeforeUnmount(() => {
            window.removeEventListener('resize', syncBreakpoint);
        });
    }

    return {
        isMobile,
        isMobileOpen,
        isDesktopCollapsed,
    };
}
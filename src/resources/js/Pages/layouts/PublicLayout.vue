<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { router as inertia, usePage } from '@inertiajs/vue3'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import {
    faChevronDown,
    faGaugeHigh,
    faRightFromBracket,
    faUser,
    faUserGear,
} from '@fortawesome/free-solid-svg-icons'
import FlashMessageToaster from '@/components/common/layout/FlashMessageToaster.vue'

library.add(
    faChevronDown,
    faGaugeHigh,
    faRightFromBracket,
    faUser,
    faUserGear,
)

const page = usePage()
const appName = import.meta.env.VITE_APP_NAME || 'Novel Forge AI'
const appLogo = import.meta.env.VITE_APP_LOGO || '/uploads/icons/app/logo.png'
const authUser = computed(() => page.props.auth?.user ?? null)
const flashMessage = computed(() => page.props.flashMessage ?? null)
const currentYear = new Date().getFullYear()
const menuOpen = ref(false)
const menuRef = ref(null)

const initials = computed(() => {
    const name = authUser.value?.name || authUser.value?.email || 'AI'

    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((part) => part.charAt(0).toUpperCase())
        .join('')
})

function closeMenu() {
    menuOpen.value = false
}

function handleClickOutside(event) {
    if (menuRef.value && !menuRef.value.contains(event.target)) {
        closeMenu()
    }
}

function logout() {
    closeMenu()
    inertia.post(route('logout'))
}

onMounted(() => {
    document.addEventListener('click', handleClickOutside)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside)
})
</script>

<template>
    <div class="flex min-h-screen flex-col bg-[var(--ai-canvas)] text-[var(--ai-ink)]">
        <header class="sticky top-0 z-50 border-b border-[var(--ai-border)] bg-white/90 backdrop-blur-xl">
            <div class="ai-container flex h-[4.5rem] items-center justify-between gap-4">
                <a :href="route('home')" class="flex min-w-0 items-center gap-3" aria-label="Go to homepage">
                    <img :src="appLogo" :alt="appName" class="h-10 w-10 shrink-0 rounded-xl object-cover shadow-[0_8px_18px_rgb(46_196_230_/_16%)]" />
                    <span class="truncate text-base font-bold tracking-[-0.02em] text-[var(--ai-ink)] sm:text-lg">
                        {{ appName }}
                    </span>
                </a>

                <div class="flex shrink-0 items-center">
                    <a
                        v-if="!authUser"
                        :href="route('login')"
                        class="inline-flex min-h-10 items-center justify-center rounded-xl border border-[var(--ai-border)] bg-white px-4 py-2 text-sm font-bold text-[var(--ai-ink)] shadow-[var(--ai-shadow-sm)] hover:-translate-y-px hover:border-[var(--ai-primary)] hover:text-[var(--ai-primary-strong)]"
                    >
                        Login
                    </a>

                    <div v-else ref="menuRef" class="relative">
                        <button
                            type="button"
                            class="flex min-h-10 items-center gap-2 rounded-xl border border-[var(--ai-border)] bg-white px-2.5 py-1.5 text-left shadow-[var(--ai-shadow-sm)] hover:border-[var(--ai-primary)]"
                            aria-label="Open account menu"
                            :aria-expanded="menuOpen"
                            @click.stop="menuOpen = !menuOpen"
                        >
                            <span class="public-profile-avatar flex h-8 w-8 items-center justify-center rounded-lg text-xs font-bold text-white">
                                {{ initials }}
                            </span>
                            <span class="hidden max-w-28 truncate text-sm font-semibold text-[var(--ai-ink)] sm:block">
                                {{ authUser.name || 'Account' }}
                            </span>
                            <FontAwesomeIcon icon="chevron-down" class="text-xs text-[var(--ai-muted)]" />
                        </button>

                        <Transition
                            enter-active-class="transition duration-150 ease-out"
                            enter-from-class="translate-y-1 scale-95 opacity-0"
                            enter-to-class="translate-y-0 scale-100 opacity-100"
                            leave-active-class="transition duration-100 ease-in"
                            leave-from-class="translate-y-0 scale-100 opacity-100"
                            leave-to-class="translate-y-1 scale-95 opacity-0"
                        >
                            <div v-if="menuOpen" class="absolute right-0 top-[calc(100%+0.6rem)] w-56 origin-top-right overflow-hidden rounded-2xl border border-[var(--ai-border)] bg-white p-1.5 text-sm shadow-[var(--ai-shadow-lg)]">
                                <div class="border-b border-[var(--ai-border)] px-3 py-2.5">
                                    <p class="truncate font-bold text-[var(--ai-ink)]">{{ authUser.name || 'Your account' }}</p>
                                    <p class="truncate text-xs text-[var(--ai-muted)]">{{ authUser.email }}</p>
                                </div>
                                <a :href="route('auth-user.dashboard.index')" class="mt-1 flex items-center gap-2 rounded-xl px-3 py-2.5 font-semibold text-[var(--ai-ink-soft)] hover:bg-[var(--ai-primary-soft)] hover:text-[var(--ai-primary-strong)]" @click="closeMenu">
                                    <FontAwesomeIcon icon="gauge-high" class="w-4 text-[var(--ai-primary)]" />
                                    Dashboard
                                </a>
                                <a :href="route('auth-user.profile.index')" class="flex items-center gap-2 rounded-xl px-3 py-2.5 font-semibold text-[var(--ai-ink-soft)] hover:bg-[var(--ai-primary-soft)] hover:text-[var(--ai-primary-strong)]" @click="closeMenu">
                                    <FontAwesomeIcon icon="user" class="w-4 text-[var(--ai-primary)]" />
                                    Profile
                                </a>
                                <a :href="route('auth-user.account.index')" class="flex items-center gap-2 rounded-xl px-3 py-2.5 font-semibold text-[var(--ai-ink-soft)] hover:bg-[var(--ai-primary-soft)] hover:text-[var(--ai-primary-strong)]" @click="closeMenu">
                                    <FontAwesomeIcon icon="user-gear" class="w-4 text-[var(--ai-primary)]" />
                                    Account settings
                                </a>
                                <button type="button" class="flex w-full items-center gap-2 rounded-xl px-3 py-2.5 text-left font-semibold text-[var(--ai-danger)] hover:bg-red-50" @click="logout">
                                    <FontAwesomeIcon icon="right-from-bracket" class="w-4" />
                                    Log out
                                </button>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>
        </header>

        <main class="flex-1">
            <slot />
        </main>

        <footer class="border-t border-[var(--ai-border)] bg-white">
            <div class="ai-container flex flex-col items-center justify-between gap-3 py-6 text-center text-sm text-[var(--ai-muted)] sm:flex-row sm:text-left">
                <div class="flex items-center gap-2.5">
                    <img :src="appLogo" :alt="appName" class="h-7 w-7 rounded-lg object-cover" />
                    <span>© {{ currentYear }} {{ appName }}</span>
                </div>
                <span>Built for the next generation of ideas.</span>
            </div>
        </footer>

        <FlashMessageToaster :flash-message="flashMessage" />
    </div>
</template>

<style>
* {
    box-sizing: border-box;
}

html {
    scroll-behavior: smooth;
}

body {
    min-width: 320px;
    margin: 0;
    background: var(--ai-canvas);
    color: var(--ai-ink);
    font-family: var(--ai-font-sans);
    text-rendering: optimizeLegibility;
    -webkit-font-smoothing: antialiased;
}

button,
input,
textarea,
select {
    font: inherit;
}

a,
button {
    transition: color var(--ai-transition), background-color var(--ai-transition), border-color var(--ai-transition), box-shadow var(--ai-transition), transform var(--ai-transition), opacity var(--ai-transition);
}

a {
    color: inherit;
    text-decoration: none;
}

::selection {
    background: rgb(91 92 240 / 18%);
    color: var(--ai-primary-strong);
}

:focus-visible {
    outline: 0;
    box-shadow: var(--ai-focus-ring);
}

.ai-container {
    width: min(100% - 2rem, 1160px);
    margin-inline: auto;
}

.public-profile-avatar {
    background: var(--ai-gradient-brand);
}

@media (min-width: 768px) {
    .ai-container {
        width: min(100% - 3rem, 1160px);
    }
}

@media (max-width: 640px) {
    .ai-container {
        width: min(100% - 1.5rem, 1160px);
    }
}
</style>

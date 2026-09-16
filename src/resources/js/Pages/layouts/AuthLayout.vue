<script setup>
import OffCanvasMenu from '@/components/common/layout/auth-layout/OffCanvasMenu.vue'
import Breadcrumbs from '@/components/common/layout/auth-layout/Breadcrumbs.vue'
import AuthTopbarDropdownMenu from '@/components/common/layout/auth-layout/AuthTopbarDropdownMenu.vue'
import FlashMessageToaster from '@/components/common/layout/FlashMessageToaster.vue'

import { usePage } from '@inertiajs/vue3'
import { computed, provide } from 'vue'

const appName = import.meta.env.VITE_APP_NAME
const appLogo = import.meta.env.VITE_APP_LOGO

const page = usePage()

const authUser = computed(() => page.props.auth?.user ?? null)
const flashMessage = computed(() => page.props.flashMessage ?? null)

provide('authUser', authUser)
</script>


<template>
    <div class="auth-layout flex min-h-screen flex-col bg-[var(--canvas)] text-[var(--ink)] [font-family:var(--font-sans)]">

        <header class="fixed top-0 left-0 z-50 w-full border-b border-[var(--border)] bg-[var(--surface)]/90 shadow-[var(--shadow-sm)] backdrop-blur-xl">
            <div class="flex h-14 items-center justify-between gap-3 px-4">

                <a :href="route('home')" class="group flex min-w-0 items-center gap-2.5">
                    <span v-if="appLogo"
                        class="flex h-9 w-9 flex-shrink-0 items-center justify-center overflow-hidden rounded-xl bg-[var(--primary-soft)] shadow-[var(--shadow-sm)] transition-shadow duration-150 group-hover:shadow-[var(--shadow-primary)]">
                        <img v-if="appLogo" :src="appLogo" :alt="appName" class="h-full w-full object-contain">
                    </span>

                    <span
                        class="truncate text-base font-bold tracking-[-0.02em] text-[var(--ink)]">
                        {{ appName }}
                    </span>
                </a>

                <div class="flex items-center gap-2.5">

                    <OffCanvasMenu mode="trigger" :auth-user="authUser" />

                    <AuthTopbarDropdownMenu :auth-user="authUser" />

                </div>
            </div>
        </header>

        <main class="main flex flex-1 pt-14">

            <OffCanvasMenu mode="sidebar" :auth-user="authUser" />

            <div class="min-w-0 flex-1 bg-[var(--canvas)] p-4">

                <Breadcrumbs />

                <div v-if="authUser && !authUser.email_verified_at"
                    class="mb-4 p-3 bg-yellow-100 border border-yellow-300 text-yellow-800 rounded">
                    Please verify your email address
                </div>

                <slot />

            </div>
        </main>

        <footer class="border-t border-[var(--border)] bg-[var(--surface)]/90 py-3 text-sm text-[var(--muted)]">

            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-2 px-4 md:flex-row">

                <span class="w-full text-center md:w-auto md:text-left">
                    &copy; {{ new Date().getFullYear() }} {{ appName }}
                </span>

                <span class="w-full text-center md:w-auto md:text-right">
                    Developed by
                    <a href="https://www.linkedin.com/in/sk-md-tahmid-farzan/" target="_blank" rel="noopener noreferrer"
                        class="font-medium text-[var(--primary-strong)] transition-colors duration-150 hover:underline">
                        Sk Md Tahmid Farzan
                    </a>
                </span>

            </div>
        </footer>

        <FlashMessageToaster :flash-message="flashMessage" />

    </div>
</template>

<style scoped>
.auth-layout :deep(a:focus-visible),
.auth-layout :deep(button:focus-visible),
.auth-layout :deep(input:focus-visible),
.auth-layout :deep(select:focus-visible),
.auth-layout :deep(textarea:focus-visible) {
    outline: 0;
    box-shadow: var(--focus-ring);
}
</style>
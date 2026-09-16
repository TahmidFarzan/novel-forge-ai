<script setup>
import layout from '@/pages/layouts/PublicLayout.vue'
import AuthCard from '@/components/common/layout/public-layout/AuthCard.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faArrowRight, faEye, faEyeSlash, faSpinner, faWandMagicSparkles } from '@fortawesome/free-solid-svg-icons'
import { showPassword, togglePasswordVisibility } from '@/composables/usePassword'

library.add(faArrowRight, faEye, faEyeSlash, faSpinner, faWandMagicSparkles)

defineOptions({ layout })

const appName = import.meta.env.VITE_APP_NAME || 'Novel Forge AI'
const appFavicon = import.meta.env.VITE_APP_FAVICON || '/uploads/icons/app/favicon.png'

const loginForm = useForm({
    email: '',
    password: '',
    remember: false,
})

function validateForm() {
    loginForm.clearErrors()

    let valid = true

    if (!loginForm.email || loginForm.email.trim() === '') {
        loginForm.setError('email', 'Email is required')
        valid = false
    } else if (loginForm.email.length > 200) {
        loginForm.setError('email', 'Email must not exceed 200 characters')
        valid = false
    }

    if (!loginForm.password || loginForm.password.trim() === '') {
        loginForm.setError('password', 'Password is required')
        valid = false
    }

    return valid
}

function handleLogin() {
    if (loginForm.processing) return
    if (!validateForm()) return

    loginForm.post(route('login.submit'), {
        preserveScroll: true,

        onSuccess: () => {
            loginForm.reset()
            loginForm.clearErrors()
        },

        onError: (errors) => {
            loginForm.clearErrors()
            loginForm.setError(errors)
        },
    })
}
</script>

<template>
    <Head title="Log in" />

    <AuthCard
        :logo="appFavicon"
        :alt="appName"
        eyebrow="Welcome back"
        :subtitle="`Continue with ${appName}`"
        title="Log in to your workspace"
        description="Your next great idea is waiting."
    >
        <template #story>
            <a :href="route('home')" class="inline-flex items-center gap-3 text-sm font-bold text-[var(--ink)]">
                <img :src="appFavicon" :alt="appName" class="h-10 w-10 rounded-xl object-cover shadow-[0_8px_18px_rgb(46_196_230_/_16%)]" />
                {{ appName }}
            </a>
            <div class="mt-12 flex h-48 w-48 items-center justify-center rounded-[2.5rem] border border-white/70 bg-white/60 p-5 shadow-[var(--shadow-md)]">
                <img :src="appFavicon" :alt="`${appName} mark`" class="h-full w-full rounded-[1.7rem] object-cover" />
            </div>
            <p class="mt-9 text-xs font-bold uppercase tracking-[0.18em] text-[var(--primary)]">Your ideas, amplified</p>
            <h1 class="mt-4 max-w-md text-4xl font-bold leading-[1.05] tracking-[-0.05em] text-[var(--ink)] sm:text-5xl">Pick up where your best thinking left off.</h1>
            <p class="mt-5 max-w-md text-base leading-7 text-[var(--muted)]">Sign in to your intelligent workspace and keep turning momentum into meaningful output.</p>
        </template>

        <form class="mt-8 space-y-5" @submit.prevent="handleLogin">
            <div>
                <label for="email" class="auth-label">Email address</label>
                <input
                    id="email"
                    v-model="loginForm.email"
                    type="email"
                    autocomplete="email"
                    placeholder="you@example.com"
                    autofocus
                    class="auth-input"
                    :class="{ 'is-invalid': loginForm.errors.email }"
                />
                <p v-if="loginForm.errors.email" class="auth-error">{{ loginForm.errors.email }}</p>
            </div>

            <div>
                <div class="mb-2 flex items-center justify-between gap-3">
                    <label for="password" class="auth-label mb-0">Password</label>
                    <a :href="route('forgot-password')" class="text-xs font-semibold text-[var(--primary-strong)] hover:text-[var(--primary)]">Forgot password?</a>
                </div>
                <div class="relative">
                    <input
                        id="password"
                        v-model="loginForm.password"
                        :type="showPassword ? 'text' : 'password'"
                        autocomplete="current-password"
                        placeholder="Enter your password"
                        class="auth-input pr-12"
                        :class="{ 'is-invalid': loginForm.errors.password }"
                    />
                    <button type="button" class="absolute right-0 top-0 flex h-[3.1rem] w-12 items-center justify-center text-[var(--muted)] hover:text-[var(--primary)]" @click="togglePasswordVisibility" :aria-label="showPassword ? 'Hide password' : 'Show password'">
                        <FontAwesomeIcon :icon="showPassword ? 'eye-slash' : 'eye'" />
                    </button>
                </div>
                <p v-if="loginForm.errors.password" class="auth-error">{{ loginForm.errors.password }}</p>
            </div>

            <label class="flex cursor-pointer items-center gap-2 text-sm text-[var(--muted)]">
                <input id="remember" v-model="loginForm.remember" type="checkbox" class="h-4 w-4 rounded border-[var(--border-strong)] accent-[var(--primary)]" />
                Keep me signed in
            </label>

            <button type="submit" :disabled="loginForm.processing" class="auth-button w-full">
                <FontAwesomeIcon v-if="loginForm.processing" icon="spinner" spin />
                <FontAwesomeIcon v-else icon="wand-magic-sparkles" />
                {{ loginForm.processing ? 'Signing in...' : 'Enter workspace' }}
            </button>
        </form>

        <!-- <p class="mt-7 text-center text-sm text-[var(--muted)]">
            New to {{ appName }}?
            <a :href="route('register')" class="auth-link ml-1">Create an account</a>
        </p> -->
    </AuthCard>
</template>

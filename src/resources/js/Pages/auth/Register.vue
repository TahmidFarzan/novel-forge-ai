<script setup>
import layout from '@/pages/layouts/PublicLayout.vue'
import AuthCard from '@/components/common/layout/public-layout/AuthCard.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faArrowRight, faEye, faEyeSlash, faSpinner, faWandMagicSparkles } from '@fortawesome/free-solid-svg-icons'
import {
    showPassword,
    showConfirmPassword,
    togglePasswordVisibility,
    toggleConfirmPasswordVisibility,
} from '@/composables/usePassword'

library.add(faArrowRight, faEye, faEyeSlash, faSpinner, faWandMagicSparkles)

defineOptions({ layout })

const appName = import.meta.env.VITE_APP_NAME || 'Novel Forge AI'
const appFavicon = import.meta.env.VITE_APP_FAVICON || '/uploads/icons/app/favicon.png'

const registerForm = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
})

function validateForm() {
    registerForm.clearErrors()

    let valid = true

    if (!registerForm.name || registerForm.name.trim() === '') {
        registerForm.setError('name', 'Name is required')
        valid = false
    } else if (registerForm.name.length > 200) {
        registerForm.setError('name', 'Name must not exceed 200 characters')
        valid = false
    }

    if (!registerForm.email || registerForm.email.trim() === '') {
        registerForm.setError('email', 'Email is required')
        valid = false
    } else if (registerForm.email.length > 200) {
        registerForm.setError('email', 'Email must not exceed 200 characters')
        valid = false
    }

    if (!registerForm.password || registerForm.password.trim() === '') {
        registerForm.setError('password', 'Password is required')
        valid = false
    }

    if (!registerForm.password_confirmation || registerForm.password_confirmation.trim() === '') {
        registerForm.setError('password_confirmation', 'Password confirmation is required')
        valid = false
    } else if (registerForm.password !== registerForm.password_confirmation) {
        registerForm.setError('password_confirmation', 'Password confirmation does not match')
        valid = false
    }

    return valid
}

function handleRegister() {
    if (registerForm.processing) return
    if (!validateForm()) return

    registerForm.post(route('register.submit'), {
        preserveScroll: true,

        onSuccess: () => {
            registerForm.reset()
            registerForm.clearErrors()
        },

        onError: (errors) => {
            registerForm.clearErrors()
            registerForm.setError(errors)
        },
    })
}
</script>

<template>
    <Head title="Create account" />

    <AuthCard
        :logo="appFavicon"
        :alt="appName"
        eyebrow="Start creating"
        :subtitle="`Join ${appName} today`"
        title="Create your workspace"
        description="Set up your account and start building with AI."
        wide
    >
        <template #story>
            <a :href="route('home')" class="inline-flex items-center gap-3 text-sm font-bold text-[var(--ink)]">
                <img :src="appFavicon" :alt="appName" class="h-10 w-10 rounded-xl object-cover shadow-[0_8px_18px_rgb(46_196_230_/_16%)]" />
                {{ appName }}
            </a>
            <div class="mt-12 flex h-48 w-48 items-center justify-center rounded-[2.5rem] border border-white/70 bg-white/60 p-5 shadow-[var(--shadow-md)]">
                <img :src="appFavicon" :alt="`${appName} mark`" class="h-full w-full rounded-[1.7rem] object-cover" />
            </div>
            <p class="mt-9 text-xs font-bold uppercase tracking-[0.18em] text-[var(--primary)]">A smarter starting point</p>
            <h1 class="mt-4 max-w-md text-4xl font-bold leading-[1.05] tracking-[-0.05em] text-[var(--ink)] sm:text-5xl">Bring your ideas into focus.</h1>
            <p class="mt-5 max-w-md text-base leading-7 text-[var(--muted)]">Create a workspace where your thinking gets a little clearer and your best work happens a lot sooner.</p>
        </template>

        <form class="mt-8 space-y-5" @submit.prevent="handleRegister">
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="name" class="auth-label">Full name</label>
                    <input id="name" v-model="registerForm.name" type="text" autocomplete="name" placeholder="Your name" autofocus class="auth-input" :class="{ 'is-invalid': registerForm.errors.name }" />
                    <p v-if="registerForm.errors.name" class="auth-error">{{ registerForm.errors.name }}</p>
                </div>
                <div>
                    <label for="email" class="auth-label">Email address</label>
                    <input id="email" v-model="registerForm.email" type="email" autocomplete="email" placeholder="you@example.com" class="auth-input" :class="{ 'is-invalid': registerForm.errors.email }" />
                    <p v-if="registerForm.errors.email" class="auth-error">{{ registerForm.errors.email }}</p>
                </div>
            </div>

            <div>
                <label for="password" class="auth-label">Password</label>
                <div class="relative">
                    <input id="password" v-model="registerForm.password" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" placeholder="Create a password" class="auth-input pr-12" :class="{ 'is-invalid': registerForm.errors.password }" />
                    <button type="button" class="absolute right-0 top-0 flex h-[3.1rem] w-12 items-center justify-center text-[var(--muted)] hover:text-[var(--primary)]" @click="togglePasswordVisibility" :aria-label="showPassword ? 'Hide password' : 'Show password'"><FontAwesomeIcon :icon="showPassword ? 'eye-slash' : 'eye'" /></button>
                </div>
                <p v-if="registerForm.errors.password" class="auth-error">{{ registerForm.errors.password }}</p>
            </div>

            <div>
                <label for="passwordConfirmation" class="auth-label">Confirm password</label>
                <div class="relative">
                    <input id="passwordConfirmation" v-model="registerForm.password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" autocomplete="new-password" placeholder="Repeat your password" class="auth-input pr-12" :class="{ 'is-invalid': registerForm.errors.password_confirmation }" />
                    <button type="button" class="absolute right-0 top-0 flex h-[3.1rem] w-12 items-center justify-center text-[var(--muted)] hover:text-[var(--primary)]" @click="toggleConfirmPasswordVisibility" :aria-label="showConfirmPassword ? 'Hide confirm password' : 'Show confirm password'"><FontAwesomeIcon :icon="showConfirmPassword ? 'eye-slash' : 'eye'" /></button>
                </div>
                <p v-if="registerForm.errors.password_confirmation" class="auth-error">{{ registerForm.errors.password_confirmation }}</p>
            </div>

            <button type="submit" :disabled="registerForm.processing" class="auth-button w-full">
                <FontAwesomeIcon v-if="registerForm.processing" icon="spinner" spin />
                <FontAwesomeIcon v-else icon="wand-magic-sparkles" />
                {{ registerForm.processing ? 'Creating workspace...' : 'Create account' }}
            </button>
        </form>

        <p class="mt-7 text-center text-sm text-[var(--muted)]">
            Already have an account?
            <a :href="route('login')" class="auth-link ml-1">Log in</a>
        </p>
    </AuthCard>
</template>

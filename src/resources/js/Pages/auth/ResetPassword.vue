<script setup>
import layout from '@/pages/layouts/PublicLayout.vue'
import AuthCard from '@/components/common/layout/public-layout/AuthCard.vue'
import { Head, useForm, usePage } from '@inertiajs/vue3'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faEye, faEyeSlash, faKey, faSpinner } from '@fortawesome/free-solid-svg-icons'
import {
    showPassword,
    showConfirmPassword,
    togglePasswordVisibility,
    toggleConfirmPasswordVisibility,
} from '@/composables/usePassword'

library.add(faEye, faEyeSlash, faKey, faSpinner)

defineOptions({ layout })

const appName = import.meta.env.VITE_APP_NAME || 'Novel Forge AI'
const appFavicon = import.meta.env.VITE_APP_FAVICON || '/uploads/icons/app/favicon.png'
const page = usePage()
const token = page.props.token
const email = page.props.email

const resetPasswordForm = useForm({
    email: email || '',
    password: '',
    password_confirmation: '',
    token: token || '',
})

function validateForm() {
    resetPasswordForm.clearErrors()

    let valid = true

    if (!resetPasswordForm.password || resetPasswordForm.password.trim() === '') {
        resetPasswordForm.setError('password', 'Password is required')
        valid = false
    }

    if (!resetPasswordForm.password_confirmation || resetPasswordForm.password_confirmation.trim() === '') {
        resetPasswordForm.setError('password_confirmation', 'Password confirmation is required')
        valid = false
    } else if (resetPasswordForm.password !== resetPasswordForm.password_confirmation) {
        resetPasswordForm.setError('password_confirmation', 'Password confirmation does not match')
        valid = false
    }

    return valid
}

function handleResetPassword() {
    if (resetPasswordForm.processing) return
    if (!validateForm()) return

    resetPasswordForm.post(route('password.reset.submit', { email, token }), {
        preserveScroll: true,

        onSuccess: () => {
            resetPasswordForm.reset()
            resetPasswordForm.clearErrors()
        },

        onError: (errors) => {
            resetPasswordForm.clearErrors()
            resetPasswordForm.setError(errors)
        },
    })
}
</script>

<template>
    <Head title="Set a new password" />

<AuthCard
        :logo="appFavicon"
        :alt="appName"
        eyebrow="New credentials"
        :subtitle="`Finish securing ${appName}`"
        title="Create a new password"
        description="Use a password you'll remember and only you can access."
    >
        <template #story>
            <a :href="route('home')" class="inline-flex items-center gap-3 text-sm font-bold text-[var(--ink)]">
                <img :src="appFavicon" :alt="appName" class="h-10 w-10 rounded-xl object-cover shadow-[0_8px_18px_rgb(46_196_230_/_16%)]" />
                {{ appName }}
            </a>
            <div class="mt-12 flex h-48 w-48 items-center justify-center rounded-[2.5rem] border border-white/70 bg-white/60 p-5 shadow-[var(--shadow-md)]">
                <img :src="appFavicon" :alt="`${appName} mark`" class="h-full w-full rounded-[1.7rem] object-cover" />
            </div>
            <p class="mt-9 text-xs font-bold uppercase tracking-[0.18em] text-[var(--primary)]">One secure step</p>
            <h1 class="mt-4 max-w-md text-4xl font-bold leading-[1.05] tracking-[-0.05em] text-[var(--ink)] sm:text-5xl">Set a password that keeps your thinking yours.</h1>
            <p class="mt-5 max-w-md text-base leading-7 text-[var(--muted)]">Choose a strong new password and your intelligent workspace will be ready when you are.</p>
        </template>

        <form class="mt-8 space-y-5" @submit.prevent="handleResetPassword">
            <input v-model="resetPasswordForm.email" type="hidden" />
            <input v-model="resetPasswordForm.token" type="hidden" />

            <div>
                <label for="password" class="auth-label">New password</label>
                <div class="relative">
                    <input id="password" v-model="resetPasswordForm.password" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" placeholder="Enter a new password" autofocus class="auth-input pr-12" :class="{ 'is-invalid': resetPasswordForm.errors.password }" />
                    <button type="button" class="absolute right-0 top-0 flex h-[3.1rem] w-12 items-center justify-center text-[var(--muted)] hover:text-[var(--primary)]" @click="togglePasswordVisibility" :aria-label="showPassword ? 'Hide password' : 'Show password'"><FontAwesomeIcon :icon="showPassword ? 'eye-slash' : 'eye'" /></button>
                </div>
                <p v-if="resetPasswordForm.errors.password" class="auth-error">{{ resetPasswordForm.errors.password }}</p>
            </div>

            <div>
                <label for="passwordConfirmation" class="auth-label">Confirm new password</label>
                <div class="relative">
                    <input id="passwordConfirmation" v-model="resetPasswordForm.password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" autocomplete="new-password" placeholder="Repeat the new password" class="auth-input pr-12" :class="{ 'is-invalid': resetPasswordForm.errors.password_confirmation }" />
                    <button type="button" class="absolute right-0 top-0 flex h-[3.1rem] w-12 items-center justify-center text-[var(--muted)] hover:text-[var(--primary)]" @click="toggleConfirmPasswordVisibility" :aria-label="showConfirmPassword ? 'Hide confirm password' : 'Show confirm password'"><FontAwesomeIcon :icon="showConfirmPassword ? 'eye-slash' : 'eye'" /></button>
                </div>
                <p v-if="resetPasswordForm.errors.password_confirmation" class="auth-error">{{ resetPasswordForm.errors.password_confirmation }}</p>
            </div>

            <button type="submit" :disabled="resetPasswordForm.processing" class="auth-button w-full">
                <FontAwesomeIcon v-if="resetPasswordForm.processing" icon="spinner" spin />
                <FontAwesomeIcon v-else icon="key" />
                {{ resetPasswordForm.processing ? 'Updating password...' : 'Set new password' }}
            </button>
        </form>

        <p class="mt-7 text-center text-sm text-[var(--muted)]">
            Need to start over?
            <a :href="route('login')" class="auth-link ml-1">Back to log in</a>
        </p>
    </AuthCard>
</template>

<script setup>
import layout from '@/pages/layouts/PublicLayout.vue'
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

    <div class="auth-page">
        <div class="ai-container auth-shell">
            <aside class="auth-story">
                <a :href="route('home')" class="inline-flex items-center gap-3 text-sm font-bold text-[var(--ai-ink)]">
                    <img :src="appFavicon" :alt="appName" class="h-10 w-10 rounded-xl object-cover shadow-[0_8px_18px_rgb(46_196_230_/_16%)]" />
                    {{ appName }}
                </a>
                <div class="mt-12 flex h-48 w-48 items-center justify-center rounded-[2.5rem] border border-white/70 bg-white/60 p-5 shadow-[var(--ai-shadow-md)]">
                    <img :src="appFavicon" :alt="`${appName} mark`" class="h-full w-full rounded-[1.7rem] object-cover" />
                </div>
                <p class="mt-9 text-xs font-bold uppercase tracking-[0.18em] text-[var(--ai-primary)]">One secure step</p>
                <h1 class="mt-4 max-w-md text-4xl font-bold leading-[1.05] tracking-[-0.05em] text-[var(--ai-ink)] sm:text-5xl">Set a password that keeps your thinking yours.</h1>
                <p class="mt-5 max-w-md text-base leading-7 text-[var(--ai-muted)]">Choose a strong new password and your intelligent workspace will be ready when you are.</p>
            </aside>

            <section class="auth-card">
                <div class="flex items-center gap-3">
                    <img :src="appFavicon" :alt="appName" class="auth-card__logo" />
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--ai-primary)]">New credentials</p>
                        <p class="mt-0.5 text-xs text-[var(--ai-muted)]">Finish securing {{ appName }}</p>
                    </div>
                </div>

                <div class="mt-8">
                    <h2 class="text-3xl font-bold tracking-[-0.04em] text-[var(--ai-ink)]">Create a new password</h2>
                    <p class="mt-2 text-sm leading-6 text-[var(--ai-muted)]">Use a password you’ll remember and only you can access.</p>
                </div>

                <form class="mt-8 space-y-5" @submit.prevent="handleResetPassword">
                    <input v-model="resetPasswordForm.email" type="hidden" />
                    <input v-model="resetPasswordForm.token" type="hidden" />

                    <div>
                        <label for="password" class="auth-label">New password</label>
                        <div class="relative">
                            <input id="password" v-model="resetPasswordForm.password" :type="showPassword ? 'text' : 'password'" autocomplete="new-password" placeholder="Enter a new password" autofocus class="auth-input pr-12" :class="{ 'is-invalid': resetPasswordForm.errors.password }" />
                            <button type="button" class="absolute right-0 top-0 flex h-[3.1rem] w-12 items-center justify-center text-[var(--ai-muted)] hover:text-[var(--ai-primary)]" @click="togglePasswordVisibility" :aria-label="showPassword ? 'Hide password' : 'Show password'"><FontAwesomeIcon :icon="showPassword ? 'eye-slash' : 'eye'" /></button>
                        </div>
                        <p v-if="resetPasswordForm.errors.password" class="auth-error">{{ resetPasswordForm.errors.password }}</p>
                    </div>

                    <div>
                        <label for="passwordConfirmation" class="auth-label">Confirm new password</label>
                        <div class="relative">
                            <input id="passwordConfirmation" v-model="resetPasswordForm.password_confirmation" :type="showConfirmPassword ? 'text' : 'password'" autocomplete="new-password" placeholder="Repeat the new password" class="auth-input pr-12" :class="{ 'is-invalid': resetPasswordForm.errors.password_confirmation }" />
                            <button type="button" class="absolute right-0 top-0 flex h-[3.1rem] w-12 items-center justify-center text-[var(--ai-muted)] hover:text-[var(--ai-primary)]" @click="toggleConfirmPasswordVisibility" :aria-label="showConfirmPassword ? 'Hide confirm password' : 'Show confirm password'"><FontAwesomeIcon :icon="showConfirmPassword ? 'eye-slash' : 'eye'" /></button>
                        </div>
                        <p v-if="resetPasswordForm.errors.password_confirmation" class="auth-error">{{ resetPasswordForm.errors.password_confirmation }}</p>
                    </div>

                    <button type="submit" :disabled="resetPasswordForm.processing" class="auth-button w-full">
                        <FontAwesomeIcon v-if="resetPasswordForm.processing" icon="spinner" spin />
                        <FontAwesomeIcon v-else icon="key" />
                        {{ resetPasswordForm.processing ? 'Updating password...' : 'Set new password' }}
                    </button>
                </form>

                <p class="mt-7 text-center text-sm text-[var(--ai-muted)]">
                    Need to start over?
                    <a :href="route('login')" class="auth-link ml-1">Back to log in</a>
                </p>
            </section>
        </div>
    </div>
</template>

<style scoped>
.auth-page {
    position: relative;
    isolation: isolate;
    overflow: hidden;
    min-height: calc(100vh - 9.5rem);
    padding: clamp(2.5rem, 6vw, 5.5rem) 0;
    background: var(--ai-gradient-hero);
}

.auth-page::before,
.auth-page::after {
    position: absolute;
    z-index: -1;
    width: 22rem;
    height: 22rem;
    border-radius: 999px;
    content: '';
    filter: blur(2px);
    pointer-events: none;
}

.auth-page::before {
    top: -12rem;
    right: -8rem;
    background: rgb(91 92 240 / 12%);
}

.auth-page::after {
    bottom: -14rem;
    left: -10rem;
    background: rgb(46 196 230 / 12%);
}

.auth-shell {
    display: grid;
    align-items: center;
    gap: clamp(2rem, 6vw, 5rem);
}

.auth-story {
    display: none;
}

.auth-card {
    width: min(100%, 30rem);
    margin-inline: auto;
    border: 1px solid rgb(255 255 255 / 80%);
    border-radius: var(--ai-radius-lg);
    background: rgb(255 255 255 / 92%);
    box-shadow: var(--ai-shadow-lg);
    padding: clamp(1.5rem, 4vw, 2.5rem);
    backdrop-filter: blur(18px);
}

.auth-card__logo {
    width: 3rem;
    height: 3rem;
    border-radius: 13px;
    object-fit: cover;
    box-shadow: 0 8px 18px rgb(46 196 230 / 18%);
}

.auth-label {
    display: block;
    margin-bottom: 0.45rem;
    color: var(--ai-ink-soft);
    font-size: 0.82rem;
    font-weight: 600;
    letter-spacing: 0.01em;
}

.auth-input {
    width: 100%;
    min-height: 3.1rem;
    border: 1px solid var(--ai-border-strong);
    border-radius: var(--ai-radius-sm);
    background: rgb(255 255 255 / 80%);
    color: var(--ai-ink);
    padding: 0.78rem 0.9rem;
    outline: 0;
}

.auth-input::placeholder {
    color: var(--ai-muted-light);
}

.auth-input:focus {
    border-color: var(--ai-primary);
    box-shadow: var(--ai-focus-ring);
}

.auth-input.is-invalid {
    border-color: var(--ai-danger);
}

.auth-input.is-invalid:focus {
    box-shadow: 0 0 0 4px rgb(217 45 32 / 12%);
}

.auth-error {
    margin-top: 0.45rem;
    color: var(--ai-danger);
    font-size: 0.78rem;
    line-height: 1.4;
}

.auth-button {
    display: inline-flex;
    min-height: 3.1rem;
    align-items: center;
    justify-content: center;
    gap: 0.55rem;
    border: 0;
    border-radius: var(--ai-radius-sm);
    background: var(--ai-gradient-brand);
    color: white;
    padding: 0.75rem 1.15rem;
    font-weight: 700;
    box-shadow: var(--ai-shadow-primary);
}

.auth-button:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 18px 32px rgb(91 92 240 / 30%);
}

.auth-button:disabled {
    cursor: not-allowed;
    opacity: 0.65;
}

.auth-link {
    color: var(--ai-primary-strong);
    font-weight: 600;
}

.auth-link:hover {
    color: var(--ai-primary);
}

@media (min-width: 768px) {
    .auth-shell {
        grid-template-columns: minmax(0, 1fr) minmax(25rem, 30rem);
    }

    .auth-story {
        display: block;
        max-width: 32rem;
    }
}

@media (max-width: 640px) {
    .auth-page {
        min-height: calc(100vh - 8rem);
    }
}
</style>

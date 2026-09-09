<script setup>
import layout from '@/pages/layouts/PublicLayout.vue'
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

    <div class="auth-page">
        <div class="ai-container auth-shell">
            <aside class="auth-story">
                <a :href="route('home')" class="inline-flex items-center gap-3 text-sm font-bold text-[var(--novel-forge-ai-ink)]">
                    <img :src="appFavicon" :alt="appName" class="h-10 w-10 rounded-xl object-cover shadow-[0_8px_18px_rgb(46_196_230_/_16%)]" />
                    {{ appName }}
                </a>
                <div class="mt-12 flex h-48 w-48 items-center justify-center rounded-[2.5rem] border border-white/70 bg-white/60 p-5 shadow-[var(--novel-forge-ai-shadow-md)]">
                    <img :src="appFavicon" :alt="`${appName} mark`" class="h-full w-full rounded-[1.7rem] object-cover" />
                </div>
                <p class="mt-9 text-xs font-bold uppercase tracking-[0.18em] text-[var(--novel-forge-ai-primary)]">Your ideas, amplified</p>
                <h1 class="mt-4 max-w-md text-4xl font-bold leading-[1.05] tracking-[-0.05em] text-[var(--novel-forge-ai-ink)] sm:text-5xl">Pick up where your best thinking left off.</h1>
                <p class="mt-5 max-w-md text-base leading-7 text-[var(--novel-forge-ai-muted)]">Sign in to your intelligent workspace and keep turning momentum into meaningful output.</p>
            </aside>

            <section class="auth-card">
                <div class="flex items-center gap-3">
                    <img :src="appFavicon" :alt="appName" class="auth-card__logo" />
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--novel-forge-ai-primary)]">Welcome back</p>
                        <p class="mt-0.5 text-xs text-[var(--novel-forge-ai-muted)]">Continue with {{ appName }}</p>
                    </div>
                </div>

                <div class="mt-8">
                    <h2 class="text-3xl font-bold tracking-[-0.04em] text-[var(--novel-forge-ai-ink)]">Log in to your workspace</h2>
                    <p class="mt-2 text-sm leading-6 text-[var(--novel-forge-ai-muted)]">Your next great idea is waiting.</p>
                </div>

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
                            <a :href="route('forgot-password')" class="text-xs font-semibold text-[var(--novel-forge-ai-primary-strong)] hover:text-[var(--novel-forge-ai-primary)]">Forgot password?</a>
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
                            <button type="button" class="absolute right-0 top-0 flex h-[3.1rem] w-12 items-center justify-center text-[var(--novel-forge-ai-muted)] hover:text-[var(--novel-forge-ai-primary)]" @click="togglePasswordVisibility" :aria-label="showPassword ? 'Hide password' : 'Show password'">
                                <FontAwesomeIcon :icon="showPassword ? 'eye-slash' : 'eye'" />
                            </button>
                        </div>
                        <p v-if="loginForm.errors.password" class="auth-error">{{ loginForm.errors.password }}</p>
                    </div>

                    <label class="flex cursor-pointer items-center gap-2 text-sm text-[var(--novel-forge-ai-muted)]">
                        <input id="remember" v-model="loginForm.remember" type="checkbox" class="h-4 w-4 rounded border-[var(--novel-forge-ai-border-strong)] accent-[var(--novel-forge-ai-primary)]" />
                        Keep me signed in
                    </label>

                    <button type="submit" :disabled="loginForm.processing" class="auth-button w-full">
                        <FontAwesomeIcon v-if="loginForm.processing" icon="spinner" spin />
                        <FontAwesomeIcon v-else icon="wand-magic-sparkles" />
                        {{ loginForm.processing ? 'Signing in...' : 'Enter workspace' }}
                    </button>
                </form>

                <!-- <p class="mt-7 text-center text-sm text-[var(--novel-forge-ai-muted)]">
                    New to {{ appName }}?
                    <a :href="route('register')" class="auth-link ml-1">Create an account</a>
                </p> -->
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
    background: var(--novel-forge-ai-gradient-hero);
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
    border-radius: var(--novel-forge-ai-radius-lg);
    background: rgb(255 255 255 / 92%);
    box-shadow: var(--novel-forge-ai-shadow-lg);
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
    color: var(--novel-forge-ai-ink-soft);
    font-size: 0.82rem;
    font-weight: 600;
    letter-spacing: 0.01em;
}

.auth-input {
    width: 100%;
    min-height: 3.1rem;
    border: 1px solid var(--novel-forge-ai-border-strong);
    border-radius: var(--novel-forge-ai-radius-sm);
    background: rgb(255 255 255 / 80%);
    color: var(--novel-forge-ai-ink);
    padding: 0.78rem 0.9rem;
    outline: 0;
}

.auth-input::placeholder {
    color: var(--novel-forge-ai-muted-light);
}

.auth-input:focus {
    border-color: var(--novel-forge-ai-primary);
    box-shadow: var(--novel-forge-ai-focus-ring);
}

.auth-input.is-invalid {
    border-color: var(--novel-forge-ai-danger);
}

.auth-input.is-invalid:focus {
    box-shadow: 0 0 0 4px rgb(217 45 32 / 12%);
}

.auth-error {
    margin-top: 0.45rem;
    color: var(--novel-forge-ai-danger);
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
    border-radius: var(--novel-forge-ai-radius-sm);
    background: var(--novel-forge-ai-gradient-brand);
    color: white;
    padding: 0.75rem 1.15rem;
    font-weight: 700;
    box-shadow: var(--novel-forge-ai-shadow-primary);
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
    color: var(--novel-forge-ai-primary-strong);
    font-weight: 600;
}

.auth-link:hover {
    color: var(--novel-forge-ai-primary);
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

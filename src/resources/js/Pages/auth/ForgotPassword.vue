<script setup>
import layout from '@/pages/layouts/PublicLayout.vue'
import AuthCard from '@/components/common/layout/public-layout/AuthCard.vue'
import { Head, useForm } from '@inertiajs/vue3'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faArrowRight, faKey, faSpinner } from '@fortawesome/free-solid-svg-icons'

library.add(faArrowRight, faKey, faSpinner)

defineOptions({ layout })

const appName = import.meta.env.VITE_APP_NAME || 'Novel Forge AI'
const appFavicon = import.meta.env.VITE_APP_FAVICON || '/uploads/icons/app/favicon.png'

const resetRequestForm = useForm({
    email: '',
})

function validateForm() {
    resetRequestForm.clearErrors()

    let valid = true

    if (!resetRequestForm.email || resetRequestForm.email.trim() === '') {
        resetRequestForm.setError('email', 'Email is required')
        valid = false
    } else if (resetRequestForm.email.length > 200) {
        resetRequestForm.setError('email', 'Email must not exceed 200 characters')
        valid = false
    }

    return valid
}

function handleForgotPassword() {
    if (resetRequestForm.processing) return
    if (!validateForm()) return

    resetRequestForm.post(route('forgot-password.submit'), {
        preserveScroll: true,

        onSuccess: () => {
            resetRequestForm.reset()
            resetRequestForm.clearErrors()
        },

        onError: (errors) => {
            resetRequestForm.clearErrors()
            resetRequestForm.setError(errors)
        },
    })
}
</script>

<template>
    <Head title="Reset access" />

    <AuthCard
        :logo="appFavicon"
        :alt="appName"
        eyebrow="Account recovery"
        :subtitle="`Secure access to ${appName}`"
        title="Forgot your password?"
        description="Enter your email and we’ll send a secure reset link."
    >
        <template #story>
            <a :href="route('home')" class="inline-flex items-center gap-3 text-sm font-bold text-[var(--ink)]">
                <img :src="appFavicon" :alt="appName" class="h-10 w-10 rounded-xl object-cover shadow-[0_8px_18px_rgb(46_196_230_/_16%)]" />
                {{ appName }}
            </a>
            <div class="mt-12 flex h-48 w-48 items-center justify-center rounded-[2.5rem] border border-white/70 bg-white/60 p-5 shadow-[var(--shadow-md)]">
                <img :src="appFavicon" :alt="`${appName} mark`" class="h-full w-full rounded-[1.7rem] object-cover" />
            </div>
            <p class="mt-9 text-xs font-bold uppercase tracking-[0.18em] text-[var(--primary)]">Back in the flow</p>
            <h1 class="mt-4 max-w-md text-4xl font-bold leading-[1.05] tracking-[-0.05em] text-[var(--ink)] sm:text-5xl">A small reset. A clear next step.</h1>
            <p class="mt-5 max-w-md text-base leading-7 text-[var(--muted)]">We’ll help you get back to your workspace securely, so you can keep your ideas moving.</p>
        </template>

        <form class="mt-8 space-y-5" @submit.prevent="handleForgotPassword">
            <div>
                <label for="email" class="auth-label">Email address</label>
                <input id="email" v-model="resetRequestForm.email" type="email" autocomplete="email" placeholder="you@example.com" autofocus class="auth-input" :class="{ 'is-invalid': resetRequestForm.errors.email }" />
                <p v-if="resetRequestForm.errors.email" class="auth-error">{{ resetRequestForm.errors.email }}</p>
            </div>

            <button type="submit" :disabled="resetRequestForm.processing" class="auth-button w-full">
                <FontAwesomeIcon v-if="resetRequestForm.processing" icon="spinner" spin />
                <FontAwesomeIcon v-else icon="key" />
                {{ resetRequestForm.processing ? 'Sending link...' : 'Send reset link' }}
            </button>
        </form>

        <p class="mt-7 text-center text-sm text-[var(--muted)]">
            Remembered your password?
            <a :href="route('login')" class="auth-link ml-1">Back to log in</a>
        </p>
    </AuthCard>
</template>

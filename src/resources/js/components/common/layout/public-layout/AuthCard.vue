<script setup>
defineProps({
    logo: {
        type: String,
        default: '',
    },
    alt: {
        type: String,
        default: '',
    },
    eyebrow: {
        type: String,
        default: '',
    },
    subtitle: {
        type: String,
        default: '',
    },
    title: {
        type: String,
        default: '',
    },
    description: {
        type: String,
        default: '',
    },
    wide: {
        type: Boolean,
        default: false,
    },
})
</script>

<template>
    <div class="auth-page">
        <div class="ai-container grid items-center gap-[clamp(2rem,6vw,5rem)] md:grid-cols-[minmax(0,1fr)_minmax(25rem,30rem)]">
            <aside class="hidden md:block md:max-w-[32rem]">
                <slot name="story" />
            </aside>

            <section class="auth-card" :class="{ 'auth-card--wide': wide }">
                <div class="flex items-center gap-3">
                    <img :src="logo" :alt="alt" class="h-12 w-12 rounded-[13px] object-cover shadow-[0_8px_18px_rgb(46_196_230_/_18%)]" />
                    <div>
                        <p class="text-xs font-bold uppercase tracking-[0.14em] text-[var(--primary)]">{{ eyebrow }}</p>
                        <p class="mt-0.5 text-xs text-[var(--muted)]">{{ subtitle }}</p>
                    </div>
                </div>

                <div class="mt-8">
                    <h2 class="text-3xl font-bold tracking-[-0.04em] text-[var(--ink)]">{{ title }}</h2>
                    <p class="mt-2 text-sm leading-6 text-[var(--muted)]">{{ description }}</p>
                </div>

                <slot />
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
    background: var(--gradient-hero);
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

.auth-card {
    width: min(100%, 30rem);
    margin-inline: auto;
    border: 1px solid rgb(255 255 255 / 80%);
    border-radius: var(--border-radius-lg);
    background: rgb(255 255 255 / 92%);
    box-shadow: var(--shadow-lg);
    padding: clamp(1.5rem, 4vw, 2.5rem);
    backdrop-filter: blur(18px);
}

.auth-card--wide {
    width: min(100%, 34rem);
}

.auth-card :deep(.auth-label) {
    display: block;
    margin-bottom: 0.45rem;
    color: var(--ink-soft);
    font-size: 0.82rem;
    font-weight: 600;
    letter-spacing: 0.01em;
}

.auth-card :deep(.auth-input) {
    width: 100%;
    min-height: 3.1rem;
    border: 1px solid var(--border-strong);
    border-radius: var(--border-radius-sm);
    background: rgb(255 255 255 / 80%);
    color: var(--ink);
    padding: 0.78rem 0.9rem;
    outline: 0;
}

.auth-card :deep(.auth-input)::placeholder {
    color: var(--muted-light);
}

.auth-card :deep(.auth-input):focus {
    border-color: var(--primary);
    box-shadow: var(--focus-ring);
}

.auth-card :deep(.auth-input.is-invalid) {
    border-color: var(--danger);
}

.auth-card :deep(.auth-input.is-invalid):focus {
    box-shadow: 0 0 0 4px rgb(217 45 32 / 12%);
}

.auth-card :deep(.auth-error) {
    margin-top: 0.45rem;
    color: var(--danger);
    font-size: 0.78rem;
    line-height: 1.4;
}

.auth-card :deep(.auth-button) {
    display: inline-flex;
    min-height: 3.1rem;
    align-items: center;
    justify-content: center;
    gap: 0.55rem;
    border: 0;
    border-radius: var(--border-radius-sm);
    background: var(--gradient-brand);
    color: white;
    padding: 0.75rem 1.15rem;
    font-weight: 700;
    box-shadow: var(--shadow-primary);
}

.auth-card :deep(.auth-button:hover:not(:disabled)) {
    transform: translateY(-1px);
    box-shadow: 0 18px 32px rgb(91 92 240 / 30%);
}

.auth-card :deep(.auth-button:disabled) {
    cursor: not-allowed;
    opacity: 0.65;
}

.auth-card :deep(.auth-link) {
    color: var(--primary-strong);
    font-weight: 600;
}

.auth-card :deep(.auth-link:hover) {
    color: var(--primary);
}

@media (max-width: 640px) {
    .auth-page {
        min-height: calc(100vh - 8rem);
    }
}
</style>
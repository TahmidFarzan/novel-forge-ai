<script setup>
import { Head } from '@inertiajs/vue3'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import {
    faArrowRight,
    faBolt,
    faBrain,
    faChartLine,
    faCircleCheck,
    faCubes,
    faGear,
    faWandMagicSparkles,
} from '@fortawesome/free-solid-svg-icons'
import PublicLayout from '@/Pages/layouts/PublicLayout.vue'

library.add(
    faArrowRight,
    faBolt,
    faBrain,
    faChartLine,
    faCircleCheck,
    faCubes,
    faGear,
    faWandMagicSparkles,
)

const appName = import.meta.env.VITE_APP_NAME || 'Novel Forge AI'
const appLogo = import.meta.env.VITE_APP_LOGO || '/uploads/icons/app/logo.png'
const appFavicon = import.meta.env.VITE_APP_FAVICON || '/uploads/icons/app/favicon.png'

const features = [
    {
        icon: 'wand-magic-sparkles',
        eyebrow: 'Generate',
        title: 'AI generation',
        description: 'Turn a blank page into thoughtful, high-quality work with a creative partner that keeps your intent in focus.',
        tone: 'violet',
    },
    {
        icon: 'bolt',
        eyebrow: 'Automate',
        title: 'Smart automation',
        description: 'Move repetitive steps into intelligent workflows so your team can spend more time on the decisions that matter.',
        tone: 'cyan',
    },
    {
        icon: 'brain',
        eyebrow: 'Understand',
        title: 'Intelligent processing',
        description: 'Give complex information structure, context, and clarity with AI that works across your entire creative process.',
        tone: 'gold',
    },
    {
        icon: 'chart-line',
        eyebrow: 'Accelerate',
        title: 'More productivity',
        description: 'Go from first thought to finished output faster with one focused workspace for the work ahead.',
        tone: 'blue',
    },
]

const workflow = [
    {
        number: '01',
        icon: 'wand-magic-sparkles',
        title: 'Share your input',
        description: 'Start with a prompt, a question, a rough idea, or the work already in progress.',
    },
    {
        number: '02',
        icon: 'gear',
        title: 'Let AI process it',
        description: 'Our intelligent engine finds patterns, builds context, and turns direction into momentum.',
    },
    {
        number: '03',
        icon: 'circle-check',
        title: 'Shape the output',
        description: 'Review, refine, and move forward with an output that feels unmistakably yours.',
    },
]

const technology = [
    { icon: 'brain', label: 'Adaptive AI models' },
    { icon: 'bolt', label: 'Workflow automation' },
    { icon: 'cubes', label: 'Future-ready platform' },
]
</script>

<template>
    <Head :title="`${appName} — Build with intelligence`" />

    <PublicLayout>
        <div class="home-page overflow-hidden">
        <section class="hero-section relative isolate overflow-hidden border-b border-[var(--novel-forge-ai-border)]">
            <div class="hero-glow hero-glow--one" aria-hidden="true"></div>
            <div class="hero-glow hero-glow--two" aria-hidden="true"></div>
            <div class="ai-container relative grid items-center gap-14 py-16 sm:py-20 lg:grid-cols-[minmax(0,0.92fr)_minmax(27rem,1.08fr)] lg:gap-10 lg:py-28">
                <div class="max-w-2xl">
                    <div class="mb-6 inline-flex items-center gap-2 rounded-full border border-[rgb(91_92_240_/_16%)] bg-white/70 px-3 py-1.5 text-xs font-bold uppercase tracking-[0.16em] text-[var(--novel-forge-ai-primary-strong)] shadow-[var(--novel-forge-ai-shadow-sm)]">
                        <span class="h-2 w-2 rounded-full bg-[var(--novel-forge-ai-cyan)] shadow-[0_0_0_4px_rgb(46_196_230_/_15%)]"></span>
                        The intelligent workspace
                    </div>

                    <h1 class="max-w-3xl text-5xl font-bold leading-[0.98] tracking-[-0.06em] text-[var(--novel-forge-ai-ink)] sm:text-6xl lg:text-7xl">
                        Make your next big idea
                        <span class="ai-gradient-text">unstoppable.</span>
                    </h1>

                    <p class="mt-7 max-w-xl text-base leading-7 text-[var(--novel-forge-ai-muted)] sm:text-lg sm:leading-8">
                        {{ appName }} gives ambitious people an AI-powered edge to think clearer, create faster, and turn possibility into polished work.
                    </p>

                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        <a :href="route('login')" class="ai-gradient-brand-bg inline-flex min-h-12 items-center justify-center gap-3 rounded-xl px-5 text-sm font-bold text-white shadow-[var(--novel-forge-ai-shadow-primary)] hover:-translate-y-0.5 hover:shadow-[0_18px_34px_rgb(91_92_240_/_30%)]">
                            Start building free
                            <FontAwesomeIcon icon="arrow-right" class="text-xs" />
                        </a>
                        <a href="#workflow" class="inline-flex min-h-12 items-center justify-center rounded-xl border border-[var(--novel-forge-ai-border-strong)] bg-white/70 px-5 text-sm font-bold text-[var(--novel-forge-ai-ink)] hover:-translate-y-0.5 hover:border-[var(--novel-forge-ai-primary)] hover:text-[var(--novel-forge-ai-primary-strong)]">
                            See how it works
                        </a>
                    </div>

                    <div class="mt-10 flex flex-wrap items-center gap-x-5 gap-y-3 text-sm text-[var(--novel-forge-ai-muted)]">
                        <span class="flex items-center gap-2"><FontAwesomeIcon icon="circle-check" class="text-[var(--novel-forge-ai-success)]" /> No credit card required</span>
                        <span class="flex items-center gap-2"><FontAwesomeIcon icon="circle-check" class="text-[var(--novel-forge-ai-success)]" /> Built for your workflow</span>
                    </div>
                </div>

                <div class="hero-visual relative mx-auto w-full max-w-xl lg:ml-auto" aria-label="AI workspace preview">
                    <div class="hero-visual__halo" aria-hidden="true"></div>
                    <div class="hero-console ai-gradient-dark-bg relative overflow-hidden rounded-[2rem] border border-white/15 p-3 shadow-[var(--novel-forge-ai-shadow-lg)] sm:p-5">
                        <div class="flex items-center justify-between border-b border-white/10 px-2 pb-4 sm:px-3">
                            <div class="flex items-center gap-2">
                                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-white/10">
                                    <img :src="appFavicon" :alt="appName" class="h-6 w-6 rounded-md object-cover" />
                                </span>
                                <span class="text-xs font-semibold text-white/80">AI workspace</span>
                            </div>
                            <span class="flex items-center gap-1.5 text-[10px] font-semibold text-[#8cf0c1]"><span class="h-1.5 w-1.5 rounded-full bg-[#49d995]"></span> Processing live</span>
                        </div>

                        <div class="relative flex min-h-[22rem] items-center justify-center overflow-hidden px-4 py-8 sm:min-h-[27rem]">
                            <div class="hero-ring hero-ring--outer" aria-hidden="true"></div>
                            <div class="hero-ring hero-ring--inner" aria-hidden="true"></div>
                            <div class="hero-logo-card relative flex h-56 w-56 items-center justify-center rounded-full border border-white/10 bg-black/20 shadow-[0_0_70px_rgb(46_196_230_/_16%)] sm:h-64 sm:w-64">
                                <img :src="appLogo" :alt="`${appName} logo`" class="h-48 w-48 object-contain sm:h-56 sm:w-56" />
                            </div>

                            <div class="absolute left-1 top-12 flex items-center gap-2 rounded-xl border border-white/10 bg-white/10 px-3 py-2 text-[10px] font-semibold text-white/85 shadow-lg backdrop-blur-md sm:left-7 sm:top-14">
                                <FontAwesomeIcon icon="wand-magic-sparkles" class="text-[#f8b84e]" />
                                Idea to insight
                            </div>
                            <div class="absolute bottom-12 right-1 flex items-center gap-2 rounded-xl border border-white/10 bg-white/10 px-3 py-2 text-[10px] font-semibold text-white/85 shadow-lg backdrop-blur-md sm:bottom-16 sm:right-7">
                                <FontAwesomeIcon icon="bolt" class="text-[#2ec4e6]" />
                                Momentum unlocked
                            </div>
                        </div>

                        <div class="grid grid-cols-3 gap-2 border-t border-white/10 pt-4 sm:gap-3 sm:px-2">
                            <div v-for="stat in [['94%', 'clarity'], ['3.2x', 'faster output'], ['24/7', 'ready to think']]" :key="stat[1]" class="rounded-xl bg-white/5 px-2 py-2.5 sm:px-3">
                                <p class="text-sm font-bold text-white sm:text-base">{{ stat[0] }}</p>
                                <p class="mt-0.5 text-[9px] font-medium text-white/45 sm:text-[10px]">{{ stat[1] }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="hero-chip hero-chip--top hidden items-center gap-3 rounded-2xl border border-[var(--novel-forge-ai-border)] bg-white p-3 shadow-[var(--novel-forge-ai-shadow-md)] sm:flex">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#fff4dc] text-[var(--novel-forge-ai-gold)]"><FontAwesomeIcon icon="brain" /></span>
                        <span><strong class="block text-xs text-[var(--novel-forge-ai-ink)]">Context understood</strong><small class="text-[10px] text-[var(--novel-forge-ai-muted)]">Your intent is in focus</small></span>
                    </div>
                    <div class="hero-chip hero-chip--bottom hidden items-center gap-3 rounded-2xl border border-[var(--novel-forge-ai-border)] bg-white p-3 shadow-[var(--novel-forge-ai-shadow-md)] sm:flex">
                        <span class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#e9fbff] text-[var(--novel-forge-ai-cyan)]"><FontAwesomeIcon icon="chart-line" /></span>
                        <span><strong class="block text-xs text-[var(--novel-forge-ai-ink)]">Output ready</strong><small class="text-[10px] text-[var(--novel-forge-ai-muted)]">Your next move is clear</small></span>
                    </div>
                </div>
            </div>
        </section>

        <section id="features" class="bg-white py-20 sm:py-24">
            <div class="ai-container">
                <div class="max-w-2xl">
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-[var(--novel-forge-ai-primary)]">One platform. More possibilities.</p>
                    <h2 class="mt-4 text-3xl font-bold tracking-[-0.04em] text-[var(--novel-forge-ai-ink)] sm:text-5xl">Everything you need to do your best work.</h2>
                    <p class="mt-5 text-base leading-7 text-[var(--novel-forge-ai-muted)] sm:text-lg">Powerful AI capabilities, presented simply, so you can stay in flow from the first prompt to the final result.</p>
                </div>

                <div class="mt-12 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <article v-for="feature in features" :key="feature.title" class="feature-card group rounded-[1.35rem] border border-[var(--novel-forge-ai-border)] bg-[var(--novel-forge-ai-surface)] p-6 shadow-[var(--novel-forge-ai-shadow-sm)] hover:-translate-y-1 hover:border-[rgb(91_92_240_/_25%)] hover:shadow-[var(--novel-forge-ai-shadow-md)]">
                        <div class="feature-icon" :class="`feature-icon--${feature.tone}`"><FontAwesomeIcon :icon="feature.icon" /></div>
                        <p class="mt-7 text-xs font-bold uppercase tracking-[0.14em] text-[var(--novel-forge-ai-muted-light)]">{{ feature.eyebrow }}</p>
                        <h3 class="mt-2 text-xl font-bold tracking-[-0.03em] text-[var(--novel-forge-ai-ink)]">{{ feature.title }}</h3>
                        <p class="mt-3 text-sm leading-6 text-[var(--novel-forge-ai-muted)]">{{ feature.description }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="workflow" class="border-y border-[var(--novel-forge-ai-border)] bg-[var(--novel-forge-ai-canvas)] py-20 sm:py-24">
            <div class="ai-container">
                <div class="flex flex-col justify-between gap-6 md:flex-row md:items-end">
                    <div class="max-w-2xl">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[var(--novel-forge-ai-primary)]">Simple by design</p>
                        <h2 class="mt-4 text-3xl font-bold tracking-[-0.04em] text-[var(--novel-forge-ai-ink)] sm:text-5xl">From thought to finished work in three clear steps.</h2>
                    </div>
                    <p class="max-w-sm text-sm leading-6 text-[var(--novel-forge-ai-muted)]">You bring the direction. {{ appName }} brings the intelligence to help you get there.</p>
                </div>

                <div class="mt-14 grid gap-8 md:grid-cols-3 md:gap-6">
                    <article v-for="(step, index) in workflow" :key="step.number" class="workflow-step relative">
                        <div v-if="index < workflow.length - 1" class="workflow-connector hidden md:block" aria-hidden="true"></div>
                        <div class="flex items-center justify-between">
                            <span class="ai-gradient-brand-bg flex h-12 w-12 items-center justify-center rounded-2xl text-white shadow-[var(--novel-forge-ai-shadow-primary)]"><FontAwesomeIcon :icon="step.icon" /></span>
                            <span class="text-4xl font-bold tracking-[-0.06em] text-[rgb(91_92_240_/_14%)]">{{ step.number }}</span>
                        </div>
                        <h3 class="mt-7 text-xl font-bold tracking-[-0.03em] text-[var(--novel-forge-ai-ink)]">{{ step.title }}</h3>
                        <p class="mt-3 max-w-xs text-sm leading-6 text-[var(--novel-forge-ai-muted)]">{{ step.description }}</p>
                    </article>
                </div>
            </div>
        </section>

        <section id="technology" class="relative overflow-hidden bg-[var(--novel-forge-ai-surface-dark)] py-20 text-white sm:py-24">
            <div class="technology-glow" aria-hidden="true"></div>
            <div class="ai-container relative grid items-center gap-12 lg:grid-cols-[1.05fr_0.95fr]">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#79dff3]">Built for what’s next</p>
                    <h2 class="mt-4 max-w-xl text-3xl font-bold tracking-[-0.04em] sm:text-5xl">Technology that feels like a creative advantage.</h2>
                    <p class="mt-5 max-w-xl text-base leading-7 text-white/60 sm:text-lg">Under the hood is a flexible AI foundation built to grow with your ideas, your workflows, and the way your team works tomorrow.</p>
                    <div class="mt-8 grid gap-3 sm:grid-cols-3">
                        <div v-for="item in technology" :key="item.label" class="rounded-2xl border border-white/10 bg-white/5 p-4 backdrop-blur-sm">
                            <FontAwesomeIcon :icon="item.icon" class="text-[#79dff3]" />
                            <p class="mt-4 text-sm font-semibold leading-5 text-white/90">{{ item.label }}</p>
                        </div>
                    </div>
                </div>

                <div class="technology-card relative mx-auto w-full max-w-md overflow-hidden rounded-[2rem] border border-white/10 bg-white/[0.07] p-5 shadow-[0_24px_70px_rgb(0_0_0_/_22%)] backdrop-blur-md">
                    <div class="flex items-center justify-between border-b border-white/10 pb-4">
                        <span class="text-xs font-bold uppercase tracking-[0.14em] text-white/50">Model orchestration</span>
                        <FontAwesomeIcon icon="cubes" class="text-[#f8b84e]" />
                    </div>
                    <div class="mt-7 flex items-center justify-center">
                        <div class="technology-logo-ring flex h-36 w-36 items-center justify-center rounded-full border border-[#79dff3]/30 bg-[#79dff3]/10 shadow-[0_0_50px_rgb(46_196_230_/_22%)]">
                            <img :src="appFavicon" :alt="appName" class="h-24 w-24 rounded-[1.4rem] object-cover" />
                        </div>
                    </div>
                    <div class="mt-7 space-y-2">
                        <div v-for="(model, index) in ['Reasoning engine', 'Creative intelligence', 'Automation layer']" :key="model" class="flex items-center justify-between rounded-xl bg-black/20 px-3 py-2.5 text-xs">
                            <span class="flex items-center gap-2 text-white/70"><span class="h-1.5 w-1.5 rounded-full" :class="index === 1 ? 'bg-[#f8b84e]' : 'bg-[#79dff3]'"></span>{{ model }}</span>
                            <span class="font-semibold text-[#8cf0c1]">Online</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="ai-gradient-soft-bg relative overflow-hidden py-20 sm:py-24">
            <div class="ai-container relative">
                <div class="rounded-[2rem] border border-[rgb(91_92_240_/_12%)] bg-white/75 px-6 py-12 text-center shadow-[var(--novel-forge-ai-shadow-md)] sm:px-12 sm:py-16">
                    <div class="ai-gradient-brand-bg mx-auto flex h-14 w-14 items-center justify-center rounded-2xl text-white shadow-[var(--novel-forge-ai-shadow-primary)]"><FontAwesomeIcon icon="wand-magic-sparkles" /></div>
                    <h2 class="mx-auto mt-7 max-w-2xl text-3xl font-bold tracking-[-0.04em] text-[var(--novel-forge-ai-ink)] sm:text-5xl">Your best work is closer than you think.</h2>
                    <p class="mx-auto mt-5 max-w-xl text-base leading-7 text-[var(--novel-forge-ai-muted)]">Create your account and give every idea the intelligent starting point it deserves.</p>
                    <a :href="route('login')" class="ai-gradient-brand-bg mt-8 inline-flex min-h-12 items-center justify-center gap-3 rounded-xl px-5 text-sm font-bold text-white shadow-[var(--novel-forge-ai-shadow-primary)] hover:-translate-y-0.5">
                        Create your workspace
                        <FontAwesomeIcon icon="arrow-right" class="text-xs" />
                    </a>
                </div>
            </div>
        </section>
        </div>
    </PublicLayout>
</template>

<style scoped>
.ai-gradient-text {
    background: var(--novel-forge-ai-gradient-brand);
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
}

.ai-gradient-brand-bg {
    background: var(--novel-forge-ai-gradient-brand);
}

.ai-gradient-dark-bg {
    background: var(--novel-forge-ai-gradient-dark);
}

.ai-gradient-soft-bg {
    background: var(--novel-forge-ai-gradient-soft);
}

.ai-dot-grid {
    background-image: radial-gradient(rgb(91 92 240 / 14%) 1px, transparent 1px);
    background-size: 18px 18px;
}

.hero-section {
    background: var(--novel-forge-ai-gradient-hero);
}

.hero-glow,
.technology-glow {
    position: absolute;
    border-radius: 999px;
    pointer-events: none;
}

.hero-glow--one {
    top: -12rem;
    right: 13%;
    width: 28rem;
    height: 28rem;
    background: rgb(91 92 240 / 14%);
    filter: blur(8px);
}

.hero-glow--two {
    bottom: -16rem;
    left: -8rem;
    width: 26rem;
    height: 26rem;
    background: rgb(46 196 230 / 12%);
    filter: blur(4px);
}

.hero-visual__halo {
    position: absolute;
    inset: 12% 8%;
    border-radius: 999px;
    background: rgb(91 92 240 / 24%);
    filter: blur(70px);
}

.hero-ring {
    position: absolute;
    border: 1px solid rgb(121 223 243 / 22%);
    border-radius: 999px;
}

.hero-ring--outer {
    width: 21rem;
    height: 21rem;
    transform: rotate(28deg) scaleY(0.6);
}

.hero-ring--inner {
    width: 16rem;
    height: 16rem;
    border-color: rgb(248 184 78 / 23%);
    transform: rotate(-26deg) scaleY(0.6);
}

.hero-chip {
    position: absolute;
    z-index: 2;
}

.hero-chip--top {
    top: 3.5rem;
    right: -3rem;
}

.hero-chip--bottom {
    bottom: 3rem;
    left: -3rem;
}

.feature-card,
.workflow-step,
.technology-card {
    transition: transform var(--novel-forge-ai-transition), border-color var(--novel-forge-ai-transition), box-shadow var(--novel-forge-ai-transition);
}

.feature-icon {
    display: flex;
    width: 3rem;
    height: 3rem;
    align-items: center;
    justify-content: center;
    border-radius: 13px;
    font-size: 1rem;
}

.feature-icon--violet {
    background: #eeefff;
    color: #5b5cf0;
}

.feature-icon--cyan {
    background: #e9fbff;
    color: #129bb9;
}

.feature-icon--gold {
    background: #fff4dc;
    color: #c98712;
}

.feature-icon--blue {
    background: #e9f1ff;
    color: #3478dd;
}

.workflow-connector {
    position: absolute;
    top: 1.5rem;
    left: 4rem;
    width: calc(100% - 1rem);
    border-top: 1px dashed rgb(91 92 240 / 28%);
}

.technology-glow {
    top: -18rem;
    right: 8%;
    width: 32rem;
    height: 32rem;
    background: rgb(91 92 240 / 22%);
    filter: blur(80px);
}

.technology-logo-ring {
    animation: breathe 4s ease-in-out infinite;
}

@keyframes breathe {
    0%,
    100% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.04);
    }
}

@media (max-width: 640px) {
    .hero-ring--outer {
        width: 18rem;
        height: 18rem;
    }

    .hero-ring--inner {
        width: 14rem;
        height: 14rem;
    }
}
</style>

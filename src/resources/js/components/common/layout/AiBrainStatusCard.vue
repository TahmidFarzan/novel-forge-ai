<script setup>
import { computed } from 'vue'
import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
import { library } from '@fortawesome/fontawesome-svg-core'
import { faBrain } from '@fortawesome/free-solid-svg-icons'

library.add(faBrain)

const { item } = defineProps({
    item: {
        type: Object,
        required: true,
    },
})

const isActive = computed(() => item?.is_active === true)
</script>

<template>
    <article class="flex w-full min-w-0 items-center gap-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface)] px-3 py-1.5 shadow-[var(--shadow-sm)]">
        <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center overflow-hidden rounded-lg text-white [background:var(--gradient-brand)] shadow-[var(--shadow-sm)]">
            <img v-if="item.icon" :src="item.icon" :alt="item.name" class="h-full w-full object-cover">
            <FontAwesomeIcon v-else icon="brain" class="text-sm" />
        </span>

        <span class="flex min-w-0 flex-1 flex-col">
            <span class="truncate text-xs font-bold text-[var(--ink)]">{{ item.name || 'Unknown AI Brain' }}</span>
            <span v-if="item.model_name" class="truncate text-[11px] leading-tight text-[var(--muted)]">{{ item.model_name }}</span>
        </span>

        <span v-if="item.profile"
            class="hidden max-w-24 flex-shrink-0 truncate rounded-full border border-[var(--border)] px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-[var(--muted)] sm:inline-flex">
            {{ item.profile }}
        </span>

        <span class="flex flex-shrink-0 items-center gap-1.5 rounded-full px-2 py-0.5 text-[10px] font-bold"
            :class="isActive ? 'bg-[var(--success)]/10 text-[var(--success)]' : 'bg-[var(--danger)]/10 text-[var(--danger)]'">
            <span class="h-1.5 w-1.5 rounded-full"
                :class="isActive ? 'bg-[var(--success)] shadow-[0_0_0_3px_rgb(18_183_106_/_18%)]' : 'bg-[var(--danger)] shadow-[0_0_0_3px_rgb(217_45_32_/_15%)]'"></span>
            {{ isActive ? 'Active' : 'Offline' }}
        </span>
    </article>
</template>
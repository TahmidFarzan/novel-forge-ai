<script setup>
import { computed, nextTick, onMounted, ref } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Autoplay, Pagination } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/pagination'
import { fetchFromApi } from '@/composables/useApiClient'
import { apiCacheKey, apiCacheTTL } from '@/composables/useApiCache'
import AiBrainStatusCard from '@/components/common/layout/AiBrainStatusCard.vue'

const modules = [Autoplay, Pagination]

const swiperBreakpoints = {
    0: { slidesPerView: 1, spaceBetween: 10 },
    640: { slidesPerView: 1, spaceBetween: 12 },
    1024: { slidesPerView: 1, spaceBetween: 16 },
}

const page = usePage()
const endpoint = route('layout.ai-brains')

const items = ref([])
const currentPage = ref(1)
const lastPage = ref(1)
const loading = ref(false)
const loadingMore = ref(false)

const knownIds = new Set()
const swiperInstance = ref(null)

const hasMultiple = computed(() => items.value.length > 1)

const applyPage = (data, reset = false) => {
    if (!data || Array.isArray(data) || !Array.isArray(data.items)) return false

    if (reset) {
        knownIds.clear()
    }

    const fresh = data.items.filter(
        (item) => item && item.id != null && !knownIds.has(item.id)
    )

    fresh.forEach((item) => knownIds.add(item.id))

    items.value = reset ? fresh : [...items.value, ...fresh]

    currentPage.value = Number(data.current_page) || 1
    lastPage.value = Number(data.last_page) || 1

    return true
}

const refreshSwiper = async () => {
    await nextTick()

    const swiper = swiperInstance.value

    if (!swiper) return

    try {
        if (swiper.params.loop) {
            swiper.loopDestroy()
            swiper.update()
            swiper.loopCreate()
        } else {
            swiper.update()
        }
    } catch {
        swiper.update()
    }
}

const fetchPage = async (pageNumber, reset = false) => {
    if (loading.value || loadingMore.value) return

    if (reset) {
        loading.value = true
    } else {
        loadingMore.value = true
    }

    try {
        const data = await fetchFromApi(
            endpoint,
            { page: pageNumber },
            {
                key: `${apiCacheKey.DEFAULT}:ai-brains`,
                ttl: apiCacheTTL.SYSTEM_SHORT,
            }
        )

        if (applyPage(data, reset)) {
            await refreshSwiper()
        }
    } finally {
        loading.value = false
        loadingMore.value = false
    }
}

const preloadNextPage = () => {
    if (loadingMore.value || loading.value) return
    if (currentPage.value >= lastPage.value) return

    fetchPage(currentPage.value + 1)
}

const onSwiper = (swiper) => {
    swiperInstance.value = swiper
}

const onSlideChange = (swiper) => {
    if (loadingMore.value) return
    if (items.value.length === 0) return

    if (swiper.realIndex >= items.value.length - 3) {
        preloadNextPage()
    }
}

onMounted(async () => {
    const initial = page.props.aiBrains

    if (initial?.items?.length) {
        applyPage(initial)
    } else if (initial) {
        applyPage(initial, true)
    } else {
        await fetchPage(1, true)
    }
})
</script>

<template>
    <div class="ai-brain-status-slider flex min-w-0 items-center">
        <div v-if="loading" class="flex h-10 w-full max-w-md animate-pulse items-center gap-2.5 rounded-xl border border-[var(--border)] bg-[var(--surface)]/70 px-3 shadow-[var(--shadow-sm)]">
            <span class="h-8 w-8 rounded-lg bg-[var(--border)]"></span>
            <span class="flex-1 space-y-1.5">
                <span class="block h-2.5 w-2/3 rounded-full bg-[var(--border)]"></span>
                <span class="block h-2 w-1/3 rounded-full bg-[var(--border)]"></span>
            </span>
        </div>

        <Swiper
            v-else-if="hasMultiple"
            :modules="modules"
            class="ai-brain-status-swiper w-full max-w-md min-w-0"
            :breakpoints="swiperBreakpoints"
            :loop="true"
            :speed="650"
            :grab-cursor="true"
            :autoplay="{ delay: 4000, disableOnInteraction: false, pauseOnMouseEnter: true }"
            :pagination="{ clickable: true }"
            @swiper="onSwiper"
            @slide-change="onSlideChange"
            @reach-end="preloadNextPage"
        >
            <SwiperSlide v-for="item in items" :key="item.id">
                <AiBrainStatusCard :item="item" />
            </SwiperSlide>

            <div class="swiper-pagination"></div>
        </Swiper>

        <AiBrainStatusCard v-else-if="items.length === 1" :item="items[0]" class="max-w-md" />
    </div>
</template>

<style scoped>
.ai-brain-status-swiper {
    padding-bottom: 0;
}

.ai-brain-status-swiper :deep(.swiper-wrapper) {
    align-items: center;
}

.ai-brain-status-swiper :deep(.swiper-pagination) {
    position: static;
    margin-top: 4px;
}

.ai-brain-status-swiper :deep(.swiper-pagination-bullet) {
    width: 12px;
    height: 3px;
    border-radius: 999px;
    background: var(--border-strong);
    opacity: 1;
    transition: width var(--transition), background-color var(--transition);
}

.ai-brain-status-swiper :deep(.swiper-pagination-bullet-active) {
    width: 20px;
    background: var(--primary);
}
</style>
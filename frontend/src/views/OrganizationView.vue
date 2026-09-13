<script setup>
import { computed, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import { useOrganizationStore } from '../stores/organization'
import ReviewCard from '../components/ReviewCard.vue'
import Pagination from '../components/Pagination.vue'

const store = useOrganizationStore()
const router = useRouter()

onMounted(async () => {
  await store.fetchOrganization()

  if (!store.organization) {
    router.push({ name: 'settings' })
    return
  }

  if (store.organization.parsing_status === 'done') {
    store.fetchReviews(1)
  }
})

onUnmounted(() => store.stopPolling())

const org = computed(() => store.organization)

function handlePageChange(page) {
  store.fetchReviews(page)
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

const statusText = computed(() => {
  switch (org.value?.parsing_status) {
    case 'queued':
      return 'Организация в очереди на парсинг…'
    case 'in_progress':
      return 'Собираем отзывы, это может занять некоторое время…'
    case 'failed':
      return org.value?.parsing_error || 'Не удалось спарсить организацию.'
    case 'markup_changed':
      return 'Изменился формат данных прочитать данные невозможно. Зайдите позже или обновите парсер.'
    default:
      return null
  }
})
</script>

<template>
  <div>
    <div v-if="store.loading" class="card">Загрузка…</div>
    <div v-else-if="store.error" class="card error">{{ store.error }}</div>

    <template v-else-if="org">
      <div class="card">
        <div class="review-head">
          <h1 style="font-size: 20px; margin: 0;">{{ org.name || 'Организация' }}</h1>
          <span class="badge" :class="org.parsing_status">{{ org.parsing_status }}</span>
        </div>

        <div v-if="statusText" class="muted" style="margin-top: 8px;">{{ statusText }}</div>

        <div v-if="org.parsing_status === 'done'" class="stats">
          <div class="stat">
            <b>{{ org.rating ?? '—' }}</b>
            <span class="muted">Средний рейтинг</span>
          </div>
          <div class="stat">
            <b>{{ org.ratings_count ?? '—' }}</b>
            <span class="muted">Оценок</span>
          </div>
          <div class="stat">
            <b>{{ org.reviews_count ?? '—' }}</b>
            <span class="muted">Отзывов</span>
          </div>
        </div>
      </div>

      <div v-if="org.parsing_status === 'done'" class="card">
        <div v-if="store.reviewsLoading">Загружаем отзывы…</div>
        <div v-else-if="store.reviewsError" class="error">{{ store.reviewsError }}</div>
        <div v-else-if="store.reviews.length === 0" class="muted">Отзывов пока нет.</div>
        <template v-else>
          <ReviewCard v-for="review in store.reviews" :key="review.id" :review="review" />
          <Pagination
            :current-page="store.reviewsMeta.current_page"
            :last-page="store.reviewsMeta.last_page"
            @change="handlePageChange"
          />
        </template>
      </div>
    </template>
  </div>
</template>

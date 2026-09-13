<script setup>
defineProps({
  review: { type: Object, required: true },
})

function formatDate(iso) {
  if (!iso) return ''
  return new Date(iso).toLocaleDateString('ru-RU', { year: 'numeric', month: 'long', day: 'numeric' })
}
</script>

<template>
  <div class="review">
    <div class="review-head">
      <span><b>{{ review.author || 'Без имени' }}</b></span>
      <span class="muted">{{ formatDate(review.published_at) }}</span>
    </div>
    <div class="muted" style="margin-bottom: 4px;">Оценка: {{ review.rating ?? '—' }} / 5</div>
    <p v-if="review.text" style="margin: 0;">{{ review.text }}</p>
    <p v-else class="muted" style="margin: 0;">Без текста (только оценка)</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  currentPage: { type: Number, required: true },
  lastPage: { type: Number, required: true },
})
const emit = defineEmits(['change'])

const pages = computed(() => {
  const total = props.lastPage
  const current = props.currentPage
  const result = []

  for (let p = 1; p <= total; p++) {
    if (p === 1 || p === total || Math.abs(p - current) <= 1) {
      result.push(p)
    } else if (result[result.length - 1] !== '…') {
      result.push('…')
    }
  }

  return result
})

function go(page) {
  if (page === '…' || page === props.currentPage) return
  emit('change', page)
}
</script>

<template>
  <div v-if="lastPage > 1" class="pagination">
    <button :disabled="currentPage === 1" @click="go(currentPage - 1)">← Назад</button>

    <template v-for="(p, idx) in pages" :key="idx">
      <span v-if="p === '…'" class="muted" style="padding: 6px 4px;">…</span>
      <button v-else :class="{ active: p === currentPage }" @click="go(p)">{{ p }}</button>
    </template>

    <button :disabled="currentPage === lastPage" @click="go(currentPage + 1)">Вперёд →</button>
  </div>
</template>

<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import { useOrganizationStore } from '../stores/organization'

const store = useOrganizationStore()
const url = ref('')

onMounted(async () => {
  await store.fetchOrganization()
  if (store.organization) {
    url.value = store.organization.source_url
  }
})

onUnmounted(() => store.stopPolling())

async function handleSubmit() {
  try {
    await store.saveUrl(url.value)
  } catch {
    // ошибка уже отражена в store.saveError
  }
}

const statusLabels = {
  pending: 'Ожидает парсинга',
  queued: 'В очереди',
  in_progress: 'Парсинг идёт…',
  done: 'Готово',
  failed: 'Ошибка парсинга',
  markup_changed: 'Источник изменил формат данных',
}
</script>

<template>
  <div>
    <h1 style="font-size: 20px;">Настройки</h1>

    <div class="card">
      <label class="muted">Ссылка на организацию в Яндекс.Картах</label>
      <form @submit.prevent="handleSubmit" style="display:flex; gap:8px; margin-top:6px;">
        <input
          v-model="url"
          type="url"
          placeholder="https://yandex.ru/maps/org/.../1234567890/"
          required
        />
        <button class="primary" type="submit" :disabled="store.saving">
          {{ store.saving ? 'Сохраняем…' : 'Сохранить' }}
        </button>
      </form>
      <p v-if="store.saveError" class="error">{{ store.saveError }}</p>
    </div>

    <div v-if="store.organization" class="card">
      <div class="review-head">
        <span><b>{{ store.organization.name || 'Название пока не получено' }}</b></span>
        <span class="badge" :class="store.organization.parsing_status">
          {{ statusLabels[store.organization.parsing_status] || store.organization.parsing_status }}
        </span>
      </div>

      <p v-if="store.organization.parsing_status === 'markup_changed'" class="error">
        Не удалось разобрать ответ Яндекса — похоже, изменилась структура данных на их стороне.
        Подробности в логах сервера.
      </p>
      <p v-else-if="store.organization.parsing_status === 'failed'" class="error">
        {{ store.organization.parsing_error }}
      </p>

      <button
        v-if="['failed', 'markup_changed', 'done'].includes(store.organization.parsing_status)"
        class="primary"
        style="margin-top: 8px;"
        @click="store.reparse"
      >
        Спарсить заново
      </button>
    </div>
  </div>
</template>

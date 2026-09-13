<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()

const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref(null)

async function handleSubmit() {
  loading.value = true
  error.value = null
  try {
    await auth.login(email.value, password.value)
    router.push({ name: 'organization' })
  } catch (e) {
    error.value = e.response?.data?.message || 'Неверный email или пароль.'
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <div class="card" style="max-width: 360px; margin: 60px auto 0;">
    <h1 style="font-size: 20px;">Вход</h1>
    <form @submit.prevent="handleSubmit">
      <div style="margin-bottom: 12px;">
        <label class="muted">Email</label>
        <input v-model="email" type="email" required autocomplete="username" />
      </div>
      <div style="margin-bottom: 16px;">
        <label class="muted">Пароль</label>
        <input v-model="password" type="password" required autocomplete="current-password" />
      </div>
      <button class="primary" type="submit" :disabled="loading" style="width: 100%;">
        {{ loading ? 'Входим…' : 'Войти' }}
      </button>
      <p v-if="error" class="error">{{ error }}</p>
    </form>
  </div>
</template>

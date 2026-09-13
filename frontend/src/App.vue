<script setup>
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from './stores/auth'

const auth = useAuthStore()
const router = useRouter()

const isAuthenticated = computed(() => auth.isAuthenticated)

async function handleLogout() {
  await auth.logout()
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="app">
    <header v-if="isAuthenticated" class="topbar">
      <nav>
        <RouterLink to="/">Отзывы</RouterLink>
        <RouterLink to="/settings">Настройки</RouterLink>
      </nav>
      <button class="link-btn" @click="handleLogout">Выйти</button>
    </header>

    <main class="container">
      <RouterView />
    </main>
  </div>
</template>

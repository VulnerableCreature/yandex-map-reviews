import { defineStore } from 'pinia'
import http, { ensureCsrfCookie } from '../api/http'

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    checked: false,
  }),
  getters: {
    isAuthenticated: (state) => !!state.user,
  },
  actions: {
    async login(email, password) {
      await ensureCsrfCookie()
      let res = await http.post('/api/login', { email, password })
      await this.fetchMe()
    },
    async logout() {
      await http.post('/api/logout')
      this.user = null
    },
    async fetchMe() {
      try {
        const { data } = await http.get('/api/me')
        this.user = data.user
      } catch {
        this.user = null
      } finally {
        this.checked = true
      }
    },
  },
})

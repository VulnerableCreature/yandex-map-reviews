import { defineStore } from 'pinia'
import http from '../api/http'

const ACTIVE_STATUSES = ['queued', 'in_progress']

export const useOrganizationStore = defineStore('organization', {
  state: () => ({
    organization: null,
    loading: false,
    error: null,

    reviews: [],
    reviewsMeta: { current_page: 1, last_page: 1, per_page: 50, total: 0 },
    reviewsLoading: false,
    reviewsError: null,

    saving: false,
    saveError: null,

    pollTimer: null,
  }),
  getters: {
    isParsing: (state) => ACTIVE_STATUSES.includes(state.organization?.parsing_status),
  },
  actions: {
    async fetchOrganization() {
      this.loading = true
      this.error = null
      try {
        const { data } = await http.get('/api/organization')
        this.organization = data.organization
        this.maybeStartPolling()
      } catch (e) {
        this.error = e.response?.data?.message || 'Не удалось загрузить данные организации.'
      } finally {
        this.loading = false
      }
    },

    async saveUrl(url) {
      this.saving = true
      this.saveError = null
      try {
        const { data } = await http.post('/api/organization', { url })
        this.organization = data.organization
        this.reviews = []
        this.reviewsMeta = { current_page: 1, last_page: 1, per_page: 50, total: 0 }
        this.maybeStartPolling()
      } catch (e) {
        this.saveError = e.response?.data?.message || 'Не удалось сохранить ссылку.'
        throw e
      } finally {
        this.saving = false
      }
    },

    async reparse() {
      this.saveError = null
      try {
        const { data } = await http.post('/api/organization/reparse')
        this.organization = data.organization
        this.maybeStartPolling()
      } catch (e) {
        this.saveError = e.response?.data?.message || 'Не удалось запустить парсинг заново.'
      }
    },

    async fetchReviews(page = 1) {
      this.reviewsLoading = true
      this.reviewsError = null
      try {
        const { data } = await http.get('/api/reviews', { params: { page } })
        this.reviews = data.data
        this.reviewsMeta = data.meta
      } catch (e) {
        this.reviewsError = e.response?.data?.message || 'Не удалось загрузить отзывы.'
      } finally {
        this.reviewsLoading = false
      }
    },

    maybeStartPolling() {
      this.stopPolling()
      if (!this.isParsing) return

      this.pollTimer = setInterval(async () => {
        try {
          const { data } = await http.get('/api/organization')
          this.organization = data.organization
          if (!ACTIVE_STATUSES.includes(data.organization?.parsing_status)) {
            this.stopPolling()
            this.fetchReviews(1)
          }
        } catch {
        }
      }, 3000)
    },

    stopPolling() {
      if (this.pollTimer) {
        clearInterval(this.pollTimer)
        this.pollTimer = null
      }
    },
  },
})

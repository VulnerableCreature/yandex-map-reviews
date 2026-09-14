import axios from 'axios'

const http = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
  withCredentials: true,
  withXSRFToken: true,
})

const authHttp = axios.create({
  baseURL: '/',
  withCredentials: true,
  withXSRFToken: true,
})

export const ensureCsrfCookie = () => {
  return authHttp.get('/sanctum/csrf-cookie')
}

export default http
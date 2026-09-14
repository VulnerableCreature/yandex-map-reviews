import axios from 'axios'

const http = axios.create({
  baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000',
  withCredentials: true,
  withXSRFToken: true,
})

export const getCsrfCookie = () => {
  return axios.get('/sanctum/csrf-cookie', {
    withCredentials: true,
    withXSRFToken: true,
  })
}

export default http
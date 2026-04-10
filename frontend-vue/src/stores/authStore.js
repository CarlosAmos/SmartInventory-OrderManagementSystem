import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useCartStore } from '@/stores/cartStore'

export const useAuthStore = defineStore('auth', () => {
  // Initialize state from localStorage
  const token = ref(localStorage.getItem('auth_token'))
  const user = ref(null)

  // Load user from localStorage on init
  const storedUser = localStorage.getItem('user')
  if (storedUser) {
    try {
      user.value = JSON.parse(storedUser)
      useCartStore().loadForUser(user.value.id)
    } catch (e) {
      console.error('Failed to parse user from localStorage:', e)
      localStorage.removeItem('user')
    }
  }

  // Computed
  const isAuthenticated = computed(() => !!token.value)

  // Actions
  const setAuth = (authToken, userData) => {
    token.value = authToken
    user.value = userData

    localStorage.setItem('auth_token', authToken)
    localStorage.setItem('user', JSON.stringify(userData))

    useCartStore().loadForUser(userData.id)
  }

  const clearAuth = () => {
    token.value = null
    user.value = null

    localStorage.removeItem('auth_token')
    localStorage.removeItem('user')

    useCartStore().resetCart()
  }

  const getToken = () => {
    return token.value
  }

  const getUser = () => {
    return user.value
  }

  return {
    // State
    token,
    user,
    
    // Computed
    isAuthenticated,
    
    // Actions
    setAuth,
    clearAuth,
    getToken,
    getUser
  }
})
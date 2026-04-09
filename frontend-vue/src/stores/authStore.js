import { defineStore } from 'pinia'
import { ref, computed } from 'vue'

export const useAuthStore = defineStore('auth', () => {
  // Initialize state from localStorage
  const token = ref(localStorage.getItem('auth_token'))
  const user = ref(null)

  // Load user from localStorage on init
  const storedUser = localStorage.getItem('user')
  if (storedUser) {
    try {
      user.value = JSON.parse(storedUser)
    } catch (e) {
      console.error('Failed to parse user from localStorage:', e)
      localStorage.removeItem('user')
    }
  }

  // Computed
  const isAuthenticated = computed(() => !!token.value)

  // Actions
  const setAuth = (authToken, userData) => {
    console.log('setAuth called with:', { authToken, userData }) // DEBUG
    
    // Set reactive state
    token.value = authToken
    user.value = userData
    
    // Persist to localStorage
    localStorage.setItem('auth_token', authToken)
    localStorage.setItem('user', JSON.stringify(userData))
    
    console.log('Auth set. Token:', token.value) // DEBUG
  }

  const clearAuth = () => {
    console.log('clearAuth called') // DEBUG
    
    // Clear reactive state
    token.value = null
    user.value = null
    
    // Clear localStorage
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user')
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
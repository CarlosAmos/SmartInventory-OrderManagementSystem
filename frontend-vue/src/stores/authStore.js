import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useAuthStore = defineStore('auth', () => {
  // ? State
  const token = ref(localStorage.getItem('auth_token') || null);
  const user = ref(JSON.parse(localStorage.getItem('user') || 'null'));

  // ? Computed
  const isAuthenticated = computed(() => !!token.value);

  // ? Actions
  const setAuth = (authToken, userData) => {
    token.value = authToken;
    user.value = userData;
    
    // ? Persist to localStorage
    localStorage.setItem('auth_token', authToken);
    localStorage.setItem('user', JSON.stringify(userData));
  }

  const clearAuth = () => {
    token.value = null;
    user.value = null;
    
    // ? Clear from localStorage
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
  }

  const getToken = () => {
    return token.value;
  }

  const getUser = () => {
    return user.value;
  }

  return {
    // ? State
    token,
    user,
    
    // ? Computed
    isAuthenticated,
    
    // ? Actions
    setAuth,
    clearAuth,
    getToken,
    getUser
  }
})
import apiClient from '@/api/client'
import { useAuthStore } from '@/stores/authStore'

export default {
  async login(email, password) {
    const response = await apiClient.post('/login', { 
      email, 
      password 
    });
    
    const { access_token: token, user } = response.data

    // ? Store token and user using pinia
    const authStore = useAuthStore()
    authStore.setAuth(token, user);
    
    return response.data;
  },

  async logout() {
    try {
      await apiClient.post('/logout');
    } finally {
      const authStore = useAuthStore();
      authStore.clearAuth();
    }
  },

  async getUser() {
    const response = await apiClient.get('/me');
    
    // ? Update user in store
    const authStore = useAuthStore();
    authStore.user = response.data;
    localStorage.setItem('user', JSON.stringify(response.data));
    
    return response.data;
  },

  isAuthenticated() {
    const authStore = useAuthStore();
    return authStore.isAuthenticated;
  },

  getToken() {
    return localStorage.getItem('auth_token');
  },

  getCurrentUser() {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null
  }
}
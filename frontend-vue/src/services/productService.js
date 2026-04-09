import apiClient from '@/api/client'

export default {
  async getProducts(filters = {}) {
    const params = {};
    
    // Add search parameter
    if (filters.search) {
      params.search = filters.search;
    }
    
    // Add category parameter
    if (filters.category && filters.category !== 'all') {
      params.category = filters.category;
    }
    
    const response = await apiClient.get('/products', { params });
    return response.data.data;
  },

  async getProduct(id) {
    const response = await apiClient.get(`/products/${id}`);
    return response.data.data;
  },

  async getCategories() {
    const response = await apiClient.get('/categories');
    return response.data.data;
  }
}
import apiClient from '@/api/client'

export default {
  async getProducts(params = {}) {
    const response = await apiClient.get('/products', { params });
    return response.data.data;
  },
  async getProduct(id) {
    const response = await apiClient.get(`/products/${id}`);
    return response.data.data;
  },
  async searchProducts(search) {
    const response = await apiClient.get('/products', {
      params: { search }
    });
    return response.data.data;
  },
  async filterByCategory(categoryId) {
    const response = await apiClient.get('/products', {
      params: { category: categoryId }
    });
    return response.data.data;
  }
}
import apiClient from '@/api/client'

export default {
  async createOrder(items) {
    console.log("Made it to here ReS");
    const response = await apiClient.post('/orders', { items });
    return response.data;
  },

  async getOrders() {
    console.log("Get Orders");
    const response = await apiClient.get('/orders');
    return response.data;
  },

  async getOrder(id) {
    const response = await apiClient.get(`/orders/${id}`);
    return response.data;
  }
}
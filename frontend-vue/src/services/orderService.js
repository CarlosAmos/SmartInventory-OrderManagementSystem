import apiClient from '@/api/client'

export default {
  async createOrder(items) {
    console.log("Made it to here ReS");
    const response = await apiClient.post('/orders', { items });
    return response.data.data;
  },

  async getOrders() {    
    const response = await apiClient.get('/orders');
    console.log("Get Orders",response);
    return response.data.data;
  },

  async getOrder(id) {
    const response = await apiClient.get(`/orders/${id}`);
    return response.data.data;
  }
}
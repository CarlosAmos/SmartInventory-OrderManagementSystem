<script setup>
import { ref, onMounted } from 'vue';
import { useQuery } from '@tanstack/vue-query';
import orderService from '@/services/orderService';
import LoadingSpinner from '@/components/LoadingSpinner.vue';


const {
  data: orders,
  isLoading: loading,
  isError,
  error
} = useQuery({
  queryKey: ['orders'],
  queryFn: orderService.getOrders,
  staleTime: 30000, // Cache for 30 seconds
});


const getStatusBadgeClass = (status) => {
  switch (status) {
    case 'completed':
      return 'bg-green-400/30 text-green-600 border-green-600';
    case 'pending':
      return 'bg-yellow-400/30 text-yellow-600 border-yellow-600';
    case 'cancelled':
      return 'bg-red-400/30 text-red-600 border-red-600';
    default:
      return 'bg-gray-400/30 text-gray-600 border-gray-600';
  }
}

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  });
}

const getProductCount = (order) => {
  return order.products?.reduce((sum, product) => sum + product.quantity, 0) || 0;
}

onMounted(() => {
  //fetchOrders();
})
</script>

<template>
  <div class="px-40 py-20">

    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-2">My Orders</h1>
      <p class="text-gray-600">View all your previous orders</p>
    </div>


    <div v-if="error" class="text-center py-20">
      <p class="text-red-600">{{ error }}</p>
      <button class="mt-4 text-blue-500 hover:text-blue-600">
        Try Again
      </button>
    </div>

    <div v-else-if="orders.length >= 0" class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Order ID
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Date
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Items
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Total
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Actions
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-if="loading">
            <td colspan="6">
              <LoadingSpinner size="sm" message="Loading Orders" />
            </td>
          </tr>

          <tr v-for="order in orders" :key="order.id" class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-medium text-gray-900">
                #{{ order.id }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-600">
                {{ formatDate(order.created_at) }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm text-gray-600">
                {{ getProductCount(order) }} item(s)
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <div class="text-sm font-semibold text-gray-900">
                ${{ parseFloat(order.total).toFixed(2) }}
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span :class="[
                'inline-flex px-2 py-1 text-xs font-medium rounded border',
                getStatusBadgeClass(order.status)
              ]">
                {{ order.status.toUpperCase() }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm">
              <button class="text-gray-500 font-medium" disabled>
                View Details
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-else class="text-center py-20 bg-white rounded-lg shadow-sm border border-gray-200">
      <p class="text-gray-600 text-lg mb-4">No orders yet</p>
      <router-link to="/" class="text-blue-500 hover:text-blue-600 font-medium">
        Browse Products
      </router-link>
    </div>
  </div>
</template>
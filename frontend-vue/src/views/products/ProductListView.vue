<script setup>
import { ref, computed } from 'vue'
import { useQuery } from '@tanstack/vue-query'
import { useToast } from 'vue-toastification'
import productService from '@/services/productService'
import { useCartStore } from '@/stores/cartStore'
import  LoadingSpinner  from '@/components/LoadingSpinner.vue'
const toast = useToast()
const cartStore = useCartStore()
const searchQuery = ref('')

// Use TanStack Query for data fetching with auto-refetching
const { 
  data: allProducts, 
  isLoading: loading, 
  isError,
  error 
} = useQuery({
  queryKey: ['products'],
  queryFn: productService.getProducts,
  refetchInterval: 3000, // Auto-refetch every 3 seconds (real-time stock updates)
  staleTime: 1000, // Consider data stale after 1 second
})

// Filter products based on search query
const products = computed(() => {
  if (!allProducts.value) return []
  
  if (!searchQuery.value) {
    return allProducts.value
  }
  
  const query = searchQuery.value.toLowerCase()
  return allProducts.value.filter(product => 
    product.name.toLowerCase().includes(query) ||
    product.sku.toLowerCase().includes(query)
  )
})

const handleSearch = () => {
  // Search is reactive via computed property
  // No need to fetch again
}

const clearSearch = () => {
  searchQuery.value = ''
}

const isInStock = (product) => {
  return product.stock > 0
}

const getStockIndicatorClass = (product) => {
  if (product.stock === 0) {
    return 'bg-red-400/30 text-red-600 border-red-600'
  } else if (product.stock < 5) {
    return 'bg-orange-400/30 text-orange-600 border-orange-600'
  } else {
    return 'bg-green-400/30 text-green-600 border-green-600'
  }
}

const getStockText = (product) => {
  if (product.stock === 0) {
    return 'OUT OF STOCK'
  } else if (product.stock < 5) {
    return 'LOW STOCK'
  } else {
    return 'IN STOCK'
  }
}

const addToOrder = (product) => {
  if (!isInStock(product)) {
    toast.error('This product is out of stock')
    return
  }
  
  cartStore.addToCart(product, 1)
  toast.success(`${product.name} added to cart!`)
}
</script>

<template>
  <div class="px-40 py-20">
    <!-- Dashboard Header with Search -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900 mb-6">Product Dashboard</h1>
      
      <!-- Search Bar -->
      <div class="flex gap-4 max-w-2xl">
        <div class="flex-1 relative">
          <input
            v-model="searchQuery"
            @keyup.enter="handleSearch"
            type="text"
            placeholder="Search by product name or SKU..."
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
          >
          <button
            v-if="searchQuery"
            @click="clearSearch"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
          >
            ✕
          </button>
        </div>
        <button
          @click="handleSearch"
          class="px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors"
        >
          Search
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-20">
      <!-- <p class="text-gray-600">Loading products...</p> -->
      <LoadingSpinner message="Test" />
    </div>
    
    <!-- Error State -->
    <div v-else-if="isError" class="text-center py-20">
      <p class="text-red-600">{{ error?.message || 'Failed to load products' }}</p>
    </div>

    <!-- Products Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
      <div
        v-for="product in products"
        :key="product.id"
        class="flex flex-col items-start bg-white max-w-sm p-6 border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow"
      >
        <!-- Product Image -->
        <img 
          class="w-full h-48 object-cover rounded-md mb-4" 
          :src="product.image_url || '/images/placeholder.png'"
          :alt="product.name"
        >
        
        <!-- Real-time Stock Indicator (auto-updates via refetchInterval) -->
        <div 
          :class="[
            'border rounded-md px-2 py-[2px] border-default font-medium text-center text-xs mb-2 transition-all duration-300',
            getStockIndicatorClass(product)
          ]"
        >
          {{ getStockText(product) }}
        </div>

        <!-- Product Name -->
        <h5 class="mb-0 text-lg font-semibold text-gray-900 leading-8">
          {{ product.name }}
        </h5>
        
        <!-- SKU -->
        <p class="-mt-1 text-gray-600 mb-2 text-sm">
          SKU: {{ product.sku }}
        </p>

        <!-- Category -->
        <p class="text-sm text-gray-500 mb-4">
          {{ product.category?.name }}
        </p>

        <!-- Spacer -->
        <div class="flex-grow"></div>

        <!-- Price -->
        <p class="mt-auto mb-4 font-medium text-3xl text-gray-900">
          ${{ parseFloat(product.price).toFixed(2) }}
        </p>

        <!-- Add to Order Button -->
        <button 
          @click="addToOrder(product)"
          :disabled="!isInStock(product)"
          :class="[
            'w-full px-10 py-2 rounded-lg font-medium transition-colors',
            isInStock(product)
              ? 'bg-blue-500 hover:bg-blue-600 text-white'
              : 'bg-gray-300 text-gray-500 cursor-not-allowed'
          ]"
        >
          Add to Order
        </button>
      </div>
    </div>

    <!-- Empty State / No Results -->
    <div v-if="!loading && !isError && products.length === 0" class="text-center py-20">
      <p class="text-gray-600 text-lg mb-4">
        {{ searchQuery ? 'No products found matching your search' : 'No products available' }}
      </p>
      <button 
        v-if="searchQuery"
        @click="clearSearch"
        class="text-blue-500 hover:text-blue-600"
      >
        Clear search
      </button>
    </div>
  </div>
</template>
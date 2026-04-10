<script setup>
import { ref, computed } from 'vue';
import { useQuery } from '@tanstack/vue-query';
import { useToast } from 'vue-toastification';
import productService from '@/services/productService';
import { useCartStore } from '@/stores/cartStore';
import  LoadingSpinner  from '@/components/LoadingSpinner.vue';
const toast = useToast();
const cartStore = useCartStore();
const searchQuery = ref('');
const selectedCategory = ref('all');

// ? Use TanStack Query for data fetching with auto-refetching


// Fetch categories
const { data: categories } = useQuery({
  queryKey: ['categories'],
  queryFn: productService.getCategories,
  staleTime: 5 * 60 * 1000, // Cache for 5 minutes
});

console.log("Categories",categories);

// Fetch products with filters (reactive to changes)
const filters = computed(() => ({
  search: searchQuery.value,
  category: selectedCategory.value
}));

const { 
  data: products, 
  isLoading: loading, 
  isError,
  error,
  refetch
} = useQuery({
  queryKey: ['products', filters],
  queryFn: () => productService.getProducts(filters.value),
  refetchInterval: 3000, // Auto-refetch every 3 seconds
  staleTime: 1000,
})



const handleSearch = () => {
  refetch();
}

const clearSearch = () => {
  searchQuery.value = '';
}

const clearFilters = () => {
  searchQuery.value = ''
  selectedCategory.value = 'all'
}

const getStockQuantity = (product) => {
  return product.stock?.quantity ?? 0
}

const isInStock = (product) => {
  return getStockQuantity(product) > 0
}

const getStockIndicatorClass = (product) => {
  if (product.stock?.is_out_of_stock) {
    return 'bg-red-400/30 text-red-600 border-red-600';
  } else if (product.stock?.is_low_stock) {
    return 'bg-yellow-400/30 text-yellow-800 border-yellow-800';
  } else {
    return 'bg-green-400/30 text-green-600 border-green-600';
  }
}

const getStockText = (product) => {
  if (product.stock?.is_out_of_stock) {
    return 'OUT OF STOCK';
  } else if (product.stock?.is_low_stock) {
    return 'LOW STOCK';
  } else {
    return 'IN STOCK';
  }
}

const getCartQuantity = (product) => {
  return cartStore.items.find(i => i.id === product.id)?.quantity ?? 0
}

const activeToasts = new Set()

const addToOrder = (product) => {
  const available = getStockQuantity(product)
  const inCart = getCartQuantity(product)

  if (available === 0) {
    toast.error('This product is out of stock');
    return;
  }

  if (inCart >= available) {
    toast.error(`Only ${available} unit${available === 1 ? '' : 's'} available`);
    return;
  }

  cartStore.addToCart(product, 1);

  if (!activeToasts.has(product.id)) {
    activeToasts.add(product.id)
    toast.success(`${product.name} added to cart!`, {
      onClose: () => activeToasts.delete(product.id)
    })
  }
}
</script>

<template>
  <div class="py-1 md:py-10 sm:px-1 md:px-10 lg:px-20">
    <div class="mb-2 sm:mb-8">      
      <!-- Search Bar -->
      <div class="flex gap-4 w-full p-1 sm:p-0 flex-col md:flex-row items-end">
        <div class="flex-1 relative w-full">
          <input
            v-model="searchQuery"
            @keyup.enter="handleSearch"
            type="text"
            placeholder="Search by product name or SKU..."
            class="w-full md:w-[70%] px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
          >
          <button
            v-if="searchQuery"
            @click="clearSearch"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600"
          >
            ✕
          </button>
        </div>
          <div class="w-full md:w-64">
            <label>Category</label>
            <select
              v-model="selectedCategory"
              class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none bg-white"
            >
              <option value="all">All Categories</option>
              <option 
                v-for="category in categories" 
                :key="category.id" 
                :value="category.id"
              >
                {{ category.name }}
              </option>
          </select>
        </div>
      </div>
    </div>

    <div v-if="loading" class="text-center py-20">
      <!-- <p class="text-gray-600">Loading products...</p> -->
      <LoadingSpinner size="sm" message="Test" />
    </div>
    
    <div v-else-if="isError" class="text-center py-20">
      <p class="text-red-600">{{ error?.message || 'Failed to load products' }}</p>
    </div>

    <div v-else class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 justify-items-center">
      <div
        v-for="product in products"
        :key="product.id"
        class="flex flex-col items-start bg-white min-w-64 max-w-64 md:min-w-80 md:max-w-sm p-6 border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow"
      >
        <img 
          class="w-full h-48 object-cover rounded-md mb-4" 
          :src="product.image_url || '/images/placeholder.png'"
          :alt="product.name"
        >
        
        <div 
          :class="[
            'border rounded-md px-2 py-[2px] border-default font-bold text-center text-xs mb-2 transition-all duration-300',
            getStockIndicatorClass(product)
          ]"
        >
          {{ getStockText(product) }}
        </div>

        <h5 class="mb-0 text-lg font-semibold text-gray-900 leading-8">
          {{ product.name }}
        </h5>
        
        <p class="-mt-1 text-gray-600 mb-2 text-sm">
          SKU: {{ product.sku }}
        </p>

        <p class="text-sm text-gray-500 mb-4">
          {{ product.category?.name }}
        </p>

        <div class="flex-grow"></div>

        <p class="mt-auto mb-4 font-medium text-3xl text-gray-900">
          ${{ parseFloat(product.price).toFixed(2) }}
        </p>

        <button
          @click="addToOrder(product)"
          :disabled="!isInStock(product) || getCartQuantity(product) >= getStockQuantity(product)"
          :class="[
            'w-full px-10 py-2 rounded-lg font-medium transition-colors',
            !isInStock(product) || getCartQuantity(product) >= getStockQuantity(product)
              ? 'bg-gray-300 text-gray-500 cursor-not-allowed'
              : 'bg-blue-500 hover:bg-blue-600 text-white'
          ]"
        >
          <template v-if="getCartQuantity(product) >= getStockQuantity(product) && isInStock(product)">
            All Stock in Cart
          </template>
          <template v-else>
            Add to Order<span v-if="getCartQuantity(product) > 0"> ({{ getCartQuantity(product) }})</span>
          </template>
        </button>
      </div>
    </div>
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

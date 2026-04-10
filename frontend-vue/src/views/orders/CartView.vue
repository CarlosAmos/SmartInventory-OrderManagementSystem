<script setup>
import { computed, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useCartStore } from '@/stores/cartStore'
import { useMutation, useQueryClient } from '@tanstack/vue-query'
import { useToast } from 'vue-toastification'
import orderService from '@/services/orderService'
import OrderModal from '@/components/OrderModal.vue'

const router = useRouter()
const cartStore = useCartStore()
const toast = useToast()
const queryClient = useQueryClient()

// Modal state
const showModal = ref(false)
const modalState = ref('processing') // 'processing', 'success', 'error'
const modalErrorMessage = ref('')
const orderData = ref({})

const cartItems = computed(() => cartStore.items)
const itemCount = computed(() => cartStore.itemCount)
const subtotal = computed(() => cartStore.subtotal)
const gst = computed(() => cartStore.gst)
const total = computed(() => cartStore.total)

// Helper to get product ID (handles both flat and nested structures)
const getProductId = (item) => {
  return item.product?.id || item.id
}

// Helper to get product property (handles both structures)
const getProductProp = (item, prop) => {
  return item.product?.[prop] || item[prop]
}

const updateQuantity = (productId, newQuantity) => {
  if (newQuantity < 1) return
  
  const item = cartItems.value.find(i => getProductId(i) === productId)
  const stock = getProductProp(item, 'stock')
  
  if (item && newQuantity > stock) {
    toast.error(`Only ${stock} units available`)
    return
  }
  
  cartStore.updateQuantity(productId, newQuantity)
}

const removeFromCart = (productId) => {
  cartStore.removeFromCart(productId)
  toast.info('Item removed from cart')
}

const clearCart = () => {
  cartStore.clearCart()
  toast.info('Cart cleared')
}

// Create order mutation
const createOrderMutation = useMutation({
  mutationFn: orderService.createOrder,
  onSuccess: (data) => {
    console.log('Order success response:', data) // DEBUG: See actual structure
    
    queryClient.invalidateQueries({ queryKey: ['orders'] })
    queryClient.invalidateQueries({ queryKey: ['products'] })
    
    modalState.value = 'success'
    
    // Handle both possible response structures
    orderData.value = data.order || data.data || data
    
    cartStore.clearCart()
    
    setTimeout(() => {
      showModal.value = false
      router.push('/orders')
    }, 3000)
  },
  onError: (error) => {
    console.error('Order creation error:', error)
    
    let errorMsg = 'An unexpected error occurred. Please try again.'
    
    if (error.response?.data?.errors?.stock) {
      errorMsg = error.response.data.errors.stock[0]
    } else if (error.response?.data?.message) {
      errorMsg = error.response.data.message
    } else if (error.message) {
      errorMsg = error.message
    }
    
    modalState.value = 'error'
    modalErrorMessage.value = errorMsg
  }
})

const proceedToCheckout = async () => {
  if (itemCount.value === 0) {
    toast.error('Your cart is empty')
    return
  }

  // Show modal in processing state
  showModal.value = true
  modalState.value = 'processing'
  
  // Prepare order items (handles both flat and nested structures)
  const items = cartItems.value.map(item => ({
    product_id: getProductId(item),
    quantity: item.quantity
  }))

  // Trigger mutation
  createOrderMutation.mutate({ items })
}

const closeModal = () => {
  showModal.value = false
  
  // If error, don't redirect - let user try again
  if (modalState.value === 'error') {
    // Modal stays closed, user can modify cart
  }
}

console.log("Cart Items",cartItems);
</script>

<template>
  <div class="px-4 md:px-20 lg:px-40 py-10 md:py-20">
    <!-- Order Modal -->
    <OrderModal
      v-if="showModal"
      :state="modalState"
      :error-message="modalErrorMessage"
      :order-data="orderData"
      @close="closeModal"
    />

    <h1 class="text-3xl font-bold text-gray-900 mb-8">Shopping Cart</h1>

    <!-- Empty Cart State -->
    <div v-if="itemCount === 0" class="text-center py-20">
      <svg class="mx-auto h-24 w-24 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
      </svg>
      <h2 class="mt-4 text-xl font-semibold text-gray-900">Your cart is empty</h2>
      <p class="mt-2 text-gray-600">Start adding some products!</p>
      <router-link 
        to="/" 
        class="mt-6 inline-block px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors"
      >
        Browse Products
      </router-link>
    </div>

    <!-- Cart Items -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Cart Items List -->
      <div class="lg:col-span-2 space-y-4">
        <div
          v-for="item in cartItems"
          :key="getProductId(item)"
          class="flex gap-4 p-4 bg-white border border-gray-200 rounded-lg"
        >
          <!-- Product Image -->
          <img 
            :src="getProductProp(item, 'image_url') || '/images/placeholder.png'"
            :alt="getProductProp(item, 'name')"
            class="w-24 h-24 object-cover rounded"
          >

          <!-- Product Details -->
          <div class="flex-1">
            <h3 class="font-semibold text-gray-900">{{ getProductProp(item, 'name') }}</h3>
            <p class="text-sm text-gray-600">{{ getProductProp(item, 'category')?.name }}</p>
            <p class="text-sm text-gray-500">SKU: {{ getProductProp(item, 'sku') }}</p>
            <p class="text-lg font-medium text-gray-900 mt-2">
              ${{ parseFloat(getProductProp(item, 'price')).toFixed(2) }}
            </p>

            <!-- Quantity Controls -->
            <div class="flex items-center gap-3 mt-3">
              <button
                @click="updateQuantity(getProductId(item), item.quantity - 1)"
                :disabled="item.quantity <= 1"
                class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                -
              </button>
              <span class="w-12 text-center font-medium">{{ item.quantity }}</span>
              <button
                @click="updateQuantity(getProductId(item), item.quantity + 1)"
                :disabled="item.quantity >= getProductProp(item, 'stock')"
                class="px-3 py-1 border border-gray-300 rounded hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
              >
                +
              </button>
              <span class="text-sm text-gray-500">
                ({{ getProductProp(item, 'stock') }} available)
              </span>
            </div>
          </div>

          <!-- Remove Button -->
          <button
            @click="removeFromCart(getProductId(item))"
            class="text-red-500 hover:text-red-700 self-start transition-colors"
            title="Remove from cart"
          >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <!-- Clear Cart Button -->
        <button
          @click="clearCart"
          class="text-red-500 hover:text-red-700 text-sm font-medium transition-colors"
        >
          Clear entire cart
        </button>
      </div>

      <!-- Order Summary -->
      <div class="lg:col-span-1">
        <div class="bg-white border border-gray-200 rounded-lg p-6 sticky top-4">
          <h2 class="text-xl font-semibold text-gray-900 mb-4">Order Summary</h2>
          
          <div class="space-y-2 mb-4">
            <div class="flex justify-between text-gray-600">
              <span>Subtotal ({{ itemCount }} {{ itemCount === 1 ? 'item' : 'items' }})</span>
              <span>${{ parseFloat(subtotal).toFixed(2) }}</span>
            </div>
            <div class="flex justify-between text-gray-600">
              <span>GST (10%)</span>
              <span>${{ parseFloat(gst).toFixed(2) }}</span>
            </div>
          </div>

          <div class="border-t border-gray-200 pt-4 mb-6">
            <div class="flex justify-between text-lg font-semibold text-gray-900">
              <span>Total</span>
              <span>${{ parseFloat(total).toFixed(2) }}</span>
            </div>
          </div>

          <button
            @click="proceedToCheckout"
            :disabled="createOrderMutation.isPending.value || itemCount === 0"
            class="w-full px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {{ createOrderMutation.isPending.value ? 'Processing...' : 'Confirm Order' }}
          </button>

          <router-link
            to="/"
            class="block text-center mt-4 text-blue-500 hover:text-blue-600 transition-colors"
          >
            Continue Shopping
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>
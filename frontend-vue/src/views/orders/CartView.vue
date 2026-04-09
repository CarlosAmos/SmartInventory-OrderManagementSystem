<script setup>
import { ref, computed } from 'vue'
import OrderSummary from './OrderSummary.vue'

// Hard-coded cart items for now
const cartItems = ref([
  { id: 1, name: 'Product 1', price: 299.99, quantity: 1 },
  { id: 2, name: 'Product 2', price: 299.99, quantity: 1 }
])

// When you change quantity, everything auto-updates!
const updateQuantity = (itemId, newQuantity) => {
  const item = cartItems.value.find(i => i.id === itemId)
  if (item) {
    item.quantity = newQuantity
  }
  // That's it! The summary will automatically recalculate
}

const removeItem = (itemId) => {
  cartItems.value = cartItems.value.filter(i => i.id !== itemId)
}
</script>

<template>
  <div class="container mx-auto p-4">
    <!-- Cart Items -->
    <div class="space-y-4 mb-8">
      <div 
        v-for="item in cartItems" 
        :key="item.id"
        class="flex items-center justify-between bg-white p-4 rounded-lg border"
      >
        <div>
          <h3 class="font-semibold">{{ item.name }}</h3>
          <p class="text-gray-600">${{ item.price.toFixed(2) }}</p>
        </div>
        
        <div class="flex items-center gap-4">
          <!-- Quantity controls -->
          <div class="flex items-center gap-2">
            <button 
              @click="updateQuantity(item.id, item.quantity - 1)"
              :disabled="item.quantity <= 1"
              class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300 disabled:opacity-50"
            >
              -
            </button>
            <span class="w-8 text-center">{{ item.quantity }}</span>
            <button 
              @click="updateQuantity(item.id, item.quantity + 1)"
              class="px-3 py-1 bg-gray-200 rounded hover:bg-gray-300"
            >
              +
            </button>
          </div>
          
          <button 
            @click="removeItem(item.id)"
            class="text-red-500 hover:text-red-700"
          >
            Remove
          </button>
        </div>
      </div>
    </div>
    
    <!-- Order Summary - automatically updates! -->
    <OrderSummary 
      :items="cartItems" 
      @confirm="handleCheckout"
    />
  </div>
</template>

<script>
const handleCheckout = () => {
  console.log('Checkout clicked!', cartItems.value)
  // Later: API call to backend
}
</script>
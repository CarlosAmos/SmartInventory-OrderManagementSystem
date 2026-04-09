<script setup>
import { computed } from 'vue'

const props = defineProps({
  items: {
    type: Array,
    required: true
  }
})

// These automatically recalculate when items change!
const productPrice = computed(() => {
  return props.items.reduce((sum, item) => sum + (item.price * item.quantity), 0)
})

const subtotal = computed(() => productPrice.value)
const gst = computed(() => subtotal.value * 0.1)
const total = computed(() => subtotal.value + gst.value)
</script>

<template>
  <!-- Same template as before -->
  <div class="max-w-sm bg-white rounded-lg border border-gray-300 shadow-sm p-6">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Summary</h2>
    
    <div class="space-y-3 mb-4">
      <div class="flex justify-between text-gray-700">
        <span>Product Price</span>
        <span>${{ productPrice.toFixed(2) }}</span>
      </div>
      <div class="flex justify-between text-gray-700">
        <span>Subtotal</span>
        <span>${{ subtotal.toFixed(2) }}</span>
      </div>
      <div class="flex justify-between text-gray-700">
        <span>GST</span>
        <span>${{ gst.toFixed(2) }}</span>
      </div>
    </div>
    
    <hr class="border-gray-300 my-4">
    
    <div class="flex justify-between text-lg font-semibold text-gray-900 mb-6">
      <span>Total</span>
      <span>${{ total.toFixed(2) }}</span>
    </div>
    
    <button 
      @click="$emit('confirm')"
      class="w-full bg-blue-500 hover:bg-blue-600 text-white font-medium py-3 px-4 rounded-lg"
    >
      Confirm Order
    </button>
  </div>
</template>
import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useCartStore = defineStore('cart', () => {
  const items = ref([]);
  const storageKey = ref('cart');

  const loadForUser = (userId) => {
    storageKey.value = `cart_${userId}`
    const stored = localStorage.getItem(storageKey.value)
    if (stored) {
      try {
        items.value = JSON.parse(stored)
      } catch (e) {
        console.error('Failed to parse cart from localStorage:', e)
        items.value = []
      }
    } else {
      items.value = []
    }
  }

  const saveToLocalStorage = () => {
    localStorage.setItem(storageKey.value, JSON.stringify(items.value));
  }

  const itemCount = computed(() => {
    return items.value.reduce((count, item) => count + item.quantity, 0);
  })

  const subtotal = computed(() => {
    return items.value.reduce((sum, item) => {
      return sum + (Number(item.price) * item.quantity);
    }, 0);
  })

  const gst = computed(() => {
    return subtotal.value * 0.10;
  })

  const total = computed(() => {
    return subtotal.value + gst.value;
  })

  const addToCart = (product, quantity = 1) => {
    const existingItem = items.value.find(item => item.id === product.id);

    if (existingItem) {
      existingItem.quantity += quantity;
    } else {
      items.value.push({
        ...product,
        quantity
      });
    }

    saveToLocalStorage()
  }

  const removeFromCart = (productId) => {
    const index = items.value.findIndex(item => item.id === productId);
    if (index > -1) {
      items.value.splice(index, 1);
      saveToLocalStorage();
    }
  }

  const updateQuantity = (productId, quantity) => {
    const item = items.value.find(item => item.id === productId);
    if (item) {
      item.quantity = quantity;
      saveToLocalStorage();
    }
  }

  const clearCart = () => {
    items.value = [];
    saveToLocalStorage();
  }

  const resetCart = () => {
    items.value = [];
    storageKey.value = 'cart';
  }

  return {
    items,
    itemCount,
    subtotal,
    gst,
    total,
    loadForUser,
    addToCart,
    removeFromCart,
    updateQuantity,
    clearCart,
    resetCart,
  }
})

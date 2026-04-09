import { defineStore } from 'pinia';
import { ref, computed } from 'vue';

export const useCartStore = defineStore('cart', () => {

  const items = ref([]);

  const loadCart = () => {
    const savedCart = localStorage.getItem('cart');
    if (savedCart) {
      items.value = JSON.parse(savedCart);
    }
  }

  const saveCart = () => {
    localStorage.setItem('cart', JSON.stringify(items.value));
  }

  const itemCount = computed(() => {
    return items.value.reduce((total, item) => total + item.quantity, 0)
  })

  const cartTotal = computed(() => {
    return items.value.reduce((total, item) => {
      return total + (item.price * item.quantity)
    }, 0)
  })

  const addToCart = (product, quantity = 1) => {
    const existingItem = items.value.find(item => item.id === product.id)

    if (existingItem) {
      existingItem.quantity += quantity
    } else {
      items.value.push({
        id: product.id,
        name: product.name,
        sku: product.sku,
        price: product.price,
        quantity: quantity,
        stock: product.stock,
        image_url: product.image_url
      })
    }

    saveCart()
  }

  const removeFromCart = (productId) => {
    items.value = items.value.filter(item => item.id !== productId);
    saveCart();
  }

  const updateQuantity = (productId, quantity) => {
    const item = items.value.find(item => item.id === productId);
    if (item) {
      item.quantity = quantity;
      if (item.quantity <= 0) {
        removeFromCart(productId);
      } else {
        saveCart();
      }
    }
  }

  const clearCart = () => {
    items.value = [];
    localStorage.removeItem('cart');
  }

  loadCart();

  return {
    items,
    itemCount,
    cartTotal,
    addToCart,
    removeFromCart,
    updateQuantity,
    clearCart
  }
})
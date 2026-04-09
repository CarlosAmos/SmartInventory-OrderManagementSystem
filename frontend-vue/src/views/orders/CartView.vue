<script setup>
import { useRouter } from 'vue-router';
import { useToast } from 'vue-toastification';
import { useCartStore } from '@/stores/cartStore';
import orderService from '@/services/orderService';
import productService from '@/services/productService';
import { computed, nextTick, ref } from 'vue';

const router = useRouter();
const toast = useToast();
const cartStore = useCartStore();
const activeToastId = ref(null);
const errorInputs = ref(new Set());
const isProcessing = ref(false);

const gst = computed(() => cartStore.cartTotal * 0.1);
const total = computed(() => cartStore.cartTotal + gst.value);

const getStockBadgeClass = (item) => {
  if (item.stock === 0) {
    return 'bg-red-400/30 text-red-600 border-red-600';
  } else if (item.stock < 5) {
    return 'bg-orange-400/30 text-orange-600 border-orange-600';
  } else {
    return 'bg-green-400/30 text-green-600 border-green-600';
  }
}

const getStockText = (item) => {
  if (item.stock === 0) {
    return 'OUT OF STOCK';
  } else if (item.stock < 5) {
    return 'LOW STOCK';
  } else {
    return 'IN STOCK';
  }
}

const showStockLimitToast = (stock) => {
  if (activeToastId.value) {
    return;
  }

  activeToastId.value = toast.error(`Only ${stock} items available in stock. Quantity set to maximum.`, {
    onClose: () => {
      activeToastId.value = null;
    }
  })
}

const flashInputError = (itemId) => {
  errorInputs.value.add(itemId);
  
  setTimeout(() => {
    errorInputs.value.delete(itemId);
  }, 2000);
}

const isInputError = (itemId) => {
  return errorInputs.value.has(itemId);
}

const updateQuantity = (item, newQuantity, event) => {
  const quantity = parseInt(newQuantity);
  
  if (isNaN(quantity) || quantity < 1) {
    cartStore.updateQuantity(item.id, 1);
    if (event && event.target) {
      nextTick(() => {
        event.target.value = 1;
      })
    }
    return;
  }
  
  if (quantity > item.stock) {
    cartStore.updateQuantity(item.id, item.stock)
    showStockLimitToast(item.stock);
    flashInputError(item.id);
    if (event && event.target) {
      nextTick(() => {
        event.target.value = item.stock;
      })
    }
    return;
  }
  
  cartStore.updateQuantity(item.id, quantity);
}

const incrementQuantity = (item) => {
  updateQuantity(item, item.quantity + 1, null);
}

const decrementQuantity = (item) => {
  updateQuantity(item, item.quantity - 1, null);
}

const removeItem = (item) => {
  cartStore.removeFromCart(item.id);
  toast.success(`${item.name} removed from cart`);
}

const proceedToCheckout = async () => {
  if (cartStore.items.length === 0) {
    toast.error('Your cart is empty');
    return;
  }

  if (isProcessing.value) {
    return;
  }

  isProcessing.value = true;

  try {
    const productIds = cartStore.items.map(item => item.id);
          
    const currentProducts = await productService.getProducts();
      
    for (const cartItem of cartStore.items) {
      const currentProduct = currentProducts.find(p => p.id === cartItem.id);
      
      if (!currentProduct) {
        toast.error(`${cartItem.name} is no longer available`);
        cartStore.removeFromCart(cartItem.id);
        isProcessing.value = false;
        return
      }
      
      if (currentProduct.stock < cartItem.quantity) {
        toast.error(`Insufficient stock for ${cartItem.name}. Only ${currentProduct.stock} available.`);
        cartItem.stock = currentProduct.stock;
        if (currentProduct.stock > 0) {
          cartStore.updateQuantity(cartItem.id, currentProduct.stock);
        } else {
          cartStore.removeFromCart(cartItem.id);
        }
        isProcessing.value = false;
        return
      }
    }

    const items = cartStore.items.map(item => ({
      product_id: item.id,
      quantity: item.quantity
    }));
        
    const response = await orderService.createOrder(items);
    console.log(response);
    
    toast.success('Order completed successfully!');
    
    setTimeout(() => {
      cartStore.clearCart();
      router.push('/orders');
    }, 300);

  } catch (error) {
    console.error('Order creation error:', error)

    if (error.response?.status === 422) {
      const errors = error.response.data.errors

      if (errors.stock) {
        toast.error(errors.stock[0])
      } else if (errors.product) {
        toast.error(errors.product[0])
      } else {
        toast.error('Please check your cart and try again')
      }
    } else if (error.response?.status === 401) {
      toast.error('Please login to place an order')
      router.push('/login')
    } else {
      toast.error('Failed to create order. Please try again.')
    }
  } finally {
    isProcessing.value = false
  }
}
</script>

<template>
  <div class="px-40 py-20">
    <!-- Empty Cart State -->
    <div v-if="cartStore.items.length === 0" class="text-center py-20 bg-white rounded-lg shadow-sm border border-gray-200">
      <p class="text-gray-600 text-lg mb-4">Your cart is empty</p>
      <router-link 
        to="/"
        class="inline-block px-6 py-3 bg-blue-500 hover:bg-blue-600 text-white font-medium rounded-lg transition-colors"
      >
        Browse Products
      </router-link>
    </div>

    <!-- Cart with Items -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Cart Items Table (Left 2/3) -->
      <div class="lg:col-span-2">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
          <!-- Table Header -->
          <div class="grid grid-cols-12 gap-4 px-6 py-4 bg-gray-50 border-b border-gray-200 font-semibold text-sm text-gray-700 uppercase">
            <div class="col-span-2">IMAGE</div>
            <div class="col-span-3">PRODUCT</div>
            <div class="col-span-3">QUANTITY</div>
            <div class="col-span-2 text-right">PRICE</div>
            <div class="col-span-2 text-right">ACTION</div>
          </div>

          <!-- Cart Items -->
          <div class="divide-y divide-gray-200">
            <div
              v-for="item in cartStore.items"
              :key="item.id"
              class="grid grid-cols-12 gap-4 px-6 py-6 items-center"
            >
              <!-- Image -->
              <div class="col-span-2">
                <img 
                  :src="item.image_url || '/images/placeholder.png'" 
                  :alt="item.name"
                  class="w-full h-24 object-cover rounded-md bg-gray-100"
                >
              </div>

              <!-- Product Info -->
              <div class="col-span-3">
                <div 
                  :class="[
                    'inline-block border rounded-md px-2 py-[2px] font-medium text-xs mb-2',
                    getStockBadgeClass(item)
                  ]"
                >
                  {{ getStockText(item) }}
                </div>
                <h3 class="font-semibold text-gray-900 mb-1">{{ item.name }}</h3>
                <p class="text-sm text-gray-600">SKU: {{ item.sku }}</p>
              </div>

              <!-- Quantity Controls -->
              <div class="col-span-3">
                <div class="flex items-center gap-2">
                  <input 
                    type="number" 
                    :value="item.quantity"
                    @input="updateQuantity(item, $event.target.value, $event)"
                    @blur="updateQuantity(item, $event.target.value, $event)"
                    :class="[
                      'w-16 text-center rounded-md py-2 focus:outline-none transition-all duration-300 h-7',
                      isInputError(item.id)
                        ? 'border-2 border-red-500 focus:ring-2 focus:ring-red-500'
                        : 'border border-gray-300 focus:ring-2 focus:ring-blue-500'
                    ]"
                    min="1"
                    :max="item.stock"
                  >
                  <div class="flex flex-row">
                    <button
                      @click="decrementQuantity(item)"
                      :disabled="item.quantity <= 1"
                      class="px-2 py-1 border border-gray-300 rounded-b border-t-0 hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed text-xs"
                    >
                      −
                    </button>
                    <button
                      @click="incrementQuantity(item)"
                      :disabled="item.quantity >= item.stock"
                      class="px-2 py-1 border border-gray-300 rounded-t hover:bg-gray-100 disabled:opacity-50 disabled:cursor-not-allowed text-xs"
                    >
                      +
                    </button>
                  </div>
                </div>
              </div>

              <!-- Price -->
              <div class="col-span-2 text-right">
                <p class="font-semibold text-gray-900 text-lg">
                  ${{ parseFloat(item.price).toFixed(2) }}
                </p>
              </div>

              <!-- Remove Button -->
              <div class="col-span-2 text-right">
                <button
                  @click="removeItem(item)"
                  class="text-red-500 hover:text-red-700 font-medium text-sm transition-colors"
                >
                  Remove
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Order Summary (Right 1/3) -->
      <div class="lg:col-span-1">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 sticky top-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-6">Summary</h2>

          <div class="space-y-4 mb-6">
            <div class="flex justify-between text-gray-700">
              <span>Product Price</span>
              <span>${{ cartStore.cartTotal.toFixed(2) }}</span>
            </div>
            <div class="flex justify-between text-gray-700">
              <span>Subtotal</span>
              <span>${{ cartStore.cartTotal.toFixed(2) }}</span>
            </div>
            <div class="flex justify-between text-gray-700">
              <span>GST</span>
              <span>${{ gst.toFixed(2) }}</span>
            </div>
          </div>

          <hr class="border-gray-300 my-6">

          <div class="flex justify-between text-lg font-semibold text-gray-900 mb-6">
            <span>Total</span>
            <span>${{ total.toFixed(2) }}</span>
          </div>

          <button
            @click="proceedToCheckout"
            :disabled="isProcessing"
            :class="[
              'w-full font-semibold py-3 px-4 rounded-lg transition-colors',
              isProcessing
                ? 'bg-gray-400 cursor-not-allowed text-white'
                : 'bg-blue-500 hover:bg-blue-600 text-white'
            ]"
          >
            {{ isProcessing ? 'Processing...' : 'Confirm Order' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
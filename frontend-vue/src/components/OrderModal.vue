<script setup>
import { computed, watch, onMounted } from 'vue'
import LoadingSpinner from '@/components/LoadingSpinner.vue'

const props = defineProps({
  state: {
    type: String,
    required: true,
    validator: (value) => ['processing', 'success', 'error'].includes(value)
  },
  errorMessage: {
    type: String,
    default: 'An error occurred while processing your order'
  },
  orderData: {
    type: Object,
    default: () => ({})
  }
})

const emit = defineEmits(['close'])

// Auto-close on success after 3 seconds
watch(() => props.state, (newState) => {
  if (newState === 'success') {
    setTimeout(() => {
      emit('close')
    }, 3000)
  }
})

const closeModal = () => {
  if (props.state === 'processing' || props.state === 'success') return
  emit('close')
}
</script>

<template>
  <div class="modal-overlay" @click.self="closeModal">
    <div class="modal-container">
      <button
        v-if="state === 'error'"
        @click="closeModal"
        class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 transition-colors"
        aria-label="Close"
      >
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
      </button>

      <div v-if="state === 'processing'" class="flex flex-col items-center">
        <LoadingSpinner size="md" class="mt-5" />
        <div class="flex justify-center mt-5 font-bold text-3xl">
          <p>Processing Order...</p>
        </div>
        <p class="text-sm text-gray-500 mt-2">Please wait...</p>
      </div>

      <div v-else-if="state === 'success'" class="flex flex-col items-center mt-5">
        <div class="success-animation">
          <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52">
            <circle class="checkmark__circle" cx="26" cy="26" r="25" fill="none" />
            <path class="checkmark__check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8" />
          </svg>
        </div>
        <div class="flex justify-center mt-5 font-bold text-3xl">
          <p>Order Confirmed!</p>
        </div>
        <div v-if="orderData.id" class="text-center mt-3">
          <p class="text-sm text-gray-600">Order #{{ orderData.id }}</p>
          <p class="text-sm text-gray-600">Total: ${{ parseFloat(orderData.total).toFixed(2) }}</p>
        </div>
        <p class="text-xs text-gray-400 mt-3">Redirecting to orders...</p>
      </div>

      <div v-else-if="state === 'error'" class="flex flex-col items-center mt-5">
        <div class="circle-border"></div>
        <div class="circle">
          <div class="error"></div>
        </div>
        <div class="flex justify-center mt-5 font-bold text-3xl">
          <p>Order Failed</p>
        </div>
        <div class="text-center mt-3 px-6">
          <p class="text-sm text-gray-600">{{ errorMessage }}</p>
        </div>
        <button
          @click="closeModal"
          class="mt-6 px-6 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg transition-colors"
        >
          Close
        </button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.modal-overlay {
  height: 100vh;
  width: 100%;
  background-color: rgba(0, 0, 0, 0.582);
  position: fixed;
  top: 0;
  left: 0;
  z-index: 50;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
}

.modal-container {
  position: relative;
  min-height: 250px;
  width: 400px;
  max-width: 90vw;
  background-color: white;
  border-radius: 10px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  padding: 20px;
}

.success-animation {}

.checkmark {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  display: block;
  stroke-width: 2;
  stroke: #4bb71b;
  stroke-miterlimit: 10;
  box-shadow: inset 0px 0px 0px #4bb71b;
  animation: fill 0.4s ease-in-out 0.4s forwards, scale 0.3s ease-in-out 0.9s both;
  position: relative;
  top: 5px;
  margin: 0 auto;
}

.checkmark__circle {
  stroke-dasharray: 166;
  stroke-dashoffset: 166;
  stroke-width: 2;
  stroke-miterlimit: 10;
  stroke: #4bb71b;
  fill: #fff;
  animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.checkmark__check {
  transform-origin: 50% 50%;
  stroke-dasharray: 48;
  stroke-dashoffset: 48;
  animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
}

@keyframes stroke {
  100% {
    stroke-dashoffset: 0;
  }
}

@keyframes scale {
  0%,
  100% {
    transform: none;
  }

  50% {
    transform: scale3d(1.1, 1.1, 1);
  }
}

@keyframes fill {
  100% {
    box-shadow: inset 0px 0px 0px 30px #4bb71b;
  }
}

.circle,
.circle-border {
  width: 80px;
  height: 80px;
  border-radius: 50%;
}

.circle {
  z-index: 1;
  position: relative;
  background: white;
  transform: scale(1);
  animation: success-anim 700ms ease;
}

.circle-border {
  z-index: 0;
  position: absolute;
  transform: scale(1.1);
  animation: circle-anim 400ms ease;
  background: #f86;
}

@keyframes success-anim {
  0% {
    transform: scale(0);
  }

  30% {
    transform: scale(0);
  }

  100% {
    transform: scale(1);
  }
}

@keyframes circle-anim {
  from {
    transform: scale(0);
  }

  to {
    transform: scale(1.1);
  }
}

.error::before,
.error::after {
  content: "";
  display: block;
  height: 4px;
  background: #f86;
  position: absolute;
}

.error::before {
  width: 55px;
  top: 48%;
  left: 16%;
  transform: rotateZ(50deg);
}

.error::after {
  width: 55px;
  top: 48%;
  left: 16%;
  transform: rotateZ(-50deg);
}
</style>
<script setup>
import { computed } from 'vue';

const props = defineProps({
  size: {
    type: String,
    default: 'sm',
    validator: (value) => ['sm', 'md'].includes(value)
  }
});

const sizeClasses = computed(() => {
  const sizes = {
    'sm': 'w-16 h-16',
    'md': 'w-24 h-24',
  }
  return sizes[props.size]
});

</script>

<template>
  <div class="flex items-center justify-center">
    <div
      :class="[
        'animate-spin rounded-full border-b-5 border-4 border-gray-500 border-b-gray-800',
        sizeClasses
      ]"
    ></div>
  </div>
</template>



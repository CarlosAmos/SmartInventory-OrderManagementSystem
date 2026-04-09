<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import authService from '@/services/authService';

const router = useRouter();
const showPassword = ref(false);
const username = ref('');
const password = ref('');

// Error states
const loginError = ref(false);
const usernameError = ref('');
const passwordError = ref('');

// Debug: Check if component is mounting/unmounting
onMounted(() => {
  console.log('LoginPage MOUNTED');
});

onBeforeUnmount(() => {
  console.log('LoginPage UNMOUNTING');
});

const clearErrors = () => {
  loginError.value = false;
  usernameError.value = '';
  passwordError.value = '';
}

const handleLogin = async () => {
  console.log('handleLogin called!');
  clearErrors();
  
  try {
    await authService.login(username.value, password.value);
    console.log('Login successful!');
    router.push('/');
  } catch (error) {
    console.error('Login error:', error);
    
    localStorage.removeItem('auth_token');
    localStorage.removeItem('user');
    
    if (error.response?.status === 401) {
      loginError.value = true;
      usernameError.value = ' ';
      passwordError.value = ' ';
      console.log('401 error - setting loginError to true');
      console.log('loginError.value is now:', loginError.value);
      
      // Debug: Check after a delay
      setTimeout(() => {
        console.log('After 1 second, loginError is:', loginError.value);
      }, 1000);
    } else if (error.response?.status === 422) {
      const errors = error.response.data.errors;
      if (errors.email) usernameError.value = errors.email[0];
      if (errors.password) passwordError.value = errors.password[0];
      console.log('422 error - validation failed');
    } else {
      loginError.value = true;
      console.log('Other error:', error.response?.status);
    }
  }
}
</script>

<template>
  <div class="relative min-h-screen">
    <!-- Background Image -->
    <img 
      src="https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=1920&q=80" 
      alt="Mountain background"
      class="absolute inset-0 w-full h-full object-cover"
    >
    
    <div class="absolute inset-0 bg-black/10"></div>
    
    <!-- Login Card -->
    <div class="relative z-10 min-h-screen flex items-center justify-center p-4">
      <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">
        <h2 class="text-2xl font-semibold text-gray-900 mb-8">
          Please enter your login details
        </h2>
        
        <!-- General Error Message -->
        <div 
          v-if="loginError"
          class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4 flex items-start gap-3"
        >
          <div class="flex-shrink-0 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center mt-0.5">
            <span class="text-white text-xs font-bold">!</span>
          </div>
          <p class="text-sm text-gray-800">
            The username or password you entered is incorrect
          </p>
        </div>
        
        <form @submit.prevent="handleLogin" class="space-y-6">
          <!-- Username Field -->
          <div>
            <label for="username" class="block text-sm font-semibold text-gray-900 mb-2">
              Username
            </label>
            <div class="relative">
              <input 
                id="username"
                v-model="username"
                type="text" 
                placeholder="name@example.com"
                :class="[
                  'w-full px-4 py-3 rounded-lg outline-none transition',
                  usernameError 
                    ? 'border-2 border-red-500 focus:ring-2 focus:ring-red-500' 
                    : 'border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent'
                ]"
                @input="clearErrors"
                required
              >
              <div 
                v-if="usernameError"
                class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 bg-red-500 rounded-full flex items-center justify-center"
              >
                <span class="text-white text-xs font-bold">!</span>
              </div>
            </div>
          </div>
          
          <!-- Password Field -->
          <div>
            <label for="password" class="block text-sm font-semibold text-gray-900 mb-2">
              Password
            </label>
            <div class="relative">
              <input 
                id="password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                placeholder="Password"
                :class="[
                  'w-full px-4 py-3 rounded-lg outline-none transition pr-24',
                  passwordError 
                    ? 'border-2 border-red-500 focus:ring-2 focus:ring-red-500' 
                    : 'border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent'
                ]"
                @input="clearErrors"
                required
              >
              <div class="absolute right-3 top-1/2 -translate-y-1/2 flex items-center gap-2">
                <div 
                  v-if="passwordError"
                  class="w-5 h-5 bg-red-500 rounded-full flex items-center justify-center"
                >
                  <span class="text-white text-xs font-bold">!</span>
                </div>
                <button 
                  type="button"
                  @click="showPassword = !showPassword"
                  class="text-sm text-gray-600 hover:text-gray-900 font-medium"
                >
                  {{ showPassword ? 'Hide' : 'Show' }}
                </button>
              </div>
            </div>
          </div>
          
          <!-- Forgot Password Link -->
          <div>
            <a href="#" @click.prevent class="text-blue-500 hover:text-blue-600 text-sm font-medium">
              Forgot password?
            </a>
          </div>
          
          <!-- Login Button -->
          <button 
            type="submit"
            class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-4 rounded-lg transition-colors"
          >
            Login
          </button>
        </form>
      </div>
    </div>
  </div>
</template>

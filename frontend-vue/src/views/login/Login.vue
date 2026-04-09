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
            <p v-if="usernameError" class="mt-2 text-sm text-red-600">
              {{ usernameError }}
            </p>
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
            <p v-if="passwordError" class="mt-2 text-sm text-red-600">
              {{ passwordError }}
            </p>
          </div>
          
          <!-- Forgot Password Link -->
          <div>
            <a href="#" class="text-blue-500 hover:text-blue-600 text-sm font-medium">
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

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const showPassword = ref(false)
const username = ref('')
const password = ref('')

// Error states
const loginError = ref(false)
const usernameError = ref('')
const passwordError = ref('')

// Hardcoded credentials for now
const VALID_USERNAME = 'admin@admin.com'
const VALID_PASSWORD = 'admin'

const clearErrors = () => {
  loginError.value = false
  usernameError.value = ''
  passwordError.value = ''
}

const validateEmail = (email) => {
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
  return emailRegex.test(email)
}

const handleLogin = async () => {
  clearErrors()
  
  // Validate email format
  if (!validateEmail(username.value)) {
    usernameError.value = 'Please enter a valid email address'
    return
  }
  
  // Check credentials
  if (username.value !== VALID_USERNAME || password.value !== VALID_PASSWORD) {
    loginError.value = true
    usernameError.value = ' ' // Just to trigger red border
    passwordError.value = ' ' // Just to trigger red border
    return
  }
  
  // Success - navigate to home
  console.log('Login successful!')
  router.push('/products')
  
  // Later: Replace with actual API call
  // try {
  //   const response = await axios.post('/api/login', {
  //     email: username.value,
  //     password: password.value
  //   })
  //   localStorage.setItem('token', response.data.token)
  //   router.push('/')
  // } catch (error) {
  //   if (error.response?.status === 422) {
  //     // Validation errors
  //     const errors = error.response.data.errors
  //     if (errors.email) usernameError.value = errors.email[0]
  //     if (errors.password) passwordError.value = errors.password[0]
  //   } else if (error.response?.status === 401) {
  //     // Invalid credentials
  //     loginError.value = true
  //     usernameError.value = ' '
  //     passwordError.value = ' '
  //   }
  // }
}
</script>
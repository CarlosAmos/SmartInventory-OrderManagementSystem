<script setup>
import { useRouter } from 'vue-router';
import authService from '@/services/authService';
import { useCartStore } from '@/stores/cartStore';
import { useAuthStore } from '@/stores/authStore';

const router = useRouter();
const cartStore = useCartStore();
const authStore = useAuthStore();

const handleLogout = async () => {
  await authService.logout();
  router.push('/login');
}
</script>

<template>
    <nav class="w-full" style="text-wrap: wrap;">
        <div class="mx-auto px-4 ">
            <div class="text-white flex items-center justify-between h-12 text-sm sm:text-lg">
                <!-- ? Left side navigation -->
                <div class="flex items-center space-x-6 ">
                    <RouterLink class="hover:text-gray-400 font-medium transition-colors" to="/products">Product List</RouterLink>
                    <RouterLink class="hover:text-gray-400 font-medium transition-colors" to="/orders">My Orders</RouterLink>
                </div>

                <!-- ? Right side navigation -->
                <div class="flex items-center">
                    <RouterLink class="hover:text-gray-400 font-medium transition-colors pr-3" to="/cart">Cart ({{ cartStore.itemCount }})</RouterLink>
                    <button @click="handleLogout" class="text-gray-400 hover:text-white pl-3" style="border-left:1px solid grey" to="/login">Sign Out</button>
                </div>
            </div>
        </div>
    </nav>
</template>

<style scope>
nav {
    font-size: 12px;
    text-align: center;
    font-size: 1rem;
    padding: 1rem 0;
}

nav a {
    display: inline-block;
}

nav a :hover {
    color: green;
}

nav a.router-link-exact-active {
    color: #0f8dfd;
}

nav a.router-link-exact-active:hover {
    background-color: transparent;
}



nav a:first-of-type {
    border: 0;
}


</style>
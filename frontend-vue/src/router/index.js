import { createRouter, createWebHistory } from 'vue-router';
import { useAuthStore } from '@/stores/authStore';
import ProductListView from '../views/products/ProductListView.vue';
import OrdersView from '../views/orders/OrdersView.vue';
import CartView from '../views/orders/CartView.vue';
import LoginView from '../views/login/Login.vue';

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/login',
      name: 'login',  // This name is used in App.vue
      component: LoginView,
      meta: { hideNavbar: true, requiresGuest: true }
    },
    {
      path: '/products',
      name: 'products',
      component: ProductListView,
      meta: { requiresAuth: true }
    },
    {
      path: '/orders',
      name: 'orders',
      component: OrdersView,
      meta: { requiresAuth: true }
    },
    {
      path: '/cart',
      name: 'cart',
      component: CartView,
      meta: { requiresAuth: true }
    }
  ],
});

router.beforeEach((to, from, next) => {
  const authStore = useAuthStore();
  
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
     console.log('Redirecting to login - no auth')
    next('/login');
  } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
    console.log('Redirecting to home - already authenticated')
    next('/products');
  } else {
    console.log('Allowing navigation')
    next();
  }
});

export default router;

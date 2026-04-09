<script setup>
import { computed } from 'vue'
import { RouterLink, RouterView, useRoute } from 'vue-router'
import HelloWorld from './components/HelloWorld.vue'
import Navbar from './components/Navbar.vue'

const route = useRoute()

const hideNavbarOn = ['login']

const showNavbar = computed(() => {
  return !hideNavbarOn.includes(route.name)
})

</script>

<template>
  <header v-if="showNavbar">
    <Navbar />
  </header>
  <RouterView />
</template>

<style scoped>
header {
  width: 100%;
  background: black;
  height:70px;
  padding:20px 10%;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.logo {
  display: block;
  margin: 0 auto 2rem;
}

@media (min-width: 1024px) {
  header {
    display: flex;
    place-items: center;
    background-color: #232833;
  }

  .logo {
    margin: 0 2rem 0 0;
  }

  header .wrapper {
    display: flex;
    place-items: flex-start;
    flex-wrap: wrap;
  }
}
</style>

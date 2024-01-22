<template>
    <main>
      <Navbar />
      <div class="mt 2">Ini dashboard ADMIN</div>
    </main>
  </template>
  
  <script>
  import Navbar from '@/components/DashboardNavbar.vue'
  export default {
    name: 'AdminDashboard',
    components: {
      Navbar
    }
  }
  </script>
  
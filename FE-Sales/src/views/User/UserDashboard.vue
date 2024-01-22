<template>
    <main>
      <Navbar />
      <div class="mt 2">Ini dashboard user</div>
    </main>
  </template>
  
  <script>
  import Navbar from '@/components/DashboardNavbar.vue'
  export default {
    name: 'UserDashboard',
    components: {
      Navbar
    }
  }
  </script>
  
<template>
  <nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid d-flex justify-content-between align-items-center">
      <a
        class="navbar-brand"
        href="/"
        style="font-size: 32px; padding-left: 20px; font-weight: bold"
        >DjaMoeDje</a
      >
      <div class="d-flex px-1 align-items-center order-lg-last">
        <router-link to="/login" class="nav-link p-1">
          <button class="btn">Masuk</button>
        </router-link>
      </div>
    </div>
  </nav>
</template>

<script>
export default {
  name: 'HomeNavbar',
  data() {
    return {
      isNavbarHidden: false,
      lastScrollPosition: 0,
      isNavbarVisible: true
    }
  },
  mounted() {
    window.addEventListener('scroll', this.handleScroll)
  },
  beforeMount() {
    window.removeEventListener('scroll', this.handleScroll)
  },
  methods: {
    handleScroll() {
      const currentScrollPosition = window.pageYOffset || document.documentElement.scrollTop
      if (currentScrollPosition < 0) {
        return
      }
      if (Math.abs(currentScrollPosition - this.lastScrollPosition) < 30) {
        return
      }
      this.isNavbarHidden = currentScrollPosition > this.lastScrollPosition
      this.isNavbarVisible = !this.isNavbarHidden
      this.lastScrollPosition = currentScrollPosition
    }
  }
}
</script>

<style>
.navbar-hidden {
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.5s ease-in-out;
}

.btn {
  background-color: #b9a119;
  border-color: transparent;
  color: white;
  font-weight: bold;
}

.btn:hover {
  background-color: #806407;
  border-color: transparent;
  transition: background-color 0.5s;
}
</style>

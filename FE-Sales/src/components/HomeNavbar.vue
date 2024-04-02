<template>
  <nav
    :class="{ 'navbar-hidden': isNavbarHidden }"
    class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark"
  >
    <div class="container-fluid d-flex justify-content-between align-items-center">
      <a class="navbar-brand" href="/" style="font-size: 32px">DjaMoeDje</a>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a class="nav-link active" aria-current="page" href="#">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="#">Link</a>
          </li>
          <li class="nav-item dropdown">
            <a
              class="nav-link dropdown-toggle"
              href="#"
              id="navbarDropdown"
              role="button"
              data-bs-toggle="dropdown"
              aria-expanded="false"
            >
              Dropdown
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
              <li><a class="dropdown-item" href="#">Action</a></li>
              <li><a class="dropdown-item" href="#">Another action</a></li>
              <li><hr class="dropdown-divider" /></li>
              <li><a class="dropdown-item" href="#">Something else here</a></li>
            </ul>
          </li>
        </ul>
      </div>
      <div class="d-flex px-1 align-items-center order-lg-last">
        <router-link to="/login" class="nav-link p-1">
          <button :class="{ 'btn': isNavbarVisible }" class="btn">Masuk</button>
        </router-link>
        <button
          class="navbar-toggler"
          type="button"
          data-bs-toggle="collapse"
          data-bs-target="#navbarSupportedContent"
          aria-controls="navbarSupportedContent"
          aria-expanded="false"
          aria-label="Toggle navigation"
        >
          <span class="navbar-toggler-icon"></span>
        </button>
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
      isNavbarVisible: true,
    };
 },
 mounted() {
    window.addEventListener('scroll', this.handleScroll);
 },
 beforeMount() {
    window.removeEventListener('scroll', this.handleScroll);
 },
 methods: {
    handleScroll() {
      const currentScrollPosition = window.pageYOffset || document.documentElement.scrollTop;
      if (currentScrollPosition < 0) {
        return;
      }
      if (Math.abs(currentScrollPosition - this.lastScrollPosition) < 30) {
        return;
      }
      this.isNavbarHidden = currentScrollPosition > this.lastScrollPosition;
      this.isNavbarVisible = !this.isNavbarHidden;
      this.lastScrollPosition = currentScrollPosition;
    },
 },
};
</script>

<style>
.navbar-hidden {
 opacity: 0;
 visibility: hidden;
 transition: opacity 0.5s ease-in-out;
}

.btn {
 background-color: #b9a119;
 border-color: #b9a119;
}

.btn:hover {
 background-color: #806407;
 border-color: #806407;
 transition: background-color 0.5s;
}

</style>
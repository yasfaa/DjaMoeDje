<template>
  <nav v-if="!isLoggedIn" class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark">
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
  <div v-else class="transition-content" :class="{ pushMainContent: isActive }">
    <div v-if="user.name === 'Admin'">
      <div id="mySidenav" class="sidenav shadow" :class="{ openNavClass: isActive }">
        <a class="closebtn" @click="isActive = !isActive" style="cursor: pointer">&times;</a>
        <router-link
          to="/admin/dashboard"
          exact
          class="nav-link"
          :class="{ 'active-link': $route.path === '/admin/dashboard' }"
        >
          Home
        </router-link>
        <router-link
          to="/admin/menu"
          exact
          class="nav-link"
          :class="{ 'active-link': $route.path === '/admin/menu' }"
        >
          Kelola Menu
        </router-link>
        <router-link
          to="/admin/menu"
          exact
          class="nav-link"
          :class="{ 'active-link': $route.path === '/admin/order' }"
        >
          Daftar Pesanan
        </router-link>
      </div>
      <div class="content">
        <div class="button-side">
          <nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark">
            <span style="font-size: 25px; cursor: pointer" @click="isActive = !isActive"
              >&#9776;</span
            >
            <a
              class="navbar-brand"
              href="/"
              style="font-size: 32px; padding-left: 30px; font-weight: bold"
              >DjaMoeDje</a
            >
            <div class="ms-auto mx-2">
              <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent"
                aria-expanded="false"
                aria-label="Toggle navigation"
              >
              </button>
              <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <div class="div">
                  <ul class="navbar-nav mb-2 mb-lg-0">
                    <li class="nav-item dropdown">
                      <button
                        class="btn dropdown-toggle"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                      >
                        {{ user.name }}
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                          <a class="dropdown-item" @click="profile">Profile</a>
                        </li>
                        <li>
                          <a class="dropdown-item" @click="onLogout()" style="cursor: pointer"
                            >Logout</a
                          >
                        </li>
                      </ul>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </nav>
        </div>
        <slot></slot>
      </div>
    </div>
    <div v-else>
      <div id="mySidenav" class="sidenav shadow" :class="{ openNavClass: isActive }">
        <a class="closebtn" @click="isActive = !isActive" style="cursor: pointer">&times;</a>
        <router-link to="/" exact class="nav-link" :class="{ 'active-link': $route.path === '/' }">
          Home
        </router-link>
        <router-link
          to="/menu"
          exact
          class="nav-link"
          :class="{ 'active-link': $route.path === '/menu' }"
        >
          Lihat Menu
        </router-link>
        <router-link
          to="/pesanan"
          exact
          class="nav-link"
          :class="{ 'active-link': $route.path === '//pesanan' }"
        >
          Pesananku
        </router-link>
      </div>
      <div class="content">
        <div class="button-side">
          <nav class="navbar fixed-top navbar-expand-lg navbar-dark bg-dark">
            <span style="font-size: 25px; cursor: pointer" @click="isActive = !isActive"
              >&#9776;
            </span>
            <a
              class="navbar-brand"
              href="/"
              style="font-size: 32px; padding-left: 20px; font-weight: bold"
              >DjaMoeDje</a
            >
            <div class="ms-auto mx-2">
              <button
                class="navbar-toggler"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarSupportedContent"
                aria-controls="navbarSupportedContent"
                aria-expanded="false"
                aria-label="Toggle navigation"
              ></button>
              <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <div class="div">
                  <ul class="navbar-nav mb-2 mb-lg-0">
                    <li class="nav-item dropdown">
                      <button
                        class="btn dropdown-toggle"
                        data-bs-toggle="dropdown"
                        aria-expanded="false"
                      >
                        {{ user.name }}
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                          <a class="dropdown-item text-center" @click="profile">Profile</a>
                        </li>
                        <li>
                          <a
                            class="dropdown-item text-center"
                            @click="onLogout()"
                            style="cursor: pointer"
                            >Logout</a
                          >
                        </li>
                      </ul>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
const BASE_URL = import.meta.env.VITE_BASE_URL_API
export default {
  name: 'HomeNavbar',
  data() {
    return {
      isLoggedIn: false,
      isActive: false,
      user: {},
      isNavbarHidden: false,
      lastScrollPosition: 0,
      isNavbarVisible: true
    }
  },
  async mounted() {
    window.addEventListener('scroll', this.handleScroll)
    try {
      const response = await axios.get(BASE_URL + '/auth/user', {
        headers: {
          Authorization: 'Bearer ' + localStorage.getItem('access_token')
        }
      })
      this.user = {
        name: response.data.name,
        role: response.data.role
      }
      this.isLoggedIn = true
    } catch (error) {
      console.error(error)
      
      this.isLoggedIn = false
      if (error.response && error.response.data.message) {
        const errorMessage = error.response.data.message
        // Tampilkan notifikasi dengan warna merah
        this.$notify({
          type: 'error',
          title: 'Error',
          text: errorMessage,
          color: 'red'
        })
      }
    }
  },
  beforeMount() {
    window.removeEventListener('scroll', this.handleScroll)
  },
  methods: {
    // profile() {
    //   const id = this.id
    //   this.$router.push({ path: `/profile/${id}` })
    // },
    onLogout() {
      axios
        .post(
          BASE_URL + '/auth/logout',
          {},
          {
            headers: {
              Authorization: 'Bearer ' + localStorage.getItem('access_token')
            }
          }
        )
        .then((response) => {
          localStorage.removeItem('access_token')
          this.$router.push('/')
        })
        .catch((error) => {
          console.error(error)
        })
    },
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

.sidenav {
  height: 100%;
  width: 0;
  position: fixed;
  z-index: 1;
  top: 0;
  left: -250px;
  background-color: #4b4b4b;
  overflow-x: hidden;
  transition: 0.5s;
  padding-top: 80px;
  width: 250px;
}

.openNavClass {
  left: 0;
}

.transition-content {
  transition: margin-left 0.5s;
}

.pushMainContent {
  margin-left: 250px;
}

@media screen and (max-width: 768px) {
  .pushMainContent {
    margin-left: 0;
  }
}

.sidenav a {
  padding: 8px 8px 8px 32px;
  text-decoration: none;
  font-size: 18px;
  color: #ffffff;
  display: block;
  transition: 0.3s;
}

.sidenav a:hover {
  color: #f1f1f1;
}

.sidenav .closebtn {
  position: absolute;
  top: 0;
  right: 25px;
  font-size: 36px;
  margin-left: 50px;
}

.button-side {
  padding: 16px;
}

@media screen and (max-height: 450px) {
  .sidenav {
    padding-top: 15px;
  }

  .sidenav a {
    font-size: 18px;
  }
}
</style>

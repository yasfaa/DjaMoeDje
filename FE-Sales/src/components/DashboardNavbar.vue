<template>
  <div class="transition-content" :class="{ pushMainContent: isActive }">
    <div v-if="isAdmin">
      <div id="mySidenav" class="sidenav shadow" :class="{ openNavClass: isActive }">
        <a class="closebtn" @click="isActive = !isActive" style="cursor: pointer">&times;</a>
        <router-link to="/" exact class="nav-link" :class="{ 'active-link': $route.path === '/#' }">
          Home
        </router-link>
        <router-link
          to="/admin/dashboard"
          exact
          class="nav-link"
          :class="{ 'active-link': $route.path === '/admin/dashboard' }"
        >
          Kelola Menu
        </router-link>
        <router-link
          to="/admin/order"
          exact
          class="nav-link"
          :class="{ 'active-link': $route.path === '/admin/order' }"
        >
          Daftar Pesanan
        </router-link>
      </div>
      <div class="content">
        <div class="button-side">
          <nav class="navbar fixed-top navbar-expand-lg">
            <span style="cursor: pointer; margin-left: 16px" @click="isActive = !isActive">
              <v-icon icon="mdi-menu" color="black"></v-icon>
            </span>
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
                        <v-icon> mdi-account </v-icon>
                        {{ user.name }}
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                          <a
                            class="dropdown-item text-center"
                            @click="AdminProfile()"
                            style="cursor: pointer"
                            >Profile</a
                          >
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
          to="/cart"
          exact
          class="nav-link"
          :class="{ 'active-link': $route.path === '/cart' }"
        >
          Keranjang saya
        </router-link>
        <router-link
          to="/order"
          exact
          class="nav-link"
          :class="{ 'active-link': $route.path === '/order' }"
        >
          Pesananku
        </router-link>
      </div>
      <div class="content">
        <div class="button-side">
          <nav class="navbar fixed-top navbar-expand-lg">
            <span style="cursor: pointer; margin-left: 16px" @click="isActive = !isActive">
              <v-icon icon="mdi-menu" color="black"></v-icon>
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
                        <v-icon> mdi-account </v-icon>
                        {{ user.name }}
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                          <a
                            class="dropdown-item text-center"
                            @click="profile()"
                            style="cursor: pointer"
                            >Profile</a
                          >
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
  name: 'DashboardNavbar',
  data() {
    return {
      isActive: false,
      user: {
        name: '',
        role: ''
      },
      isNavbarVisible: true
    }
  },
  mounted() {
    this.fetchUserData()
  },
  methods: {
    async fetchUserData() {
      try {
        const name = localStorage.getItem('name')
        const role = localStorage.getItem('role')
        if (name && role) {
          this.user.name = name
          this.user.role = role
        }
      } catch (error) {
        console.error(error)
        this.$notify({
          type: 'error',
          title: 'Error',
          text: errorMessage,
          color: 'red'
        })
      }
    },
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
          localStorage.removeItem('name')
          localStorage.removeItem('role')
          this.isLoggedIn = false
          this.$router.push('/')
        })
        .catch((error) => {
          console.error(error)
        })
    },
    profile() {
      this.$router.push('/profile')
    },
    AdminProfile() {
      this.$router.push('/admin/profile')
    }
  },
  computed: {
    isAdmin() {
      return this.user.role === 'Admin'
    }
  }
}
</script>

<style>
.sidenav {
  height: 100%;
  width: 0;
  position: fixed;
  z-index: 1000; /* Pastikan ini lebih tinggi dari konten lainnya */
  top: 0;
  left: -250px;
  background-color: #e5c54f;
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
  color: #6a6a6a;
}

.sidenav .closebtn {
  position: absolute;
  top: 0;
  right: 25px;
  font-size: 36px;
  margin-left: 50px;
  z-index: 3;
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

.navbar {
  z-index: 999;
  background-color: #fff6d6;
}
</style>

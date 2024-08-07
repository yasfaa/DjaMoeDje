<template>
  <div class="transition-content" :class="{ pushMainContent: isActive }">
    <div v-if="isAdmin">
      <div id="mySidenav" class="sidenav shadow" :class="{ openNavClass: isActive }">
        <a class="closebtn" @click="isActive = !isActive" style="cursor: pointer">&times;</a>
        <router-link to="/" exact class="nav-link" :class="{ 'active-link': $route.path === '/#' }">
          <v-icon>mdi-home</v-icon> Home
        </router-link>
        <router-link
          to="/admin/dashboard"
          exact
          class="nav-link"
          :class="{ 'active-link': $route.path === '/admin/dashboard' }"
        >
          <v-icon>mdi-view-dashboard</v-icon> Kelola Menu
        </router-link>
        <router-link
          to="/admin/order"
          exact
          class="nav-link"
          :class="{ 'active-link': $route.path === '/admin/order' }"
        >
          <v-icon>mdi-file-document</v-icon> Daftar Pesanan
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
              >
                <span class="navbar-toggler-icon"></span>
              </button>
              <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mb-2 mb-lg-0 ms-auto">
                  <li class="nav-item dropdown me-3">
                    <button
                      class="btn position-relative"
                      data-bs-toggle="dropdown"
                      aria-expanded="false"
                    >
                      <v-icon> mdi-bell </v-icon>
                      <span
                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                      >
                        {{ notificationCount }}
                        <span class="visually-hidden">unread messages</span>
                      </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end" style="width: 300px">
                      <li
                        v-for="notif in notification"
                        :key="notif.order_id"
                        class="px-3 py-2 border-bottom"
                        @click="lihatDetail(notif)"
                        style="cursor: pointer"
                      >
                        <div>
                          <strong>{{ notif.nama }}</strong>
                          <p class="mb-1">Nomor Pesanan: {{ notif.no_pesanan }}</p>
                          <small class="text-muted">{{ timeAgo(notif.tanggal) }}</small>
                        </div>
                      </li>
                      <li v-if="notification.length === 0" class="text-center py-2">
                        <span>Tidak ada notifikasi</span>
                      </li>
                    </ul>
                  </li>
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
          </nav>
        </div>
        <slot></slot>
      </div>
    </div>
    <div v-else>
      <div id="mySidenav" class="sidenav shadow" :class="{ openNavClass: isActive }">
        <a class="closebtn" @click="isActive = !isActive" style="cursor: pointer">&times;</a>
        <router-link to="/" exact class="nav-link" :class="{ 'active-link': $route.path === '/' }">
          <v-icon>mdi-home</v-icon> Home
        </router-link>
        <router-link
          to="/cart"
          exact
          class="nav-link"
          :class="{ 'active-link': $route.path === '/cart' }"
        >
          <v-icon>mdi-cart</v-icon> Keranjang saya
        </router-link>
        <router-link
          to="/order"
          exact
          class="nav-link"
          :class="{ 'active-link': $route.path === '/order' }"
        >
          <v-icon>mdi-file-document</v-icon> Pesananku
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
              >
                <span class="navbar-toggler-icon"></span>
              </button>
              <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav mb-2 mb-lg-0 ms-auto">
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
      isNotificationDropdownVisible: false,
      user: {
        name: '',
        role: ''
      },
      notification: [],
      isNavbarVisible: true
    }
  },
  mounted() {
    this.fetchUserData()
    this.retrieveNotif()
  },
  methods: {
    toggleNotificationDropdown() {
      this.isNotificationDropdownVisible = !this.isNotificationDropdownVisible
    },
    async retrieveNotif() {
      try {
        const response = await axios.get(BASE_URL + '/auth/notif', {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })
        this.notification = response.data.order
      } catch (error) {
        console.error('Error fetching notifications:', error)
      }
    },
    timeAgo(date) {
      const now = new Date()
      const past = new Date(date)
      const diffInSeconds = Math.floor((now - past) / 1000)

      const intervals = {
        tahun: 31536000,
        bulan: 2592000,
        hari: 86400,
        jam: 3600,
        menit: 60
      }

      let counter
      for (const i in intervals) {
        counter = Math.floor(diffInSeconds / intervals[i])
        if (counter > 0) return `${counter} ${i}${counter === 1 ? '' : ''} yang lalu`
      }
      return 'baru saja'
    },
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
    },
    lihatDetail(notification) {
      this.$router.push('/orders/' + notification.order_id)
    }
  },
  computed: {
    isAdmin() {
      return this.user.role === 'Admin'
    },
    notificationCount() {
      return this.notification.length
    }
  }
}
</script>

<style>
.sidenav {
  height: 100%;
  width: 0;
  position: fixed;
  z-index: 1000;
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
    z-index: 999;
  }

  .sidenav a {
    font-size: 18px;
  }
}

.navbar {
  z-index: 999;
  background-color: #fff6d6;
}

.dropdown-toggle:focus,
.dropdown-toggle:hover {
  background-color: #e5c54f;
  color: black;
}

.dropdown-menu {
  width: 150px;
}

@media (max-width: 768px) {
  .navbar-nav .dropdown-menu {
    width: auto;
  }

  .navbar-collapse {
    justify-content: flex-end;
  }

  .navbar-toggler {
    border: none;
  }

  .navbar-toggler:focus,
  .navbar-toggler:hover {
    background-color: transparent;
  }
}
</style>

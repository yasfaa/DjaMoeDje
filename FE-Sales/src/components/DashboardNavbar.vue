<template>
  <div class="transition-content" :class="{ pushMainContent: isActive }">
    <div v-if="role === 'Admin'">
      <div id="mySidenav" class="sidenav shadow" :class="{ openNavClass: isActive }">
        <a class="closebtn" @click="isActive = !isActive" style="cursor: pointer">&times;</a>
        <router-link
          to="/admin/dashboard"
          exact
          class="nav-link"
          :class="{ 'active-link': $route.path === '/admin/dashboard' }"
          >Home</router-link
        >
        <router-link
          to="/admin/daftarbuku"
          exact
          class="nav-link"
          :class="{ 'active-link': $route.path === '/admin/daftarbuku' }"
          >Daftar Pesanan</router-link
        >
        <router-link
          to="/admin/kelolapelanggan"
          exact
          class="nav-link"
          :class="{ 'active-link': $route.path === '/admin/kelolapelanggan' }"
          >Kelola Pelanggan</router-link
        >
      </div>
      <div class="content">
        <div class="button-side">
          <nav class="navbar navbar-expand-lg navbar-light white shadow-sm bg-light fixed-top">
            <span style="font-size: 25px; cursor: pointer" @click="isActive = !isActive"
              >&#9776;
            </span>
            <a class="navbar-brand" href="/" style="font-size: 32px">DjaMoeDje</a>
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
                <ion-icon name="person"></ion-icon>
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
                        {{ username }}
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                          <a class="dropdown-item" @click="profile"
                            ><ion-icon name="person"></ion-icon>&nbsp;Profile</a
                          >
                        </li>
                        <li>
                          <a class="dropdown-item" @click="onLogout()" style="cursor: pointer"
                            ><ion-icon name="log-out"></ion-icon>&nbsp;Logout</a
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
        <router-link
          to="/dashboard"
          exact
          class="nav-link"
          :class="{ 'active-link': $route.path === '/dashboard' }"
          >Home</router-link
        >
        <router-link
          to="/menu"
          exact
          class="nav-link"
          :class="{ 'active-link': $route.path === '/menu' }"
          >Daftar Menu</router-link
        >
        <router-link
          to="/pesanan"
          exact
          class="nav-link"
          :class="{ 'active-link': $route.path === '//pesanan' }"
          >Kelola Pesanan</router-link
        >
      </div>
      <div class="content">
        <div class="button-side">
          <nav class="navbar navbar-expand-lg navbar-light white shadow-sm bg-light fixed-top">
            <span style="font-size: 25px; cursor: pointer" @click="isActive = !isActive"
              >&#9776;
            </span>
            <a class="navbar-brand" href="/" style="font-size: 32px">DjaMoeDje</a>
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
                        {{ username }}
                      </button>
                      <ul class="dropdown-menu dropdown-menu-end">
                        <li>
                          <a class="dropdown-item" @click="profile"
                            ><ion-icon name="person"></ion-icon>&nbsp;Profile</a
                          >
                        </li>
                        <li>
                          <a class="dropdown-item" @click="onLogout()" style="cursor: pointer"
                            ><ion-icon name="log-out"></ion-icon>&nbsp;Logout</a
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
      username: '',
      id: ''
    }
  },
  async mounted() {
    try {
      const response = await axios.get(BASE_URL + '/auth/user', {
        headers: {
          Authorization: 'Bearer ' + localStorage.getItem('access_token')
        }
      })
      this.id = response.data.id
      this.username = response.data.name
    } catch (error) {
      console.error(error)

      if (error.response && error.response.data.message) {
        const errorMessage = error.response.data.message
        // Display notification with red color
        this.$notify({
          type: 'error',
          title: 'Error',
          text: errorMessage,
          color: 'red'
        })
      }
    }
  },
  methods: {
    // profile() {
    //   const id = this.id
    //   this.$router.push({ path: `/profile/${id}` })
    // }
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
          this.$router.push('/login')
        })
        .catch((error) => {
          console.error(error)
        })
    }
  }
}
</script>
<style scoped>
.navbar {
  padding-left: 15px;
}

.navbar-brand {
  font-weight: bold;
  padding-left: 15px;
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

.overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  /* Change the alpha value to adjust transparency */
  display: none;
  z-index: 0;
}

.showOverlay {
  display: block;
  z-index: 1;
}

@media screen and (max-height: 450px) {
  .sidenav {
    padding-top: 15px;
  }

  .sidenav a {
    font-size: 18px;
  }
}

.content {
  min-height: 100vh;

  background: url('../../../src/assets/LandingPage/Background.png');
  background-position: center;
  background-size: cover;
}
</style>

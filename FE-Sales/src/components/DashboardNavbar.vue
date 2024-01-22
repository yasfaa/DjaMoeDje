<template>
  <main>
    <Navbar />
    <section class="navbar-section">
      <div class="container">
        <div class="form-box" :class="{ 'register-form': isRegister }">
          <div class="padding-container">
              <div v-if="role === 'Admin'">
                <div class="transition-content" :class="{ pushMainContent: isActive }">
                  <div>
                    <div id="mySidenav" class="sidenav shadow" :class="{ openNavClass: isActive }">
                      <a class="closebtn" @click="isActive = !isActive" style="cursor: pointer"
                        >&times;</a
                      >
                      <a href="/admin/dashboard">Home</a>
                      <a href="/admin/daftarbuku">Daftar Buku</a>
                      <a href="/admin/kelolapelanggan">Kelola Pelangan</a>
                    </div>
                    <div class="content">
                      <div class="button-side">
                        <nav
                          class="navbar navbar-expand-lg navbar-light white bgnav shadow-sm rounded"
                        >
                          <span
                            style="font-size: 25px; cursor: pointer"
                            @click="isActive = !isActive"
                            >&#9776;</span
                          >
                          <a class="navbar-brand" href="/" style="margin-left: 15px"
                            >Toko Buku Oscar</a
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
                                        <a
                                          class="dropdown-item"
                                          @click="onLogout()"
                                          style="cursor: pointer"
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
              </div>
              <form v-else key="register-form" @submit.prevent="onRegist">
                <h2>Register</h2>
                <div class="inputbox">
                  <input class="input-type" type="input" v-model="registerName" required />
                  <label for="">Name</label>
                </div>
                <div class="inputbox">
                  <input class="input-type" type="email" v-model="registerEmail" required />
                  <label for="">Email</label>
                </div>
                <div class="inputbox">
                  <input class="input-type" type="password" v-model="registerPassword" required />
                  <label for="">Password</label>
                </div>
                <div class="inputbox">
                  <input class="input-type" type="password" v-model="confirmPassword" required />
                  <label for="">Re-Password</label>
                </div>
                <div class="text-uppercase mb-3" v-if="passwordsNotMatch" style="color: red">
                  <b>Passwords do not match</b>
                </div>
                <button class="login-button" type="submit" style="color: white">Register</button>
                <div class="register">
                  <p>
                    Already have an account?
                    <a @click="toggleForm" style="cursor: pointer">Log in</a>
                  </p>
                </div>
          </div>
        </div>
      </div>
    </section>
  </main>
</template>

<script>
import axios from 'axios'
const BASE_URL = import.meta.env.VITE_BASE_URL_API
export default {
  name: 'AdminNavbar',
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
      this.id = response.data.user.id
      this.username = response.data.user.name
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
    profile() {
      const id = this.id
      this.$router.push({ path: `/profile/${id}` })
    }
    // onLogout() {
    //     axios.post(BASE_URL + '/logout', {}, {
    //         headers: {
    //             Authorization: "Bearer " + localStorage.getItem('access_token'),
    //         }
    //     })
    //         .then(response => {
    //             localStorage.removeItem('access_token');
    //             this.$router.push('/login');
    //         })
    //         .catch(error => {
    //             console.error(error);
    //         });
    // }
  }
}
</script>

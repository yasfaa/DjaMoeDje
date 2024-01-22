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
                        <nav class="navbar navbar-expand-lg navbar-light white shadow-sm bg-light fixed-top">
                          <span
                            style="font-size: 25px; cursor: pointer"
                            @click="isActive = !isActive"
                            >&#9776;
                          </span>
                          <a class="navbar-brand" href="/" style="font-size: 32px;">DjaMoeDje</a>
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
              <div v-else>
                <div class="transition-content" :class="{ pushMainContent: isActive }">
                  <div>
                    <div id="mySidenav" class="sidenav shadow" :class="{ openNavClass: isActive }">
                      <a class="closebtn" @click="isActive = !isActive" style="cursor: pointer"
                        >&times;</a
                      >
                      <a href="/dashboard">Home</a>
                      <a href="/menu">Daftar Menu</a>
                      <a href="/Pesanan">Pesanan Saya</a>
                    </div>
                    <div class="content">
                      <div class="button-side">
                        <nav class="navbar navbar-expand-lg navbar-light white shadow-sm bg-light fixed-top">
                        <span style="font-size: 25px; cursor: pointer" @click="isActive = !isActive">&#9776;
                          </span>
                          <a class="navbar-brand" href="/" style="font-size: 32px;">DjaMoeDje</a>
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
        axios.post(BASE_URL + '/auth/logout', {}, {
            headers: {
                Authorization: "Bearer " + localStorage.getItem('access_token'),
            }
        })
            .then(response => {
                localStorage.removeItem('access_token');
                this.$router.push('/login');
            })
            .catch(error => {
                console.error(error);
            });
    }
  }
}
</script>
<style scoped>
.navbar-section {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    width: 100%;
    background-image: url('../assets/LandingPage/Background.png');
    background-position: center;
    background-size: cover;
}

.form-box {
    position: relative;
    width: 400px;
    height: 450px;
    background: transparent;
    border: 2px solid rgba(0, 0, 0, 0.5);
    border-radius: 20px;
    backdrop-filter: blur(15px);
    display: flex;
    justify-content: center;
    align-items: center;

}

h2 {
    font-size: 2em;
    color: black;
    text-align: center;
}

.inputbox {
    position: relative;
    margin: 30px 0;
    width: 310px;
    border-bottom: 2px solid black;
}

.inputbox label {
    position: absolute;
    top: 50%;
    left: 5px;
    transform: translateY(-50%);
    color: black;
    font-size: 1em;
    pointer-events: none;
    transition: .5s;
}

.input-type:focus~label,
.input-type:valid~label {
    top: -5px;
}

.inputbox input {
    width: 100%;
    height: 50px;
    background: transparent;
    border: none;
    outline: none;
    font-size: 1em;
    padding: 0 35px 0 5px;
    color: black;
}

.inputbox ion-icon {
    position: absolute;
    right: 8px;
    color: black;
    font-size: 1.2em;
    top: 20px;
}

.forget {
    margin: -15px 0 15px;
    font-size: .9em;
    color: black;
    display: flex;
    justify-content: space-between;
}

.forget label input {
    margin-right: 3px;

}

.forget label a {
    color: black;
    text-decoration: none;
}

.forget label a:hover {
    text-decoration: underline;
}

.login-button {
    width: 100%;
    height: 40px;
    border-radius: 40px;
    background: black;
    border: none;
    outline: none;
    cursor: pointer;
    font-size: 1em;
    font-weight: 600;
}

.register {
    font-size: .9em;
    color: black;
    text-align: center;
    margin: 25px 0 10px;
}

.register p a {
    text-decoration: none;
    color: black;
    font-weight: 600;
}

.register p a:hover {
    text-decoration: underline;
}

.container {
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.padding-container {
    padding: 20px;
    display: flex;
    justify-content: center;
    align-items: center;
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s;
}

.fade-enter,
.fade-leave-to {
    opacity: 0;
}
</style>


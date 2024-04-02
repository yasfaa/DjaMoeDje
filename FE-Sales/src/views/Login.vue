<template>
  <main>
      <Navbar />
      <section class="login-section">
          <div class="container">
              <div class="form-box" :class="{ 'register-form': isRegister }">
                  <div class="padding-container">
                      <div class="form-value">
                          <transition name="fade" mode="out-in">
                              <form v-if="!isRegister && !isEmailForgotPassword && !isForgotPassword" key="login-form"
                                  @submit.prevent="onSubmit">
                                  <h2>Login</h2>
                                  <div class="inputbox">
                                      <ion-icon name="mail-outline"></ion-icon>
                                      <input class="input-type" type="email" v-model="loginEmail" required>
                                      <label for="">Email</label>
                                  </div>
                                  <div class="inputbox">
                                      <ion-icon name="lock-closed-outline"></ion-icon>
                                      <input class="input-type" type="password" v-model="loginPassword" required>
                                      <label for="">Password</label>
                                  </div>
                                  <button class="login-button" type="submit" style="color:white">Log in</button>
                                  <div class="register">
                                      <p>Don't have an account <a @click="toggleForm" style="cursor: pointer">Register</a>
                                      </p>
                                  </div>
                              </form>
                              <form v-else key="register-form" @submit.prevent="onRegist">
                                  <h2>Register</h2>
                                  <div class="inputbox">
                                      <ion-icon name="person-outline"></ion-icon>
                                      <input class="input-type" type="input" v-model="registerName" required>
                                      <label for="">Name</label>
                                  </div>
                                  <div class="inputbox">
                                      <ion-icon name="mail-outline"></ion-icon>
                                      <input class="input-type" type="email" v-model="registerEmail" required>
                                      <label for="">Email</label>
                                  </div>
                                  <div class="inputbox">
                                      <ion-icon name="lock-closed-outline"></ion-icon>
                                      <input class="input-type" type="password" v-model="registerPassword" required>
                                      <label for="">Create Password</label>
                                  </div>
                                  <button class="login-button" type="submit" style="color:white">Register</button>
                                  <div class="register">
                                      <p>Already have an account <a @click="toggleForm" style="cursor: pointer">Log in</a>
                                      </p>
                                  </div>
                              </form>
                          </transition>
                      </div>
                  </div>
              </div>
          </div>
      </section>
  </main>
</template>

<script>
import Navbar from '@/components/HomeNavbar.vue'
import axios from 'axios'
const BASE_URL = import.meta.env.VITE_BASE_URL_API
export default {
  name: 'LoginPage',
  components: {
    Navbar
  },
  data() {
    return {
      isRegister: false,
      // Login Inpput
      loginEmail: '',
      loginPassword: '',
      // Register Input
      registerName: '',
      registerEmail: '',
      registerPassword: '',
      registerRePassword: '',
      passwordsNotMatch: false
    }
  },
  mounted() {
    const inputElements = this.$el.querySelectorAll('input')
    inputElements.forEach((input) => {
      if (input.value) {
        this.moveLabelUp(input)
      }
      input.addEventListener('input', () => {
        if (input.value) {
          this.moveLabelUp(input)
        } else {
          this.moveLabelDown(input)
        }
      })
    })

    const accessToken = localStorage.getItem('access_token')
    if (accessToken) {
      axios.get(BASE_URL + '/user', {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })
        .then((response) => {
          if (response.data.role === 'Admin') {
            this.$router.push('/admin/dashboard')
          } else if (response.data.role === 'User') {
            this.$router.push('/dashboard')
          }
        })
        .catch((error) => {
          console.error(error)
          // Handle error if necessary
        })
    }
  },

  methods: {
    toggleForm() {
      this.isRegister = !this.isRegister
    },
    moveLabelUp(input) {
      const label = input.nextElementSibling
      label.style.top = '-5px'
    },

    moveLabelDown(input) {
      const label = input.nextElementSibling
      if (!input.value) {
        label.style.top = '50%'
      }
    },

    async onSubmit() {
      try {
        const response = await axios.post(BASE_URL + '/auth/login', {
          email: this.loginEmail,
          password: this.loginPassword
        })
        localStorage.setItem('access_token', response.data.access_token)
        localStorage.setItem('name', response.data.name)
        console.log(response.data)

        if (response.data.role === 'Admin') {
          localStorage.setItem('access_token', response.data.access_token)
          this.$router.push('/admin/dashboard')
        } else if (response.data.role === 'User') {
          localStorage.setItem('access_token', response.data.access_token)
          this.$router.push('/dashboard')
        }
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
    async onRegist() {
      try {
        if (this.registerPassword !== this.confirmPassword) {
          this.passwordsNotMatch = true
          return
        }

        const response = await axios.post(BASE_URL + '/auth/register', {
          name: this.registerName,
          email: this.registerEmail,
          password: this.registerPassword
        })

        console.log(response.data)
        this.$router.push('/login')
        // Reload the page after redirection
        window.location.reload()
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
    }
  }
}
</script>
<style scoped>
.login-section {
  display: flex;
  justify-content: center;
  align-items: center;
  padding-top: 100px;
  width: 100%;
  background-position: center;
  background-size: cover;
}

.form-box {
  position: relative;
  width: 400px;
  height: auto;
  background: #141001;
  border: 1px solid rgba(255, 255, 255, 0.5);
  border-radius: 10px;
  display: flex;
  justify-content: center;
  align-items: center;
}

h2 {
  font-size: 2em;
  color: white;
  text-align: center;
}

.inputbox {
  position: relative;
  margin: 30px 0;
  width: 310px;
  border-bottom: 1px solid wheat;
}

.inputbox label {
  position: absolute;
  top: 50%;
  left: 5px;
  transform: translateY(-50%);
  color: white;
  font-size: 1em;
  pointer-events: none;
  transition: 0.5s;
}

.input-type:focus ~ label,
.input-type:valid ~ label {
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
  color: whitesmoke;
}

.login-button {
  width: 100%;
  height: 40px;
  border-radius: 40px;
  background: #b9a119;
  border: none;
  outline: none;
  cursor: pointer;
  font-size: 1em;
  font-weight: 600;
}

.login-button:hover{
  background-color: #806407;
  transition: background-color 0.5s;
}

.register {
  font-size: 0.9em;
  color: white;
  text-align: center;
  margin: 25px 0 10px;
}

.register p a {
  text-decoration: none;
  color: white;
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

.slide-enter-active,
.slide-leave-active {
  transition: opacity 0.5s;
}

.slide-enter,
.slide-leave-to {
  opacity: 0;
}
</style>

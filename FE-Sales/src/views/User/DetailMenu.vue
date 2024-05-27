<template>
  <section class="navbar">
    <Navbar />
  </section>
  <div class="py-4 container-fluid gradient-container">
    <div class="container">
      <div class="row mt-5">
        <div class="card border-2 pt-3" v-if="menu.id">
          <v-carousel hide-delimiters class="carousel">
            <v-carousel-item v-for="(link, index) in fileLinks" :key="index">
              <v-img :src="link || 'https://via.placeholder.com/150'"></v-img>
            </v-carousel-item>
          </v-carousel>
          <div class="row p-2 pt-2">
            <div class="d-flex flex-column">
              <div class="d-flex justify-content-between align-items-center">
                <h2 class="menu-title">{{ menu.nama_menu }}</h2>
              </div>
              <div class="menu-price">Rp. {{ formatPrice(menu.total) }}</div>
              <div class="theme-text subtitle">Deskripsi:</div>
              <div class="brief-description">{{ menu.deskripsi }}</div>
              <div class="justify-content-end">
                <button class="btn btn-primary" @click="goToMenu(menu.id)">Add to Cart</button>
                <button class="btn btn-secondary mx-4 my-2">Customize Menu</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import Navbar from '@/components/HomeNavbar.vue'

const BASE_URL = import.meta.env.VITE_BASE_URL_API

export default {
  components: {
    Navbar
  },
  data() {
    return {
      menu: {},
      fileLinks: [],
      overlay: false
    }
  },
  mounted() {
    this.retrieveMenu()
  },
  methods: {
    formatPrice(price) {
      const numericPrice = parseFloat(price)
      return numericPrice.toLocaleString('id-ID')
    },
    async retrieveMenu() {
      try {
        this.overlay = true

        const id = this.$route.params.id
        const response = await axios.get(`${BASE_URL}/menu/get/` + id, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })

        if (response.data.status === 'success') {
          this.menu = response.data.menu
          this.fileLinks = response.data.fileLinks
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
      } finally {
        this.overlay = false
      }
    }
  }
}
</script>

<style scoped>
.card {
  border-radius: 10px !important;
  box-shadow: 1px 1px 15px #cccccc40;
  transition: 0.5s ease-in;
  background-color: white;
  margin: 1rem 0;
}

.card:hover {
  box-shadow: 1px 1px 28.5px -7px #d6d6d6;
}

.carousel {
  max-height: 550px;
  overflow: hidden;
  border-radius: 10px;
}

.menu-title {
  font-size: 2rem;
  font-weight: bold;
  color: #19160c;
  margin-bottom: 0.5rem;
}

.menu-price {
  font-size: 1.5rem;
  font-weight: bold;
  color: #19160c;
  margin-bottom: 1rem;
}

.brief-description {
  font-size: 1rem;
  color: #333;
  margin-bottom: 1rem;
}

.subtitle {
  font-size: 1.25rem;
  font-weight: bold;
  color: #666;
  margin-bottom: 0.5rem;
}

.btn {
  font-size: 1.2rem;
  border-radius: 5px;
  transition: background-color 0.3s;
}

.btn-primary {
  background-color: #ffe279;
  color: white;
  border: none;
}

.btn-primary:hover {
  background-color: #e5c54f;
}

.btn-secondary {
  background-color: #6c757d;
  color: white;
  border: none;
}

.btn-secondary:hover {
  background-color: #5a6268;
}
</style>

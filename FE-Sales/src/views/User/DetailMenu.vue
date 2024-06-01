<template>
  <section class="navbar">
    <Navbar />
  </section>
  <div class="py-4 container-fluid gradient-container">
    <div class="container">
      <div class="row mt-5">
        <div class="card border-2 pt-3" v-if="menu.id">
          <div id="menuCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
              <div
                v-for="(link, index) in fileLinks"
                :key="index"
                :class="['carousel-item', { active: index === 0 }]"
              >
                <img
                  :src="link || 'https://via.placeholder.com/150'"
                  class="d-block w-100"
                  alt="Menu Image"
                />
              </div>
            </div>
            <button
              class="carousel-control-prev"
              type="button"
              data-bs-target="#menuCarousel"
              data-bs-slide="prev"
            >
              <span class="carousel-control-prev-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Previous</span>
            </button>
            <button
              class="carousel-control-next"
              type="button"
              data-bs-target="#menuCarousel"
              data-bs-slide="next"
            >
              <span class="carousel-control-next-icon" aria-hidden="true"></span>
              <span class="visually-hidden">Next</span>
            </button>
          </div>
          <div class="row p-2 pt-2">
            <div class="d-flex flex-column">
              <div class="d-flex justify-content-between align-items-center">
                <h2 class="menu-title">{{ menu.nama_menu }}</h2>
              </div>
              <div class="menu-price">Rp. {{ formatPrice(menu.total) }}</div>
              <div class="theme-text subtitle">Deskripsi:</div>
              <div class="brief-description">{{ menu.deskripsi }}</div>
              <div class="justify-content-end">
                <button class="btn btn-primary me-4" @click.prevent="addToCart(menu.id)">
                  Add to Cart
                </button>
                <button class="btn btn-secondary my-2">Customize Menu</button>
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
    },
    async addToCart(menuId) {
      const isLoggedIn = !!localStorage.getItem('access_token') // Check if the user is logged in

      if (!isLoggedIn) {
        this.$router.push('/login') // Redirect to login if not logged in
        return
      }

      try {
        const formData = new FormData()
        formData.append('menu_id', menuId)
        formData.append('quantity', '1')

        await axios.post(BASE_URL + '/cart/add', formData, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token'),
            'Content-Type': 'multipart/form-data'
          }
        })
        this.showSuccessDialog = true
      } catch (error) {
        console.error('Error adding menu to cart:', error)
      }
    }
  }
}
</script>

<style scoped>
.card {
  border-radius: 10px !important;
  box-shadow: 1px 1px 15px #cccccc40;
  background-color: white;
  margin: 1rem 0;
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

@media (max-width: 600px) {
  .carousel {
    max-height: 200px;
    overflow: hidden;
    border-radius: 10px;
  }
}
</style>

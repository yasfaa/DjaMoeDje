<script>
import Navbar from '@/components/HomeNavbar.vue'
import axios from 'axios'
const BASE_URL = import.meta.env.VITE_BASE_URL_API

export default {
  name: 'HomePage',
  components: {
    Navbar
  },
  data() {
    return {
      menus: '',
      showSuccessDialog: false
    }
  },
  mounted() {
    this.retrieveMenus()
  },
  methods: {
    formatPrice(price) {
      const numericPrice = parseFloat(price)
      return numericPrice.toLocaleString('id-ID')
    },
    goToMenu(menuId) {
      this.$router.push(`/menu/${menuId}`)
    },
    async retrieveMenus() {
      try {
        const response = await axios.get(BASE_URL + '/getMenu', {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })
        this.menus = response.data.menus
      } catch (error) {
        console.error('Error fetching menus:', error)
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
    },
    getMenuImage(menuId) {
      if (this.menus && this.menus.length > 0) {
        const menu = this.menus.find((menu) => menu.id === menuId)
        if (menu && menu.imagePath) {
          return menu.imagePath
        }
      }
      return 'https://via.placeholder.com/150'
    }
  }
}
</script>

<template>
  <section class="navbar pb-6">
    <Navbar />
  </section>
  <div class="hero-section mt-2">
    <div id="carouselExampleControls" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner">
        <div class="carousel-item active">
          <img
            src="https://cdn.vuetifyjs.com/images/cards/docks.jpg"
            class="d-block w-100"
            alt="..."
          />
        </div>
        <div class="carousel-item">
          <img
            src="https://cdn.vuetifyjs.com/images/cards/hotel.jpg"
            class="d-block w-100"
            alt="..."
          />
        </div>
        <div class="carousel-item">
          <img
            src="https://cdn.vuetifyjs.com/images/cards/sunshine.jpg"
            class="d-block w-100"
            alt="..."
          />
        </div>
      </div>
      <button
        class="carousel-control-prev"
        type="button"
        data-bs-target="#carouselExampleControls"
        data-bs-slide="prev"
      >
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
      </button>
      <button
        class="carousel-control-next"
        type="button"
        data-bs-target="#carouselExampleControls"
        data-bs-slide="next"
      >
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
      </button>
    </div>
  </div>
  <div class="menu-section container">
    <div class="row">
      <div class="col-12">
        <h2 class="section-title">Our Menu</h2>
      </div>
      <div class="col-12">
        <div class="menu-grid">
          <v-row>
            <v-col v-for="menu in menus" :key="menu.id" cols="12" sm="6" md="4">
              <v-card class="menu-card">
                <v-img
                  :src="getMenuImage(menu.id)"
                  height="225"
                  gradient="to bottom, rgba(0,0,0,.1), rgba(0,0,0,.5)"
                  cover
                >
                </v-img>
                <v-card-title class="menu-title">{{ menu.nama }}</v-card-title>
                <v-card-actions class="menu-actions">
                  <p class="menu-price mt-0">Rp {{ formatPrice(menu.total) }}</p>
                  <v-spacer></v-spacer>
                  <button class="btn btn-primary" @click="goToMenu(menu.id)">View Menu</button>
                  <button class="btn btn-secondary mx-2" @click.prevent="addToCart(menu.id)">
                    Add to Cart
                  </button>
                </v-card-actions>
              </v-card>
            </v-col>
          </v-row>
        </div>
      </div>
    </div>
  </div>
  <div>
    <v-dialog v-model="showSuccessDialog" max-width="400">
      <v-card>
        <v-card-title>Success</v-card-title>
        <v-card-text> Menu berhasil dimasukkan ke dalam keranjang! </v-card-text>
        <v-card-actions>
          <v-btn color="primary" @click="showSuccessDialog = false">OK</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
  <app-footer class="footer-section" />
</template>

<style scoped>
.navbar {
  z-index: 999;
}

.hero-section {
  position: relative;
}

.carousel {
  max-height: 400px;
  overflow: hidden;
  z-index: 0;
}

.carousel-item img {
  width: 100%;
  height: auto;
  aspect-ratio: 21/9;
}

.menu-section {
  padding: 2rem 0;
}

.section-title {
  font-size: 2.5rem;
  font-weight: bold;
  color: #19160c;
  text-align: center;
  margin-bottom: 1.5rem;
}

.menu-grid {
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
}

.menu-card {
  border-radius: 15px;
  box-shadow: 1px 1px 15px #cccccc40;
  transition: 0.5s ease-in;
  background-color: white;
  margin: 1rem;
}

.menu-card:hover {
  box-shadow: 1px 1px 28.5px -7px #d6d6d6;
}

.menu-title {
  font-size: 1.5rem;
  font-weight: bold;
  color: #19160c;
  margin-top: 1rem;
}

.menu-actions {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0.5rem 1rem;
}

.menu-price {
  font-size: 1.3rem;
  font-weight: bold;
}

.btn {
  border-radius: 5px;
  padding: 0.25rem 0.5rem;
  font-size: 0.875rem;
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
  }
}
</style>

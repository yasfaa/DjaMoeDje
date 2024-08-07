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
      showSuccessDialog: false,
      user: null,
      isLoggedIn: false
    }
  },
  mounted() {
    this.retrieveMenus()

    try {
      const name = localStorage.getItem('name')
      const role = localStorage.getItem('role')
      if (name && role) {
        this.user = { name, role }
        this.isLoggedIn = true
      } else {
        this.isLoggedIn = false
      }
    } catch (error) {
      console.error(error)
      this.isLoggedIn = false
      if (error.response && error.response.data.message) {
        const errorMessage = error.response.data.message
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
      const isLoggedIn = !!localStorage.getItem('access_token')

      if (!isLoggedIn) {
        this.$router.push('/login')
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
            src="../assets/img/banner.png"
            style="width: 100%; max-height: 500px; object-fit: cover"
          />
        </div>
      </div>
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
              <v-card class="menu-card" @click.prevent="goToMenu(menu.id)">
                <div class="menu-image-container">
                  <v-img :src="getMenuImage(menu.id)" class="menu-image"></v-img>
                </div>
                <v-card-title class="menu-title">{{ menu.nama }}</v-card-title>
                <v-card-actions class="menu-actions align-item-center">
                  <p class="menu-price m-0">Rp {{ formatPrice(menu.total) }}</p>
                  <v-spacer></v-spacer>
                  <button class="btn btn-primary" @click="goToMenu(menu.id)">Lihat Menu</button>
                  <button
                    v-if="!user || user.role !== 'Admin'"
                    class="btn btn-secondary ms-2"
                    @click.prevent.stop="addToCart(menu.id)"
                  >
                    + Keranjang
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
        <v-card-text>Menu berhasil dimasukkan ke dalam keranjang!</v-card-text>
        <v-card-actions>
          <v-btn color="primary" @click="showSuccessDialog = false">OK</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<style scoped>
.navbar {
  z-index: 999;
}

.hero-section {
  position: relative;
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

.menu-image-container {
  position: relative;
  width: 100%;
  padding-bottom: 100%;
  background-color: rgba(255, 255, 255, 0.212);
}

.menu-image {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  object-fit: contain;
}

@media (max-width: 600px) {
  .carousel {
    max-height: 200px;
  }
}
</style>

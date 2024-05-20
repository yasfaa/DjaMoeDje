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
      menus: ''
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
  <section class="navbar">
    <Navbar />
  </section>
  <div class="py-4 container-fluid">
    <div class="row mt-6">
      <div class="row mt-6">
        <v-carousel class="carousel" hide-delimiters show-arrows="hover" height="400" width="100%">
          <v-carousel-item
            src="https://cdn.vuetifyjs.com/images/cards/docks.jpg"
            cover
          ></v-carousel-item>
          <v-carousel-item
            src="https://cdn.vuetifyjs.com/images/cards/hotel.jpg"
            cover
          ></v-carousel-item>
          <v-carousel-item
            src="https://cdn.vuetifyjs.com/images/cards/sunshine.jpg"
            cover
          ></v-carousel-item>
        </v-carousel>
      </div>
      <section class="next-section mt-6 py-5">
        <div class="container">
          <v-row>
            <v-col v-for="menu in menus" :key="menu.id" cols="12" sm="6" md="4">
              <v-card hover @click="goToMenu(menu.id)" class="rounded-card">
                <v-img
                  :src="getMenuImage(menu.id)"
                  height="225"
                  gradient="to bottom, rgba(0,0,0,.1), rgba(0,0,0,.5)"
                  cover
                >
                </v-img>
                <h6 class="nama-menu">{{ menu.nama }}</h6>
                <v-card-actions>
                  <p> Rp {{ formatPrice(menu.total) }}</p>
                  <v-spacer></v-spacer>
                  <button class="card-btn mx-3" @click="goToMenu(menu.id)">View Menu</button>
                  <button class="card-btn">Add to Cart</button>
                </v-card-actions>
              </v-card>
            </v-col>
          </v-row>
        </div>
      </section>
    </div>
  </div>
  <app-footer class="py-3 bg-white border-radius-lg" />
</template>

<style>
.rounded-card {
  border-radius: 15px;
  box-shadow: 1px 1px 15px #cccccc40;
  transition: 0.5s ease-in;
  background-color: white;
}

.rounded-card:hover {
  box-shadow: 1px 1px 28.5px -7px #d6d6d6;
}

.card-btn {
  border-color: transparent;
  color: rgb(60, 60, 60);
  font-weight: bold;
}

.card-btn:hover {
  border-color: transparent;
  color: #c9a938;
  transition: background-color 0.5s;
}

.nama-menu{
  border-color: transparent;
  color: rgb(60, 60, 60);
  font-weight: bold;
  font-size: 24px;
  margin-left: 10px;
  margin-top: 5px;
}

.a{
  border-color: transparent;
  color: rgb(60, 60, 60);
  font-weight: bold;
  font-size: 16px;
  margin-left: 10px;
}
</style>

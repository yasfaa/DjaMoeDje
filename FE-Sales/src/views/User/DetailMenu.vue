<template>
  <section class="navbar">
    <Navbar />
  </section>
  <div class="py-4 container-fluid gradient-container">
    <div class="container">
      <div class="row mt-5">
        <div class="card border-2 pt-3" v-if="menu.id">
          <v-carousel  hide-delimiters  class="carousel">
            <v-carousel-item v-for="(link, index) in fileLinks" :key="index">
              <v-img :src="link || 'https://via.placeholder.com/150' "></v-img>
            </v-carousel-item>
          </v-carousel>
          <div class="row p-2 pt-2 justify-content-center">
            <div class="col-md-9 d-flex flex-column">
              <a style="font-size: 32px; font-weight: bold">{{ menu.nama_menu }}</a>
              <div class="price" style="font-weight: bold; font-size: 24px">
                Rp. {{ formatPrice(menu.total) }}
              </div>
              <div class="theme-text subtitle">Deskripsi:</div>
              <div class="brief-description">
                {{ menu.deskripsi }}
              </div>
              <div class="mt-auto pb-2">
                <div class="row col-md-12">
                  <div class="col-md-5">
                    <div class="text-center">
                      <v-btn variant="tonal" class="m-2 button">Add to Cart</v-btn>
                      <v-btn variant="tonal" class="mx-4 button">Customize Menu</v-btn>
                    </div>
                  </div>
                </div>
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

<style>

.card {
  border-radius: 10px !important;
}

.carousel {
  max-height: 550px;
}
</style>

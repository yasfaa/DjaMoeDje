<template>
  <section class="navbar">
    <Navbar />
  </section>
  <div class="py-4 container-fluid gradient-container">
    <div class="container">
      <div class="row mt-5">
        <div class="col-lg-12 col-md-12">
          <div class="card border-2 pt-3" v-if="menu.id">
            <div class="row">
              <div class="col-md-4 order-1 order-md-1">
                <div id="menuCarousel" class="carousel slide p-3" data-bs-ride="carousel">
                  <div class="carousel-inner">
                    <div
                      v-for="(link, index) in fileLinks"
                      :key="index"
                      :class="['carousel-item', { active: index === 0 }]"
                    >
                      <div class="carousel-image-container">
                        <img
                          :src="link || 'https://via.placeholder.com/150'"
                          class="d-block w-100 carousel-image"
                          alt="Menu Image"
                        />
                      </div>
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
              </div>
              <div class="col-md-6 order-2 order-md-2">
                <div class="d-flex flex-column mx-5">
                  <h2 class="menu-title mt-2">{{ menu.nama_menu }}</h2>
                  <div class="menu-price">Rp. {{ formatPrice(menu.total) }}</div>
                  <div class="theme-text subtitle">Deskripsi:</div>
                  <div class="brief-description">{{ menu.deskripsi }}</div>
                </div>
              </div>
            </div>
            <Datatables />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
import Navbar from '@/components/DashboardNavbar.vue'
import Datatables from '@/components/Vuetify/DataTablesBahan.vue'

const BASE_URL = import.meta.env.VITE_BASE_URL_API

export default {
  components: {
    Navbar,
    Datatables
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
  background-color: white;
  margin: 1rem 0;
}

.carousel {
  max-height: 400px;
  max-width: 400px;
  border-radius: 10px;
  margin-bottom: 20px;
}

.carousel-image-container {
  position: relative;
  width: 100%;
  padding-bottom: 100%; /* This creates the 1:1 aspect ratio container */
  background-color: white; /* White background for the empty space */
}

.carousel-image {
  position: absolute;
  top: 50%;
  left: 50%;
  max-width: 100%;
  max-height: 100%;
  object-fit: contain; /* Ensure the image fits within the container without stretching */
  transform: translate(-50%, -50%);
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
    max-height: 350px;
    border-radius: 10px;
  }

  .order-1 {
    order: 1 !important;
  }

  .order-2 {
    order: 2 !important;
  }
}
</style>

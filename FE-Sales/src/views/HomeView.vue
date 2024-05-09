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

    const scrollWrapper = this.$refs.scrollWrapper
    if (scrollWrapper) {
      scrollWrapper.addEventListener('scroll', this.handleScroll)
    }
  },
  beforeMount() {
    const scrollWrapper = this.$refs.scrollWrapper
    if (scrollWrapper) {
      scrollWrapper.removeEventListener('scroll', this.handleScroll)
    }
  },
  methods: {
    formatPrice(price) {
      const numericPrice = parseFloat(price)
      return numericPrice.toLocaleString('id-ID')
    },
    scrollToNextSection() {
      // Scroll to the next section using JavaScript
      const nextSection = this.$refs.scrollWrapper.querySelector('.next-section')
      if (nextSection) {
        nextSection.scrollIntoView({ behavior: 'smooth' })
      }
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
  <main>
    <section class="navbar">
      <Navbar />
    </section>
    <div class="scroll-wrapper" ref="scrollWrapper">
      <section class="content py-5 text-center container">
        <div class="row py-lg-5">
          <div class="text-atas col-lg-6 col-md-8 mx-auto">
            <h1 class="fw-bold">DjaMoeDje</h1>
            <p class="lead">Spesialis Makanan Khas Surabaya dengan Cita Rasa yang Nikmat</p>
            <button class="scroll-button btn" @click="scrollToNextSection">Daftar Menu</button>
          </div>
        </div>
      </section>
      <section class="next-section py-5 bg-dark">
        <div class="container">
          <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
            <v-col v-for="menu in menus" :key="menu.id" cols="12" sm="6" md="4">
              <v-card hover @click="goToMenu(menu.id)">
                <v-img
                  :src="getMenuImage(menu.id)"
                  height="225"
                  gradient="to bottom, rgba(0,0,0,.1), rgba(0,0,0,.5)"
                >
                </v-img>
                <v-card-text class="white--text">{{ menu.nama }}</v-card-text>
                <v-card-actions>
                  <v-btn small color="primary" text @click="goToMenu(menu.id)">view Menu</v-btn>
                  <v-btn small color="primary" text>add to cart</v-btn>
                  <v-spacer></v-spacer>
                  <small class="text--secondary">Rp. {{ formatPrice(menu.total) }}</small>
                </v-card-actions>
              </v-card>
            </v-col>
          </div>
        </div>
      </section>
      <section>
        <footer class="bg-body-tertiary text-center text-lg-start">
          <div class="text-center p-3" style="background-color: #806407; color: white">
            © 2024 Copyright: Yasfa Ainun Abdullah
          </div>
        </footer>
      </section>
    </div>
  </main>
</template>
<style scoped>
.content {
  margin-top: 177px;
  margin-bottom: 100px;
  content: 250px;
  padding: 50px;
}

.text-atas {
  color: white;
}

.lead {
  color: white;
}

.scroll-button {
  background-color: #b9a119;
  border-color: transparent;
  color: white;
  font-weight: bold;
}

.scroll-button:hover {
  background-color: #806407;
  border-color: transparent;
  transition: background-color 0.5s;

  .black {
    transition: background-color 0.3s ease;
  }

  .black:hover {
    background-color: #adadad;
  }
}
</style>

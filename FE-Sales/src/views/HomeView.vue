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
            <v-col cols="12" sm="6" md="4">
              <v-card class="black" hover @click="$router.push('/menu')">
                <v-img
                  src="https://via.placeholder.com/150"
                  height="225"
                  class="white--text align-end"
                  gradient="to bottom, rgba(0,0,0,.1), rgba(0,0,0,.5)"
                >
                </v-img>
                <v-card-text class="white--text">
                  This is a wider card with supporting text below as a natural lead-in to additional
                  content. This content is a little bit longer.
                </v-card-text>
                <v-card-actions>
                  <v-btn small color="primary" text>View</v-btn>
                  <v-btn small color="primary" text>Edit</v-btn>
                  <v-spacer></v-spacer>
                  <small class="text--secondary">9 mins</small>
                </v-card-actions>
              </v-card>
            </v-col>
            <v-col cols="12" sm="6" md="4">
              <v-card hover>
                <v-img
                  src="https://via.placeholder.com/150"
                  height="225"
                  class="white--text align-end"
                  gradient="to bottom, rgba(0,0,0,.1), rgba(0,0,0,.5)"
                >
                </v-img>
                <v-card-text class="black">
                  This is a wider card with supporting text below as a natural lead-in to additional
                  content. This content is a little bit longer.
                </v-card-text>
                <v-card-actions>
                  <v-btn small color="primary" text>View</v-btn>
                  <v-btn small color="primary" text>Edit</v-btn>
                  <v-spacer></v-spacer>
                  <small class="text--secondary">9 mins</small>
                </v-card-actions>
              </v-card>
            </v-col>
            <v-col cols="12" sm="6" md="4">
              <v-card hover>
                <v-img
                  src="https://via.placeholder.com/150"
                  height="225"
                  class="white--text align-end"
                  gradient="to bottom, rgba(0,0,0,.1), rgba(0,0,0,.5)"
                >
                </v-img>
                <v-card-text>
                  This is a wider card with supporting text below as a natural lead-in to additional
                  content. This content is a little bit longer.
                </v-card-text>
                <v-card-actions>
                  <v-btn small color="primary" text>View</v-btn>
                  <v-btn small color="primary" text>Edit</v-btn>
                  <v-spacer></v-spacer>
                  <small class="text--secondary">9 mins</small>
                </v-card-actions>
              </v-card>
            </v-col>
          </div>
        </div>
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
    background-color: #adadad; /* Warna hitam yang lebih gelap untuk efek hover */
  }
}
</style>

<script>
import Navbar from '@/components/HomeNavbar.vue'
export default {
  name: 'HomePage',
  components: {
    Navbar
  },
  methods: {
    scrollToNextSection() {
      // Scroll to the next section using JavaScript
      const nextSection = this.$refs.scrollWrapper.querySelector('.next-section')
      if (nextSection) {
        nextSection.scrollIntoView({ behavior: 'smooth' })
      }
    }
  },
  mounted() {
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
  }
}
</script>

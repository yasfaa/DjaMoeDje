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
            <button class="scroll-button btn" @click="scrollToNextSection">
              Daftar Menu
            </button>
          </div>
        </div>
      </section>

      <section class="next-section py-5 bg-dark">
        <div class="container">
          <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
            <div class="col">
              <div class="card shadow-sm">
                <svg
                  class="bd-placeholder-img card-img-top"
                  width="100%"
                  height="225"
                  xmlns="http://www.w3.org/2000/svg"
                  role="img"
                  aria-label="Placeholder: Thumbnail"
                  preserveAspectRatio="xMidYMid slice"
                  focusable="false"
                >
                  <title>Placeholder</title>
                  <rect width="100%" height="100%" fill="#55595c" />
                  <text x="50%" y="50%" fill="#eceeef" dy=".3em">Thumbnail</text>
                </svg>

                <div class="card-body">
                  <p class="card-text">
                    This is a wider card with supporting text below as a natural lead-in to
                    additional content. This content is a little bit longer.
                  </p>
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="btn-group">
                      <button type="button" class="btn btn-sm btn-outline-secondary">View</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
                    </div>
                    <small class="text-muted">9 mins</small>
                  </div>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card shadow-sm">
                <svg
                  class="bd-placeholder-img card-img-top"
                  width="100%"
                  height="225"
                  xmlns="http://www.w3.org/2000/svg"
                  role="img"
                  aria-label="Placeholder: Thumbnail"
                  preserveAspectRatio="xMidYMid slice"
                  focusable="false"
                >
                  <title>Placeholder</title>
                  <rect width="100%" height="100%" fill="#55595c" />
                  <text x="50%" y="50%" fill="#eceeef" dy=".3em">Thumbnail</text>
                </svg>

                <div class="card-body">
                  <p class="card-text">
                    This is a wider card with supporting text below as a natural lead-in to
                    additional content. This content is a little bit longer.
                  </p>
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="btn-group">
                      <button type="button" class="btn btn-sm btn-outline-secondary">View</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
                    </div>
                    <small class="text-muted">9 mins</small>
                  </div>
                </div>
              </div>
            </div>
            <div class="col">
              <div class="card shadow-sm">
                <svg
                  class="bd-placeholder-img card-img-top"
                  width="100%"
                  height="225"
                  xmlns="http://www.w3.org/2000/svg"
                  role="img"
                  aria-label="Placeholder: Thumbnail"
                  preserveAspectRatio="xMidYMid slice"
                  focusable="false"
                >
                  <title>Placeholder</title>
                  <rect width="100%" height="100%" fill="#55595c" />
                  <text x="50%" y="50%" fill="#eceeef" dy=".4em">Thumbnail</text>
                </svg>

                <div class="card-body">
                  <p class="card-text">
                    This is a wider card with supporting text below as a natural lead-in to
                    additional content. This content is a little bit longer.
                  </p>
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="btn-group">
                      <button type="button" class="btn btn-sm btn-outline-secondary">View</button>
                      <button type="button" class="btn btn-sm btn-outline-secondary">Edit</button>
                    </div>
                    <small class="text-muted">9 mins</small>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
    </div>
  </main>
</template>

<style>
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

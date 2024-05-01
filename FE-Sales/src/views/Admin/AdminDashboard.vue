<!-- Catalogue.vue -->
<template>
  <main>
    <section class="navbar">
      <Navbar />
    </section>
    <div>
      <div class="container-fluid px-4 py-2">
        <div class="row py-5">
          <div class="col-md-12">
            <div class="card border-0 px-2" style="text-align: end">
              <div class="col text-right">
                <div class="button-set my-4">
                  <v-btn
                    color="#b9a119"
                    rounded="l"
                    data-bs-toggle="modal"
                    data-bs-target="#addMenu"
                    class=""
                  >
                    Tambah Menu
                  </v-btn>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-12 mt-4 mb-12">
            <Datatables ref="datatablesMenu" />
          </div>
        </div>
      </div>
      <!-- Modal Nich -->
      <div
        class="modal fade"
        id="addMenu"
        tabindex="-1"
        aria-labelledby="exampleModalLabel"
        aria-hidden="true"
      >
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header">
              <h1 class="modal-title fs-5" id="exampleModalLabel">Tambah Menu</h1>
              <button
                type="button"
                class="btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"
                id="closeModal"
              ></button>
            </div>
            <div class="modal-body">
              <form @submit.prevent="addMenu">
                <div class="mb-3">
                  <label for="exampleInputEmail1" class="form-label">Nama Menu</label>
                  <input
                    type="name"
                    class="form-control"
                    v-model="menu.name"
                    aria-describedby="namaCustomer"
                  />
                </div>
                <div class="mb-3">
                  <label for="exampleInputDesc" class="form-label">Deskripsi</label>
                  <textarea
                    type="text"
                    class="form-control"
                    v-model="menu.desc"
                    aria-describedby="emailCustomer"
                  ></textarea>
                </div>
                <div class="mb-3">
                  <label class="form-label">Tags</label>
                  <div class="form-check" v-for="tag in tags" :key="tag.id">
                    <input
                      class="form-check-input"
                      type="checkbox"
                      :value="tag.name"
                      v-model="selectedTags"
                    />
                    <label class="form-check-label">{{ tag.name }}</label>
                  </div>
                </div>
                <div class="mb-3">
                  <label for="exampleKategori" class="form-label">Kategori</label>
                  <select class="form-select" v-model="menu.category">
                    <option v-for="category in categories" :key="category.id" :value="category.id">
                      {{ category.name }}
                    </option>
                  </select>
                </div>
                <div class="mb-3">
                  <label for="FileInput" class="form-label">Gambar Menu</label>
                  <input
                    type="file"
                    class="form-control"
                    ref="fileInput"
                    @change="handleFileChange"
                    multiple
                  />
                </div>
                <div class="mb-3">
                  <label for="exampleInputHarga" class="form-label">Harga</label>
                  <input type="number" class="form-control" v-model="menu.price" />
                </div>
                <div class="mb-3">
                  <label for="exampleInputStock" class="form-label">Stok</label>
                  <input type="text" class="form-control" v-model="menu.stok" />
                </div>
              </form>
            </div>
            <div class="modal-footer">
              <button-custom type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                >Close</button-custom
              >
              <button-custom
                class="btn btn-info"
                type="submit"
                @click="addMenu"
                data-bs-dismiss="modal"
                >Tambah Menu</button-custom
              >
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>

<script>
import axios from 'axios'
import Navbar from '@/components/DashboardNavbar.vue'
import Datatables from '@/components/Vuetify/DataTablesProduk.vue'

const BASE_URL = import.meta.env.VITE_BASE_URL_API

export default {
  name: 'AdminDashboard',
  components: {
    Navbar,
    Datatables
  },
  data() {
    return {
      menu: {
        name: '',
        desc: '',
        price: '',
        stok: '',
        category: '',
        tag: ''
      },
      selectedFile: [],
      categories: [],
      tags: [],
      selectedTags: []
    }
  },

  mounted() {
    // this.fetchCategories()
    // this.retrieveTags()
  },

  methods: {
    // Prompt
    closeModal() {
      document.getElementById('closeModal').click()
    },

    clearForm() {
      this.menu.name = ''
      this.menu.desc = ''
      this.menu.price = ''
      this.menu.stok = ''
      this.menu.foto = ''
      ;(this.menu.category = ''), (this.$refs.fileInput.value = '')
    },

    // Method
    handleFileChange(event) {
      // Handle file change event
      const fileInput = this.$refs.fileInput
      this.selectedFiles = Array.from(fileInput.files)
    },

    async addMenu() {
      try {
        const formData = new FormData()
        this.selectedFiles.forEach((file, index) => {
          formData.append(`images[${index}]`, file, file.name)
        })

        formData.append('name', this.menu.name)
        formData.append('description', this.menu.desc)
        formData.append('price', this.menu.price)
        formData.append('stock', this.menu.stok)
        formData.append('category_id', this.menu.category)
        document.getElementById('closeModal').click()
        const token = localStorage.getItem('access_token')
        const response = await axios.post(BASE_URL + '/menu/get', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            Authorization: 'Bearer ' + token
          }
        })
        this.clearForm()
        this.selectedFiles = [] // Clear selected files
        this.selectedTags = []
        this.$refs.datatablesMenu.retrieveMenu()

        this.$notify({
          type: 'success',
          title: 'Success',
          text: response.data.message,
          color: 'green'
        })
      } catch (error) {
        console.error(error)
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
    }
  }
}
</script>

<style scoped>
.dashboard-admin {
  background-position: center;
  background-size: cover;
}

.card {
  margin-top: 30px;
}

.button-set {
  display: block;
  width: 100%;
  clear: both;
  margin: 15px 0;
}

button-custom,
.button {
  outline: 0 none;
  border: 0 none;
  padding: 13px 30px;
  background-color: #b9a119;
  background-position: 100% 0;
  background-size: 200% 200%;
  color: #fff;
  font-size: 12px;
  text-transform: uppercase;
  border-radius: 20px;
  letter-spacing: 2px;
  transition: 0.3s;
  cursor: pointer;
  text-decoration: none;
  transition: all 0.5s ease;

  &:hover {
    background-position: 0 0;
    transition: background-color 0.5s;
  }
}
</style>

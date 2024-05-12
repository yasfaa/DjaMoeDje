<!-- Catalogue.vue -->
<template>
  <main>
    <section class="navbar">
      <Navbar />
    </section>
    <div>
      <div class="container-fluid px-4 py-2">
        <div class="row py-5">
          <div class="mt-3">
            <Datatables ref="datatablesMenu" />
          </div>
        </div>
      </div>
      <!-- Modal -->
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
                  <label for="InputNamaMenu" class="form-label">Nama Menu</label>
                  <input
                    type="name"
                    class="form-control"
                    v-model="menu.name"
                    aria-describedby="namaCustomer"
                  />
                </div>
                <div class="mb-3">
                  <label for="InputDeskripsi" class="form-label">Deskripsi</label>
                  <textarea
                    type="text"
                    class="form-control"
                    v-model="menu.desc"
                    aria-describedby="emailCustomer"
                  ></textarea>
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
                  <input type="price" class="form-control" v-model="menu.price" />
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
        price: ''
      },
      selectedFile: []
    }
  },

  mounted() {
    
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
      this.menu.foto = ''
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
          formData.append(`gambar[${index}]`, file, file.name)
        })

        formData.append('nama_menu', this.menu.name)
        formData.append('deskripsi', this.menu.desc)
        formData.append('total', this.menu.price)
        document.getElementById('closeModal').click()
        const token = localStorage.getItem('access_token')
        const response = await axios.post(BASE_URL + '/menu/add', formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            Authorization: 'Bearer ' + token
          }
        })
        this.clearForm()
        this.selectedFiles = [] // Clear selected files
        this.selectedTags = []
        this.$refs.datatablesMenu.retrieveMenus()

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

.form-label {
  color: #63560c;
}

.modal-title {
  color: #63560c;
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

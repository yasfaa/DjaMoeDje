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
            <Datatables @open-dialog="openDialog" />
          </div>
        </div>
      </div>
      <!-- Modal -->
      <v-dialog v-model="dialog" max-width="600">
        <v-card>
          <v-card-title>
            <span class="text-h5">Tambah Menu</span>
          </v-card-title>
          <v-spacer></v-spacer>
          <div class="modal-content">
            <div class="modal-body mx-3">
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
                  <input type="number" class="form-control" v-model="menu.price" />
                </div>
              </form>
            </div>
          </div>
          <v-card-actions>
            <v-spacer></v-spacer>
            <button class="btn btn-primary mx-2" @click="closeDialog">Close</button>
            <button
              class="btn btn-primary mx-1"
              type="submit"
              @onclick="addMenu"
              data-bs-dismiss="modal"
            >
              Tambah Menu
            </button>
          </v-card-actions>
        </v-card>
      </v-dialog>
      <div class="modal-dialog"></div>
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
      selectedFile: [],
      dialog: false
    }
  },

  mounted() {},

  methods: {
    openDialog() {
      this.dialog = true
    },
    closeDialog() {
      this.clearForm()
      this.dialog = false
    },

    clearForm() {
      this.menu.name = ''
      this.menu.desc = ''
      this.menu.price = ''
      this.menu.foto = ''
    },

    handleFileChange(event) {
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



input[type='number'] {
  -moz-appearance: textfield;
}

.btn-primary {
  background-color: #ffe279;
  color: black;
  border: none;
}

.btn-primary:hover {
  background-color: #e5c54f;
}
</style>

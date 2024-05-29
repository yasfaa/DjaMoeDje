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
      <!-- Dialog -->
      <v-dialog v-model="dialog" max-width="450">
        <v-card class="padding-container form-box">
          <v-form @submit.prevent="addMenu">
            <h2>Tambah Menu</h2>
            <div class="mb-3">
              <label for="InputNamaMenu" class="form-label">Nama Menu</label>
              <input
                type="text"
                class="form-control"
                v-model="menu.name"
                aria-describedby="namaCustomer"
                required
              />
            </div>
            <div class="mb-3">
              <label for="InputDeskripsi" class="form-label">Deskripsi</label>
              <textarea
                type="text"
                class="form-control"
                v-model="menu.desc"
                aria-describedby="emailCustomer"
                required
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
              <input type="number" class="form-control" v-model="menu.price" required />
            </div>
            <div class="modal-footer">
              <button class="btn btn-primary mx-4" @click="closeDialog">Cancel</button>
              <button class="btn btn-primary" type="submit">Tambah Menu</button>
            </div>
          </v-form>
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

.container {
  padding: 20px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.padding-container {
  padding: 20px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.form-box {
  position: relative;
  width: 450px;
  height: auto;
  background: #fffcf1;
  display: flex;
  justify-content: center;
  align-items: center;
}

.form-label {
  color: #63560c;
}

h2 {
  font-size: 2em;
  color: #63560c;
  text-align: center;
}

.label {
  position: absolute;
  top: 50%;
  left: 5px;
  transform: translateY(-50%);
  color: rgb(60, 60, 60);
  font-size: 1em;
  pointer-events: none;
  transition: 0.5s;
}

.input {
  width: 100%;
  height: 120px;
  background: transparent;
  border: none;
  outline: none;
  font-size: 1em;
  padding: 0 35px 0 5px;
  color: rgb(60, 60, 60);
}

.btn {
  font-size: 1.1rem;
  border-radius: 5px;
  transition: background-color 0.3s;
}

.btn-primary {
  background-color: #ffe279;
  color: #19160c;
  border: none;
}

.btn-primary:hover {
  background-color: #e5c54f;
}
</style>

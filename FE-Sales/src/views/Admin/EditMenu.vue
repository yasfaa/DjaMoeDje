<template>
  <main>
    <section class="navbar">
      <Navbar />
    </section>
    <div class="container-fluid px-4 py-2">
      <div class="row py-5">
        <div class="mt-3">
          <v-form @submit.prevent="updateMenu">
            <div class="mb-3">
              <label for="InputNamaMenu" class="form-label">Nama Menu</label>
              <input
                type="text"
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
            <div class="modal-footer">
              <button class="btn btn-primary" @click="cancelEdit">Cancel</button>
              <button class="btn btn-primary mx-4" type="submit">Update Menu</button>
            </div>
          </v-form>
        </div>
      </div>
    </div>
  </main>
</template>

<script>
import axios from 'axios'
import Navbar from '@/components/DashboardNavbar.vue'

export default {
  name: 'EditMenu',
  components: {
    Navbar
  },
  data() {
    return {
      BASE_URL: import.meta.env.VITE_BASE_URL_API,
      menu: {
        name: '',
        desc: '',
        price: '',
        images: []
      },
      selectedFiles: []
    }
  },
  async mounted() {
    const menuId = localStorage.getItem('editMenuId')
    if (menuId) {
      try {
        const response = await axios.get(`${this.BASE_URL}/menu/get/${menuId}`, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })
        const menu = response.data.menu
        this.menu.name = menu.nama_menu
        this.menu.desc = menu.deskripsi
        this.menu.price = menu.total
        this.menu.images = response.data.fileLinks
      } catch (error) {
        console.error('Error fetching menu:', error)
      }
    }
  },
  methods: {
    handleFileChange(event) {
      this.selectedFiles = Array.from(event.target.files)
    },
    async updateMenu() {
      const menuId = localStorage.getItem('editMenuId')
      if (menuId) {
        try {
          const formData = new FormData()
          this.selectedFiles.forEach((file, index) => {
            formData.append(`gambar[${index}]`, file, file.name)
          })
          formData.append('nama_menu', this.menu.name)
          formData.append('deskripsi', this.menu.desc)
          formData.append('total', this.menu.price)

          const response = await axios.post(`${this.BASE_URL}/menu/update/${menuId}`, formData, {
            headers: {
              'Content-Type': 'multipart/form-data',
              Authorization: 'Bearer ' + localStorage.getItem('access_token')
            }
          })

          this.$notify({
            type: 'success',
            title: 'Success',
            text: 'Menu berhasil diperbarui',
            color: 'green'
          })
          localStorage.removeItem('editMenuId')
          this.$router.push('/admin/dashboard')
        } catch (error) {
          console.error('Error updating menu:', error)
        }
      }
    },
    cancelEdit() {
      localStorage.removeItem('editMenuId')
      this.$router.push('/admin/dashboard')
    }
  }
}
</script>

<style scoped>
.form-label {
  color: #63560c;
}

.modal-title {
  color: #63560c;
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

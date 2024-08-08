<template>
  <main>
    <section class="navbar">
      <Navbar />
    </section>
    <div class="container">
      <div class="form-box">
        <div class="padding-container">
          <div class="row">
            <v-form @submit.prevent="updateMenu">
              <h2>Edit Menu</h2>
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
                <label for="InputStok" class="form-label">Stok Harian</label>
                <input id="InputStok" type="number" class="form-control" v-model="menu.stock" />
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
                <button class="btn btn-primary mx-4" @click="cancelEdit">Cancel</button>
                <button class="btn btn-primary" type="submit">Update Menu</button>
              </div>
            </v-form>
          </div>
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
        stock: '',
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
        this.menu.stock = menu.stok_harian
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
          formData.append('stok_harian', this.menu.stock)

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
  background: #fff4cc;
  border: 1px solid rgba(255, 237, 171, 0.5);
  border-radius: 10px;
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

<template>
  <main>
    <section class="navbar">
      <Navbar />
    </section>
    <div>
      <div class="container-fluid px-4 py-2">
        <div class="row py-5">
          <div class="mt-3">
            <Datatables @open-dialog="openDialog" ref="datatables" />
          </div>
        </div>
      </div>
      <!-- Add Menu Dialog -->
      <v-dialog v-model="dialog1" max-width="450">
        <v-card class="padding-container form-box">
          <v-form @submit.prevent="addMenu">
            <h2>Tambah Menu</h2>
            <div class="mb-3">
              <label for="InputNamaMenu" class="form-label">Nama Menu</label>
              <input id="InputNamaMenu" type="text" class="form-control" v-model="menu.name" />
            </div>
            <div class="mb-3">
              <label for="InputDeskripsi" class="form-label">Deskripsi</label>
              <textarea id="InputDeskripsi" class="form-control" v-model="menu.desc"></textarea>
            </div>
            <div class="mb-3">
              <label for="FileInput" class="form-label">Gambar Menu</label>
              <input
                id="FileInput"
                type="file"
                class="form-control"
                @change="handleFileChange"
                multiple
              />
            </div>
            <div class="mb-3">
              <label for="exampleInputHarga" class="form-label">Harga</label>
              <input
                id="exampleInputHarga"
                type="number"
                class="form-control"
                v-model="menu.price"
              />
            </div>
            <div class="modal-footer">
              <button class="btn btn-primary mx-4" @click="closeDialog">Cancel</button>
              <button class="btn btn-primary" type="submit">Tambah Menu</button>
            </div>
          </v-form>
        </v-card>
      </v-dialog>
      <!-- Add Ingredients Dialog -->
      <v-dialog v-model="dialog2" max-width="450">
        <v-card class="padding-container form-box">
          <v-form @submit.prevent="addAllIngredients">
            <h2>Tambah Bahan</h2>
            <div class="ingredient-tags">
              <v-chip
                v-for="(ingredient, index) in ingredients"
                :key="index"
                :class="{ 'selected-card': selectedIngredientIndex === index }"
                class="ma-2"
                close
                @click="editIngredient(index)"
                @click:close="removeIngredient(index)"
              >
                {{ ingredient.name }} | {{ ingredient.price }} |
                <v-icon size="small" @click.stop="removeIngredient(index)">mdi-delete</v-icon>
              </v-chip>
            </div>
            <div class="mb-3">
              <label for="InputNamaBahan" class="form-label">Nama Bahan</label>
              <input
                id="InputNamaBahan"
                type="text"
                class="form-control"
                v-model="ingredient.name"
              />
            </div>
            <div class="mb-3">
              <label for="InputHargaBahan" class="form-label">Harga Bahan</label>
              <input
                id="InputHargaBahan"
                type="number"
                class="form-control"
                v-model="ingredient.price"
              />
            </div>
            <v-icon
              size="large"
              class="justify-content-center"
              @click="addOrUpdateIngredient"
              color="blue"
            >
              mdi-plus
            </v-icon>
            <div class="modal-footer">
              <button class="btn btn-primary me-4 justify-content-start" @click="closeDialog">
                Batal
              </button>
              <button class="btn btn-primary" type="submit">Simpan</button>
            </div>
          </v-form>
        </v-card>
      </v-dialog>
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
      ingredient: { name: '', price: '' },
      ingredients: [],
      selectedFiles: [],
      dialog1: false,
      dialog2: false,
      menuId: null,
      selectedIngredientIndex: null
    }
  },
  methods: {
    openDialog() {
      this.dialog1 = true
    },
    closeDialog() {
      this.clearForm()
      this.dialog1 = false
      this.dialog2 = false
    },
    clearForm() {
      this.menu.name = ''
      this.menu.desc = ''
      this.menu.price = ''
      this.selectedFiles = []
      this.ingredient.name = ''
      this.ingredient.price = ''
      this.ingredients = []
      this.menuId = null
      this.selectedIngredientIndex = null
    },
    handleFileChange(event) {
      this.selectedFiles = Array.from(event.target.files)
    },
    async addMenu() {
      try {
        if (
          !this.menu.name ||
          !this.menu.desc ||
          !this.menu.price ||
          this.selectedFiles.length === 0
        ) {
          this.$notify({
            type: 'error',
            title: 'Error',
            text: 'All fields are required',
            color: 'red'
          })
          return
        }

        const formData = new FormData()
        this.selectedFiles.forEach((file, index) => {
          formData.append(`gambar[${index}]`, file, file.name)
        })

        formData.append('nama_menu', this.menu.name)
        formData.append('deskripsi', this.menu.desc)
        formData.append('total', this.menu.price)

        const token = localStorage.getItem('access_token')
        const response = await axios.post(`${BASE_URL}/menu/add`, formData, {
          headers: {
            'Content-Type': 'multipart/form-data',
            Authorization: `Bearer ${token}`
          }
        })

        this.menuId = response.data.menu.id // Assuming the response contains the saved menu ID
        this.$refs.datatables.retrieveMenus()

        this.$notify({
          type: 'success',
          title: 'Success',
          text: response.data.message,
          color: 'green'
        })
        this.dialog1 = false
        this.dialog2 = true
      } catch (error) {
        console.error(error)
        const errorMessage = error.response?.data.message || 'An error occurred'
        this.$notify({
          type: 'error',
          title: 'Error',
          text: errorMessage,
          color: 'red'
        })
      }
    },
    addOrUpdateIngredient() {
      if (!this.ingredient.name || !this.ingredient.price) {
        this.$notify({
          type: 'error',
          title: 'Error',
          text: 'Both ingredient name and price are required',
          color: 'red'
        })
        return
      }

      if (this.selectedIngredientIndex !== null) {
        this.ingredients.splice(this.selectedIngredientIndex, 1, { ...this.ingredient })
      } else {
        this.ingredients.push({ ...this.ingredient })
      }

      this.ingredient.name = ''
      this.ingredient.price = ''
      this.selectedIngredientIndex = null
    },
    editIngredient(index) {
      this.ingredient = { ...this.ingredients[index] }
      this.selectedIngredientIndex = index
    },
    removeIngredient(index) {
      this.ingredients.splice(index, 1)
      if (this.selectedIngredientIndex === index) {
        this.ingredient.name = ''
        this.ingredient.price = ''
        this.selectedIngredientIndex = null
      } else if (this.selectedIngredientIndex > index) {
        this.selectedIngredientIndex--
      }
    },
    async addAllIngredients() {
      try {
        if (this.ingredients.length === 0) {
          this.$notify({
            type: 'error',
            title: 'Error',
            text: 'No ingredients to add',
            color: 'red'
          })
          return
        }

        const token = localStorage.getItem('access_token')

        for (const ingredient of this.ingredients) {
          const formData = new FormData()
          formData.append('nama_bahan', ingredient.name)
          formData.append('harga_bahan', ingredient.price)
          formData.append('menu_id', this.menuId)

          await axios.post(`${BASE_URL}/ingredient/add`, formData, {
            headers: {
              'Content-Type': 'multipart/form-data',
              Authorization: `Bearer ${token}`
            }
          })
        }

        this.clearForm()
        this.$refs.datatables.retrieveMenus()

        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Ingredients added successfully',
          color: 'green'
        })
        this.dialog2 = false
      } catch (error) {
        console.error(error)
        const errorMessage = error.response?.data.message || 'An error occurred'
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

.ingredient-tags {
  display: flex;
  flex-wrap: wrap;
  margin-bottom: 1em;
}

.v-chip {
  margin-right: 5px;
  margin-bottom: 5px;
  background-color: #f0e68c;
  color: #63560c;
}

.v-chip__close {
  color: #63560c;
}

.selected-card {
  border: 2px solid #e5c54f;
  background-color: #4c4324;
  color: white;
}

.delete-icon {
  cursor: pointer;
  margin-left: auto;
}
</style>

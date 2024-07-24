<template>
  <v-card flat>
    <v-card-title class="d-flex align-center pe-0">
      Bahan
      <v-spacer></v-spacer>
      <v-text-field
        class="search-input"
        v-model="search"
        density="compact"
        label="Search"
        variant="solo-filled"
        flat
        hide-details
        single-line
      ></v-text-field>
      <div class="button-set mx-4">
        <button class="btn btn-primary" @click="openAddDialog">Tambah Bahan</button>
      </div>
    </v-card-title>
    <v-divider></v-divider>
    <v-data-table v-model:search="search" :items="menus">
      <template v-slot:item.id="{ item }">
        <div v-if="false">{{ item.id }}</div>
      </template>
      <template v-slot:header.id> </template>
      <template v-slot:item.no="{ item }">
        <div class="text-start">
          {{ item.no }}
        </div>
      </template>
      <template v-slot:item.nama="{ item }">
        <div class="text-start">{{ item.nama }}</div>
      </template>
      <template v-slot:item.harga="{ item }">
        <div class="text-start">Rp. {{ formatPrice(item.harga) }}</div>
      </template>
      <template v-slot:item.actions="{ item }">
        <v-icon size="large" class="me-2" @click="openEditDialog(item)" color="blue">
          mdi-pencil
        </v-icon>
        <v-icon size="large" @click="confirmDelete(item)" color="red"> mdi-delete </v-icon>
      </template>
    </v-data-table>
    <v-dialog v-model="dialogDelete" max-width="500px">
      <v-card>
        <v-card-title>Konfirmasi Hapus</v-card-title>
        <v-card-text>Anda yakin ingin menghapus bahan ini?</v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="dialogDelete = false">Batal</v-btn>
          <v-btn color="red" @click="deleteBahan">Hapus</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogAdd" max-width="450">
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
            <input id="InputNamaBahan" type="text" class="form-control" v-model="ingredient.name" />
          </div>
          <div class="mb-3">
            <label for="InputHargaBahan" class="form-label">Harga Bahan</label>
            <input
              id="InputHargaBahan"
              type="number"
              class="form-control"
              v-model="ingredient.price"
            />
            <a style="font-size: 11px; color: red"
              ><i class="fas fa-info-circle" style="color: #ff0000"></i>&nbsp;Masukkan harga jika
              mempengaruhi harga menu</a
            >
          </div>
          <button type="button" class="btn btn-primary mb-5 w-100" @click="addOrUpdateIngredient">
            Tambah Bahan Lainnya
          </button>
          <div class="modal-footer">
            <button
              class="btn btn-primary me-4 justify-content-start"
              type="button"
              @click="closeAddDialog"
            >
              Batal
            </button>
            <button class="btn btn-primary" type="submit">Simpan</button>
          </div>
        </v-form>
      </v-card>
    </v-dialog>
    <v-dialog v-model="dialogEdit" max-width="450">
      <v-card class="padding-container form-box">
        <v-form @submit.prevent="saveEditedIngredient">
          <h2>Edit Bahan</h2>
          <div class="mb-3">
            <label for="EditNamaBahan" class="form-label">Nama Bahan</label>
            <input id="EditNamaBahan" type="text" class="form-control" v-model="ingredient.name" />
          </div>
          <div class="mb-3">
            <label for="EditHargaBahan" class="form-label">Harga Bahan</label>
            <input
              id="EditHargaBahan"
              type="number"
              class="form-control"
              v-model="ingredient.price"
            />
            <a style="font-size: 11px; color: red"
              ><i class="fas fa-info-circle" style="color: #ff0000"></i>&nbsp;Masukkan harga jika
              mempengaruhi harga menu</a
            >
          </div>
          <div class="modal-footer">
            <button class="btn me-4 justify-content-start" type="button" @click="closeEditDialog">
              Batal
            </button>
            <button class="btn btn-primary" type="submit">Simpan</button>
          </div>
        </v-form>
      </v-card>
    </v-dialog>
  </v-card>
</template>

<script>
import axios from 'axios'

export default {
  data() {
    return {
      BASE_URL: import.meta.env.VITE_BASE_URL_API,
      search: '',
      ingredient: { name: '', price: '' },
      ingredients: [],
      itemToDeleteId: null,
      dialogAdd: false,
      dialogEdit: false,
      dialogDelete: false,
      menus: [],
      selectedIngredientIndex: null,
      menuId: localStorage.getItem('editMenuId')
    }
  },
  mounted() {
    this.retrieveBahans()
  },
  computed: {
    getitems() {
      return this.menus.map((item, index) => {
        return { ...item, no: index + 1 }
      })
    }
  },
  methods: {
    openAddDialog() {
      this.clearForm()
      this.dialogAdd = true
    },
    openEditDialog(item) {
      this.ingredient = { name: item.nama, price: item.harga }
      this.selectedIngredientIndex = this.menus.findIndex((i) => i.id === item.id)
      this.dialogEdit = true
    },
    closeAddDialog() {
      this.clearForm()
      this.dialogAdd = false
    },
    closeEditDialog() {
      this.clearForm()
      this.dialogEdit = false
    },
    async retrieveBahans() {
      try {
        const response = await axios.get(`${this.BASE_URL}/ingredient/get/${this.menuId}`, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })
        this.menus = response.data.map((ingredient, index) => ({
          id: ingredient.id,
          no: index + 1,
          nama: ingredient.nama,
          harga: parseFloat(ingredient.harga).toFixed(2),
          actions: ''
        }))
      } catch (error) {
        console.error('Error fetching menus:', error)
      }
    },
    formatPrice(price) {
      const numericPrice = parseFloat(price)
      return numericPrice.toLocaleString('id-ID')
    },
    confirmDelete(item) {
      this.itemToDeleteId = item.id
      this.dialogDelete = true
    },
    async deleteBahan() {
      try {
        await axios.delete(this.BASE_URL + '/ingredient/delete/' + this.itemToDeleteId, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })
        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Bahan berhasil dihapus',
          color: 'green'
        })
        this.retrieveBahans()
        this.dialogDelete = false
        this.itemToDeleteId = null
      } catch (error) {
        console.error(error)
      }
    },
    addOrUpdateIngredient() {
      if (!this.ingredient.name) {
        this.$notify({
          type: 'error',
          title: 'Error',
          text: 'Nama bahan harus terisi',
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
    removeIngredient(index) {
      this.ingredients.splice(index, 1)
      if (this.selectedIngredientIndex === index) {
        this.ingredient.name = ''
        this.ingredient.price = ''
        this.selectedIngredientIndex = null
      }
    },
    editIngredient(index) {
      this.ingredient = { ...this.ingredients[index] }
      this.selectedIngredientIndex = index
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

          await axios.post(this.BASE_URL + '/ingredient/add', formData, {
            headers: {
              'Content-Type': 'multipart/form-data',
              Authorization: 'Bearer ' + localStorage.getItem('access_token')
            }
          })
        }

        this.retrieveBahans()
        this.dialogAdd = false
        this.clearForm()

        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Ingredients added successfully',
          color: 'green'
        })
      } catch (error) {
        console.error(error)
        const errorMessage = error.response?.data.message || 'An error occurred'
        this.$notify({
          type: 'error',
          title: 'Error',
          text: 'Gagal menambahkan bahan',
          color: 'red'
        })
      }
    },
    async saveEditedIngredient() {
      try {
        const payload = {
          nama_bahan: this.ingredient.name,
          harga_bahan: this.ingredient.price
        }
        const response = await axios.put(
          `${this.BASE_URL}/ingredient/update/${this.menus[this.selectedIngredientIndex].id}`,
          payload,
          {
            headers: {
              Authorization: 'Bearer ' + localStorage.getItem('access_token')
            }
          }
        )
        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Bahan berhasil diperbarui',
          color: 'green'
        })
        this.retrieveBahans()
        this.dialogEdit = false
        this.clearForm()
      } catch (error) {
        console.error(error)
        this.$notify({
          type: 'error',
          title: 'Error',
          text: 'Gagal memperbarui bahan',
          color: 'red'
        })
      }
    },
    clearForm() {
      this.ingredient.name = ''
      this.ingredient.price = ''
      this.selectedIngredientIndex = null
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
  width: 350px;
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

.modal-footer {
  display: flex;
  justify-content: space-between;
}

.cancel-button {
  margin-left: 0;
}

.button-set {
  display: flex;
  gap: 10px;
}

.v-data-table .v-data-table__actions {
  display: flex;
  justify-content: center;
  align-items: center;
}

.v-icon {
  cursor: pointer;
}

@media (max-width: 600px) {
  .search-input {
    display: none;
  }

  .form-box {
    position: relative;
    width: 95%;
    height: auto;
    background: #fffcf1;
    display: flex;
    justify-content: center;
    align-items: center;
  }
}
</style>

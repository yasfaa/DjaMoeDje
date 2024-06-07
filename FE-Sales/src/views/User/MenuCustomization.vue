<template>
  <navbar />
  <div class="mt-6 p-4 container">
    <v-card>
      <v-card-title>Kustomisasi Menu</v-card-title>
      <v-card-text class="py-2">
        <div v-for="ingredient in ingredients" :key="ingredient.id" class="ingredient-item">
          <div class="d-flex py-4 align-items-center" style="height: 70px">
            <input
              type="checkbox"
              class="checkbox"
              v-model="ingredient.selected"
              @change="toggleSelectIngredient()"
            />
            <span class="ms-2 text" :class="{ 'text-disabled': !ingredient.selected }">
              {{ ingredient.nama }} - Rp {{ formatPrice(ingredient.harga) }}
            </span>
            <div
              class="row quantity-controls align-items-center ml-auto"
              v-if="ingredient.selected"
            >
              <v-icon
                icon="mdi-minus"
                class="icon-btn"
                @click="decrementIngredientQuantity(ingredient.id, ingredient.quantity)"
              ></v-icon>
              <input
                type="number"
                v-model.number="ingredient.quantity"
                class="form-control text-center quantity-input mx-2"
                style="width: 45px"
              />
              <v-icon
                icon="mdi-plus"
                class="icon-btn"
                @click="incrementIngredientQuantity(ingredient.id, ingredient.quantity)"
              ></v-icon>
            </div>
          </div>
        </div>
      </v-card-text>
    </v-card>
  </div>
  <div class="row sticky-bottom justify-content-center">
    <div class="row footer-content mb-2">
      <div class="d-flex justify-content-between align-items-center">
        <strong>Harga Menu: Rp {{ formatPrice(menuPrice) }}</strong>
        <div>
          <v-tooltip text="Cancel" location="top">
            <template v-slot:activator="{ props }">
              <v-icon v-bind="props" class="mx-2" size="large" @click="hapus" color="red">
                mdi-delete
              </v-icon>
            </template>
          </v-tooltip>
          <button class="btn btn-primary" @click="simpan">Simpan</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'
const BASE_URL = import.meta.env.VITE_BASE_URL_API
import Navbar from '@/components/DashboardNavbar.vue'

export default {
  components: {
    Navbar
  },
  data() {
    return {
      ingredients: [],
      menuId: this.$route.params.menuId,
      cartItemId: null,
      menuPrice: 0
    }
  },
  mounted() {
    this.retrieveIngredients()
    this.retrieveMenuPrice()
  },
  methods: {
    formatPrice(price) {
      const numericPrice = parseFloat(price)
      return numericPrice.toLocaleString('id-ID')
    },
    async retrieveIngredients() {
      try {
        const response = await axios.get(`${BASE_URL}/ingredient/get/${this.menuId}`, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })

        this.ingredients = response.data.map((ingredient) => ({
          ...ingredient,
          selected: true,
          quantity: 1 // Default quantity
        }))
      } catch (error) {
        console.error(error)
      }
    },
    async retrieveMenuPrice() {
      try {
        const response = await axios.get(`${BASE_URL}/menu/get/${this.menuId}`, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })
        this.menuPrice = response.data.menu.total
      } catch (error) {
        console.error(error)
      }
    },
    async toggleSelectIngredient() {
      await this.saveCustomizations()
    },
    async updateIngredientQuantity(ingredientId, quantity) {
      if (quantity < 0) return
      const ingredient = this.ingredients.find((ing) => ing.id === ingredientId)
      ingredient.quantity = quantity
      await this.saveCustomizations()
    },
    incrementIngredientQuantity(ingredientId, currentQuantity) {
      const ingredient = this.ingredients.find((ing) => ing.id === ingredientId)
      const newQuantity = currentQuantity + 1
      ingredient.quantity = newQuantity
      this.updateIngredientQuantity(ingredientId, newQuantity)
    },
    decrementIngredientQuantity(ingredientId, currentQuantity) {
      const ingredient = this.ingredients.find((ing) => ing.id === ingredientId)
      if (currentQuantity > 1) {
        const newQuantity = currentQuantity - 1
        ingredient.quantity = newQuantity
        this.updateIngredientQuantity(ingredientId, newQuantity)
      }
    },
    async saveCustomizations() {
      try {
        if (!this.cartItemId) {
          await this.addCustomizationMenu()
        } else {
          await this.addCustomizationCart()
        }
      } catch (error) {
        console.error(error)
      }
    },
    async addCustomizationMenu() {
      const customizations = this.ingredients.map((ingredient) => ({
        ingredient_id: ingredient.id,
        quantity: ingredient.selected ? ingredient.quantity : 0
      }))
      try {
        const response = await axios.post(
          `${BASE_URL}/customize/menu/${this.menuId}`,
          { customizations },
          {
            headers: {
              Authorization: 'Bearer ' + localStorage.getItem('access_token')
            }
          }
        )
        this.cartItemId = response.data.id
        this.menuPrice = response.data.harga_dasar
      } catch (error) {
        console.error(error)
      }
    },
    async simpan() {
      this.$router.push('/cart')
    },
    async addCustomizationCart() {
      const customizations = this.ingredients.map((ingredient) => ({
        ingredient_id: ingredient.id,
        quantity: ingredient.selected ? ingredient.quantity : 0
      }))
      try {
        const response = await axios.post(
          `${BASE_URL}/customize/cart/${this.cartItemId}`,
          { customizations },
          {
            headers: {
              Authorization: 'Bearer ' + localStorage.getItem('access_token')
            }
          }
        )
        this.menuPrice = response.data.harga_dasar
        // Redirect to cart after saving
      } catch (error) {
        console.error(error)
      }
    },
    async hapus() {
      if (!this.cartItemId) return

      try {
        await axios.delete(`${BASE_URL}/cart/delete/${this.cartItemId}`, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })
        this.cartItemId = null // Reset cartItemId setelah penghapusan berhasil
        this.$router.push(`/menu/${this.menuId}`) // Arahkan pengguna ke halaman menu setelah penghapusan
      } catch (error) {
        console.error(error)
      }
    }
  }
}
</script>

<style>
.text-disabled {
  color: grey;
}

.ingredient-item {
  margin-bottom: 15px;
}

.icon-btn {
  cursor: pointer;
}

.sticky-footer {
  position: sticky;
  bottom: 0;
  width: 100%;
  background-color: white;
  padding: 1rem 0;
  box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
  display: flex;
  justify-content: center;
}

.footer-content {
  width: 80%;
  border-radius: 10px;
  padding: 1rem;
  color: black;
  background-color: #f8f9fa;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

.text {
  font-size: 1.2rem;
  font-weight: bold;
}

.checkbox {
  width: 1.3rem;
  height: 1.3rem;
}
</style>

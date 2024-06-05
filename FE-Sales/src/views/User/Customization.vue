<template>
  <navbar />
  <div class="py-4 container">
    <v-card>
      <v-card-title>Kustomisasi Item</v-card-title>
      <v-card-text>
        <div v-for="ingredient in ingredients" :key="ingredient.id" class="ingredient-item">
          <div class="d-flex align-items-center">
            <input
              type="checkbox"
              v-model="ingredient.selected"
              @change="toggleSelectIngredient(ingredient.id, ingredient.selected)"
            />
            <span :class="{ 'text-disabled': !ingredient.selected }">
              {{ ingredient.nama }} - Rp {{ formatPrice(ingredient.harga) }}
            </span>
            <div class="quantity-controls ml-auto" v-if="ingredient.selected">
              <v-icon
                icon="mdi-minus"
                class="icon-btn"
                @click="decrementIngredientQuantity(ingredient.id, ingredient.quantity)"
              ></v-icon>
              <input
                type="number"
                v-model.number="ingredient.quantity"
                @change="updateIngredientQuantity(ingredient.id, ingredient.quantity)"
                class="form-control text-center quantity-input mx-2"
                style="width: 55px"
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
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="primary" @click="saveCustomizations">Simpan</v-btn>
      </v-card-actions>
    </v-card>
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
      cartItemId: this.$route.params.id
    }
  },
  mounted() {
    this.retrieveIngredients()
    this.retrieveCustomizations()
  },
  methods: {
    formatPrice(price) {
      const numericPrice = parseFloat(price)
      return numericPrice.toLocaleString('id-ID')
    },
    async retrieveIngredients() {
      try {
        const response = await axios.get(`${BASE_URL}/ingredient/getCart/${this.cartItemId}`, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })

        this.ingredients = response.data.map((ingredient) => ({
          ...ingredient,
          selected: false,
          quantity: 1 // Default quantity
        }))
      } catch (error) {
        console.error(error)
      }
    },
    async retrieveCustomizations() {
      try {
        const response = await axios.get(`${BASE_URL}/cart/customize/get/${this.cartItemId}`, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })

        const customizations = response.data.cart_item_ingredients
        customizations.forEach((custom) => {
          const ingredient = this.ingredients.find((ing) => ing.id === custom.ingredient_id)
          if (ingredient) {
            ingredient.selected = true
            ingredient.quantity = custom.quantity
          }
        })
      } catch (error) {
        console.error(error)
      }
    },
    async updateIngredientQuantity(ingredientId, quantity) {
      if (quantity < 0) return
      const ingredient = this.ingredients.find((ing) => ing.id === ingredientId)
      ingredient.quantity = quantity
    },
    incrementIngredientQuantity(ingredientId, currentQuantity) {
      const newQuantity = currentQuantity + 1
      this.updateIngredientQuantity(ingredientId, newQuantity)
    },
    decrementIngredientQuantity(ingredientId, currentQuantity) {
      const newQuantity = currentQuantity - 1
      if (newQuantity >= 0) {
        this.updateIngredientQuantity(ingredientId, newQuantity)
      }
    },
    toggleSelectIngredient(ingredientId, isSelected) {
      const ingredient = this.ingredients.find((ing) => ing.id === ingredientId)
      if (!isSelected) {
        ingredient.quantity = 1
      }
    },
    async saveCustomizations() {
      const customizations = this.ingredients
        .filter((ing) => ing.selected)
        .map((ing) => ({
          ingredient_id: ing.id,
          quantity: ing.quantity
        }))

      try {
        await axios.post(`${BASE_URL}/cart/customize/cart/${this.cartItemId}`, customizations, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })
        this.$router.push('/cart') // Redirect back to cart after saving
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
</style>

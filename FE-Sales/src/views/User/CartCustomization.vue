<template>
  <navbar />
  <div class="mt-6 p-4 container">
    <v-card>
      <v-card-title>Kustomisasi Item</v-card-title>
      <v-card-text class="py-2">
        <div v-for="ingredient in ingredients" :key="ingredient.id" class="ingredient-item">
          <div class="d-flex py-4 align-items-center" style="height: 70px">
            <input
              type="checkbox"
              class="checkbox"
              v-model="ingredient.selected"
              @change="toggleSelectIngredient(ingredient.id, ingredient.selected)"
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
                @change="updateIngredientQuantity(ingredient.id, ingredient.quantity)"
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
  <div class="row sticky-bottom justify-content-center ">
    <div class="row footer-content mb-2">
      <div class="d-flex justify-content-between align-items-center">
        <strong>Harga Menu: Rp {{ formatPrice(menuPrice) }}</strong>
        <v-btn color="primary" @click="navigateToCart">Simpan</v-btn>
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
      cartItemId: this.$route.params.id,
      menuPrice: 0
    }
  },
  mounted() {
    this.retrieveIngredients()
    this.retrieveCustomizations()
    this.retrieveMenuPrice()
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
        const response = await axios.get(`${BASE_URL}/customize/get/${this.cartItemId}`, {
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
        if (error.response && error.response.status === 404) {
          this.ingredients.forEach((ingredient) => {
            ingredient.selected = true
          })
        } else {
          console.error(error)
        }
      }
    },
    async retrieveMenuPrice() {
      try {
        const response = await axios.get(`${BASE_URL}/cart/item/${this.cartItemId}`, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })
        this.menuPrice = response.data.data.customization_price
      } catch (error) {
        console.error(error)
      }
    },
    async updateIngredientQuantity(ingredientId, quantity) {
      if (quantity < 0) return
      const ingredient = this.ingredients.find((ing) => ing.id === ingredientId)
      ingredient.quantity = quantity
      await this.saveCustomization(ingredientId, quantity)
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
    async toggleSelectIngredient(ingredientId, isSelected) {
      const ingredient = this.ingredients.find((ing) => ing.id === ingredientId)
      const quantity = isSelected ? 1 : 0
      ingredient.selected = isSelected
      ingredient.quantity = quantity
      await this.saveCustomization(ingredientId, quantity)
    },
    async saveCustomization(ingredientId, quantity) {
      try {
        const response = await axios.post(
          `${BASE_URL}/customize/cart/${this.cartItemId}`,
          {
            ingredient_id: ingredientId,
            quantity: quantity
          },
          {
            headers: {
              Authorization: 'Bearer ' + localStorage.getItem('access_token')
            }
          }
        )
        this.menuPrice = response.data.harga_dasar
      } catch (error) {
        console.error(error)
      }
    },
    navigateToCart() {
      this.$router.push('/cart') // Redirect back to cart after saving
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

.text{
  font-size: 1.2rem;
  font-weight: bold;
}

.checkbox{
  width: 1.3rem;
  height: 1.3rem;
}
</style>

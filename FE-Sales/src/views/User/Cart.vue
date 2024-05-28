<script>
// import { useRoute } from "vue-router";
import axios from 'axios'
const BASE_URL = import.meta.env.VITE_BASE_URL_API
import Navbar from '@/components/HomeNavbar.vue'

export default {
  components: {
    Navbar
  },
  data() {
    return {
      overlay: false,
      orders: [],
      totalPayment: 0
    }
  },
  mounted() {
    this.retrieveCart()
  },
  created() {
    this.store = this.$store
    this.body = document.getElementsByTagName('body')[0]
  },
  methods: {
    formatPrice(price) {
      const numericPrice = parseFloat(price)
      return numericPrice.toLocaleString('id-ID')
    },
    async updateQuantity(index, id, newQuantity) {
      if (newQuantity < 1) return

      try {
        const response = await axios.post(
          `${BASE_URL}/cart/update/${id}`,
          { quantity: newQuantity },
          {
            headers: {
              Authorization: 'Bearer ' + localStorage.getItem('access_token')
            }
          }
        )
        this.orders[index].quantity = response.data.quantity
        this.orders[index].totalPrice = response.data.harga
        this.totalPayment = response.data.total_harga
      } catch (error) {
        console.error(error)
      }
    },
    // async proceedToCheckout() {
    //   const selectedOrders = this.orders.filter((order) => order.selected)
    //   if (selectedOrders.length > 0) {
    //     try {
    //       const selectedIds = selectedOrders.map((order) => order.id)
    //       await axios.put(
    //         `${BASE_URL}/cart/select`,
    //         { selected_ids: selectedIds },
    //         {
    //           headers: {
    //             Authorization: 'Bearer ' + localStorage.getItem('access_token')
    //           }
    //         }
    //       )
    //       this.$router.push('/checkout')
    //     } catch (error) {
    //       console.error('Error updating selected items:', error)
    //     }
    //   } else {
    //     this.$notify({
    //       type: 'danger',
    //       title: 'Gagal',
    //       text: 'Pilih salah satu item untuk checkout',
    //       color: 'green'
    //     })
    //   }
    // },
    async incrementQuantity(index) {
      const order = this.orders[index]
      const newQuantity = order.quantity + 1
      await this.updateQuantity(index, order.id, newQuantity)
    },
    async decrementQuantity(index) {
      const order = this.orders[index]
      const newQuantity = order.quantity - 1
      if (newQuantity > 0) {
        await this.updateQuantity(index, order.id, newQuantity)
      }
    },
    async retrieveCart() {
      this.overlay = true
      try {
        const response = await axios.get(`${BASE_URL}/cart/get`, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })

        this.orders = response.data.items.map((item) => ({
          ...item,
          selected: item.select === 1, // Automatically select if select is 1
          totalPrice: item.harga
        }))
        this.totalPayment = response.data.total_harga
      } catch (error) {
        console.error(error)
        this.$notify({
          type: 'danger',
          title: 'Notif',
          text: 'Anda belum menambahkan barang',
          color: 'green'
        })
      } finally {
        this.overlay = false
      }
    },
    async removeOrder(index) {
      const orderId = this.orders[index].id
      try {
        await axios.post(`${BASE_URL}/cart/delete/${orderId}`, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })
        this.orders.splice(index, 1)
        this.totalPayment = response.data.total_harga
      } catch (error) {
        console.error(error)
      }
    },
    async toggleSelect(index) {
      const orderId = this.orders[index].id
      const url = orderId.selected
        ? `${BASE_URL}/cart/select/${orderId}`
        : `${BASE_URL}/cart/unselect/${orderId}`

      try {
        const response = await axios.get(
          url,
          {
            headers: {
              Authorization: 'Bearer ' + localStorage.getItem('access_token')
            }
          },
          {
            headers: {
              Authorization: 'Bearer ' + localStorage.getItem('access_token')
            }
          }
        )
        this.orders[index].selected = !this.orders[index].selected
        this.totalPayment = response.data.total_harga
      } catch (error) {
        console.error(error)
      }
    }
  }
}
</script>

<template>
  <navbar />
  <div class="py-4 container">
    <div class="row mt-6">
      <v-overlay :model-value="overlay" class="d-flex align-items-center justify-content-center">
        <v-progress-circular color="primary" size="96" indeterminate></v-progress-circular>
      </v-overlay>
      <div class="container">
        <div class="row">
          <div class="col-lg-8">
            <div v-for="(order, index) in orders" :key="index" class="mb-4 card">
              <div class="card-body">
                <h5 class="card-title">Pesanan {{ index + 1 }}</h5>
                <div class="row">
                  <div class="col-md-3">
                    <div class="row">
                      <div class="col-1 align-items-center d-flex">
                        <input
                          type="checkbox"
                          class="large-checkbox"
                          v-model="order.selected"
                          @change="toggleSelect(index)"
                        />
                      </div>
                      <div class="col">
                        <img :src="order.foto" class="img-fluid" alt="Book image" />
                      </div>
                    </div>
                  </div>
                  <div class="col-md-9">
                    <div class="row">
                      <div class="col">
                        <h6>{{ order.name }}</h6>
                        <p>Rp {{ formatPrice(order.harga_menu) }}</p>
                      </div>
                      <div class="col">
                        <div class="d-flex align-items-center">
                          <v-icon
                            icon="mdi-minus"
                            style="cursor: pointer"
                            @click="decrementQuantity(index)"
                          ></v-icon>
                          <span class="mx-2">{{ order.quantity }}</span>
                          <v-icon
                            icon="mdi-plus"
                            style="cursor: pointer"
                            @click="incrementQuantity(index)"
                          ></v-icon>
                          <button class="btn btn-link text-danger ms-3" @click="removeOrder(index)">
                            <i class="bi bi-trash"></i> Hapus
                          </button>
                          <span class="ms-auto">Rp {{ formatPrice(order.totalPrice) }}</span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="card">
              <div class="card-body">
                <h5 class="card-title">Rincian Belanja</h5>
                <p>Ringkasan Pembayaran</p>
                <p>Rp {{ formatPrice(totalPayment) }}</p>
                <button class="btn btn-primary w-100" @click="proceedToCheckout">
                  Lanjut ke Pembayaran
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style>
.user-select-none {
  user-select: none;
}

a {
  text-decoration: none;
  color: unset;
}

.large-checkbox {
  width: 1.5rem;
  height: 1.5rem;
}
</style>

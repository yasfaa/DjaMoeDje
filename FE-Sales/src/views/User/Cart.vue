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
      overlay: false,
      orders: [],
      currentDropdownIndex: null,
      totalPayment: 0,
      noteModal: false,
      note: '',
      noteIndex: null
    }
  },
  mounted() {
    this.retrieveCart()
  },
  methods: {
    toggleDropdown(index) {
      if (this.currentDropdownIndex === index) {
        this.currentDropdownIndex = null
      } else {
        this.currentDropdownIndex = index
      }
    },
    parseCustomization(customization) {
      try {
        return JSON.parse(customization)
      } catch (e) {
        console.error('Error parsing customization JSON:', e)
        return []
      }
    },
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
        this.totalPayment = response.data.total_harga
      } catch (error) {
        console.error(error)
      }
    },
    async proceedToCheckout() {
      const selectedOrders = this.orders.filter((order) => order.selected)
      if (selectedOrders.length > 0) {
        try {
          this.$router.push('/checkout')
        } catch (error) {
          console.error('Error updating selected items:', error)
        }
      } else {
        this.$notify({
          type: 'danger',
          title: 'Gagal',
          text: 'Pilih salah satu item untuk checkout',
          color: 'green'
        })
      }
    },
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
          selected: item.select === 1 // Automatically select if select is 1
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
        await axios.delete(`${BASE_URL}/cart/delete/${orderId}`, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })
        this.orders.splice(index, 1)
        // Periksa apakah response.data.total_harga selalu tersedia setelah menghapus item
        this.totalPayment = this.orders.reduce(
          (acc, order) => acc + parseFloat(order.harga_dasar),
          0
        )
      } catch (error) {
        console.error(error)
      }
    },
    async toggleSelect(index) {
      const orderId = this.orders[index].id
      const url = this.orders[index].selected
        ? `${BASE_URL}/cart/select/${orderId}`
        : `${BASE_URL}/cart/unselect/${orderId}`

      try {
        const response = await axios.get(url, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })
        this.orders[index].selected = response.data.select === 1
        this.totalPayment = response.data.total_harga
      } catch (error) {
        console.error(error)
      }
    },
    showNoteModal(index) {
      this.noteIndex = index
      this.note = this.orders[index].catatan || ''
      this.noteModal = true
    },
    closeNoteModal() {
      this.noteModal = false
      this.note = ''
      this.noteIndex = null
    },
    async saveNote() {
      const index = this.noteIndex
      const orderId = this.orders[index].id

      try {
        await axios.post(
          `${BASE_URL}/cart/addNote/${orderId}`,
          { catatan: this.note },
          {
            headers: {
              Authorization: 'Bearer ' + localStorage.getItem('access_token')
            }
          }
        )
        this.orders[index].catatan = this.note
        this.closeNoteModal()
      } catch (error) {
        console.error(error)
      }
    },
    goToCustomization(cartItemId) {
      this.$router.push(`/customization/${cartItemId}`)
    }
  }
}
</script>

<template>
  <navbar />
  <div class="py-4 container">
    <div class="row mt-6">
      <v-overlay :model-value="overlay" class="d-flex align-items-center justify-content-center">
        <v-progress-circular
          color="amber"
          indeterminate
          :size="69"
          :width="6"
        ></v-progress-circular>
      </v-overlay>
      <div class="container mt-2">
        <div class="row">
          <div class="col-lg-8">
            <div v-for="(order, index) in orders" :key="index" class="mb-4 card">
              <div class="card-body">
                <div class="row d-flex align-items-center justify-content-between">
                  <div class="col-md-12">
                    <div class="row">
                      <div class="col-md-2 d-flex align-items-center">
                        <input
                          type="checkbox"
                          class="large-checkbox me-1"
                          v-model="order.selected"
                          @change="toggleSelect(index)"
                        />
                        <img :src="order.foto" class="foto img-fluid" />
                      </div>
                      <div class="col detailMenu">
                        <h5>{{ order.name }}</h5>
                        <p class="mb-3">Rp {{ formatPrice(order.harga_dasar) }}</p>
                        <a class="lihat" v-if="order.customization" @click="toggleDropdown(index)">
                          {{
                            currentDropdownIndex === index
                              ? 'Sembunyikan Kustomisasi'
                              : 'Lihat Kustomisasi Anda'
                          }}
                        </a>
                        <div
                          :class="{ 'hidden-dropdown': true, show: currentDropdownIndex === index }"
                        >
                          <p
                            v-for="(item, idx) in parseCustomization(order.customization)"
                            :key="idx"
                          >
                            {{ item.nama }}: {{ item.quantity }}
                          </p>
                        </div>
                      </div>

                      <div class="col d-flex align-items-center justify-content-end">
                        <v-icon
                          icon="mdi-minus"
                          class="icon-btn"
                          @click="decrementQuantity(index)"
                        ></v-icon>
                        <input
                          type="number"
                          min="1"
                          v-model.number="order.quantity"
                          @change="updateQuantity(index, order.id, order.quantity)"
                          class="form-control text-center quantity-input mx-2"
                          style="width: 55px"
                        />
                        <v-icon
                          icon="mdi-plus"
                          class="icon-btn"
                          @click="incrementQuantity(index)"
                        ></v-icon>
                        <v-icon
                          class="mx-3 justify-content-end"
                          icon="mdi-delete"
                          color="red"
                          style="cursor: pointer"
                          @click="removeOrder(index)"
                        ></v-icon>
                        <v-icon
                          class="mx-3 justify-content-end"
                          icon="mdi-note-edit"
                          style="cursor: pointer"
                          @click="showNoteModal(index)"
                        ></v-icon>
                      </div>
                    </div>
                    <div class="row">
                      <a class="custom ms-3 mt-2" @click="goToCustomization(order.id)">
                        {{
                          order.customization
                            ? 'Ubah Kustomisasi Anda'
                            : 'Kustomisasi Menu Anda Sekarang!'
                        }}
                      </a>
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
                <p>Total biaya belanja</p>
                <p>Rp {{ formatPrice(totalPayment) }}</p>
                <button class="btn btn-primary w-100" @click="proceedToCheckout">Checkout</button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- Note Modal -->
  <v-dialog v-model="noteModal" max-width="500px">
    <v-card>
      <v-card-title>
        <span class="headline">Tambah Catatan</span>
      </v-card-title>
      <v-card-text>
        <v-textarea v-model="note" label="Catatan" rows="3"></v-textarea>
      </v-card-text>
      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="blue darken-1" text @click="closeNoteModal">Batal</v-btn>
        <v-btn color="blue darken-1" text @click="saveNote">Simpan</v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
</template>

<style>
.user-select-none {
  user-select: none;
}

.custom {
  text-decoration: underline;
  color: #998748;
  cursor: pointer;
}

.custom:hover {
  color: #ffe279;
}

.lihat {
  color: #6b6b6b;
  cursor: pointer;
}

.lihat:hover {
  color: #000000;
}

.large-checkbox {
  width: 1.5rem;
  height: 1.5rem;
}

.h5 {
  overflow: hidden;
}
.foto {
  max-width: 100px;
  overflow: hidden;
}

.icon-btn {
  cursor: pointer;
}

.hidden-dropdown {
  display: none;
  background-color: #f0f0f0;
  padding: 10px;
  border-radius: 5px;
  margin-top: 10px;
}

.hidden-dropdown.show {
  display: block;
}

.hidden-dropdown p {
  margin: 0;
}

@media (max-width: 600px) {
  .large-checkbox {
    width: 1rem;
    height: 1rem;
  }
  .detailMenu {
    margin-left: 30px;
  }
}
</style>

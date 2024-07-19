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
      selectedFilter: '',
      currentDropdownIndex: null
    }
  },
  mounted() {
    this.retrieveOrders()
  },
  methods: {
    formatDate(data_date) {
      const date = new Date(data_date)
      const options = { year: 'numeric', month: 'long', day: '2-digit' }
      return date.toLocaleDateString('id-ID', options)
    },
    formatPrice(price) {
      const numericPrice = parseFloat(price)
      return numericPrice.toLocaleString('id-ID')
    },
    toggleDropdown(index) {
      console.log('Toggle Dropdown called with index:', index)
      if (this.currentDropdownIndex === index) {
        this.currentDropdownIndex = null
      } else {
        this.currentDropdownIndex = index
      }
    },
    async retrieveOrders() {
      this.overlay = true
      try {
        const response = await axios.get(`${BASE_URL}/order/getOrder`, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          },
          params: {
            status: this.selectedFilter
          }
        })

        this.orders = response.data.order
      } catch (error) {
        console.error(error)
        this.$notify({
          type: 'danger',
          title: 'Notif',
          text: 'Error retrieving orders',
          color: 'green'
        })
      } finally {
        this.overlay = false
      }
    },
    lihatDetail(order) {
      this.$router.push('/orders/' + order.id_transaksi)
    },
    payNow(order) {
      const paymentUrl = order.payment
      window.open(paymentUrl, '_blank')
    },
    async updatestatus(transactionId, status) {
      try {
        const response = await axios.post(
          `${BASE_URL}/order/update`,
          {
            transactionId: transactionId,
            status: status
          },
          {
            headers: {
              'Content-Type': 'multipart/form-data',
              Authorization: 'Bearer ' + localStorage.getItem('access_token')
            }
          }
        )

        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Status Diperbarui',
          color: 'green'
        })
        await this.retrieveOrders()
      } catch (error) {
        console.error('Error updating status:', error)
      }
    },
    async cancelOrder(order) {
      const transactionId = order.id_transaksi
      await this.updatestatus(transactionId, 'cancelled')
    },
    async finishOrder(order) {
      const transactionId = order.id_transaksi
      await this.updatestatus(transactionId, 'finished')
    },
    getStatusBadge(status) {
      switch (status) {
        case 'pending':
          return 'text-bg-danger'
        case 'process':
        case 'confirmed':
          return 'text-bg-success'
        case 'allocated':
        case 'picking_up':
        case 'picked':
        case 'dropping_off':
        case 'return_in_transit':
          return 'text-bg-warning'
        case 'delivered':
        case 'finished':
          return 'text-bg-info'
        case 'rejected':
        case 'courierNotFound':
        case 'returned':
        case 'cancelled':
        case 'disposed':
        case 'on_hold':
          return 'text-bg-danger'
        default:
          return 'text-bg-secondary'
      }
    },
    getStatusText(status) {
      switch (status) {
        case 'pending':
          return 'Menunggu Pembayaran'
        case 'process':
          return 'Pesanan Diproses'
        case 'finished':
          return 'Pesanan Selesai'
        case 'confirmed':
          return 'Pesanan Dikonfirmasi'
        case 'allocated':
          return 'Kurir Telah Dialokasikan'
        case 'picking_up':
          return 'Kurir Sedang Menuju Titik Jemput'
        case 'picked':
          return 'Barang Telah Diambil'
        case 'dropping_off':
          return 'Barang Sedang Dikirim'
        case 'return_in_transit':
          return 'Pengembalian Sedang Dalam Perjalanan'
        case 'delivered':
          return 'Telah Terkirim'
        case 'rejected':
          return 'Pesanan Ditolak'
        case 'courier_not_found':
          return 'Kurir Tidak Tersedia'
        case 'returned':
          return 'Pesanan Dikembalikan'
        case 'cancelled':
          return 'Pesanan Dibatalkan'
        case 'disposed':
          return 'Pesanan Dibuang'
        case 'on_hold':
          return 'Pesanan Dalam Tunggu'
        default:
          return 'Tidak Diketahui'
      }
    }
  }
}
</script>

<template>
  <navbar />
  <div class="py-4 container mt-6">
    <div class="row mt-6">
      <v-overlay :model-value="overlay" class="d-flex align-items-center justify-content-center">
        <v-progress-circular
          color="amber"
          indeterminate
          :size="69"
          :width="6"
        ></v-progress-circular>
      </v-overlay>
      <div class="container">
        <div class="row">
          <div class="col-md-12">
            <div class="row ps-3 mb-2" style="color: black">
              Filter:
              <div class="col-4">
                <select
                  class="form-select form-select-sm"
                  aria-label="Small select example"
                  v-model="selectedFilter"
                  @change="retrieveOrders"
                >
                  <option value="" selected>Semua</option>
                  <option value="pending">Menunggu Pembayaran</option>
                  <option value="process">Pesanan Diproses</option>
                  <option value="packing">Dikemas</option>
                  <option value="confirmed">Pesanan Dikonfirmasi</option>
                  <option value="allocated">Kurir Telah Dialokasikan</option>
                  <option value="picking_up">Kurir Sedang Menuju Titik Jemput</option>
                  <option value="picked">Barang Telah Diambil</option>
                  <option value="dropping_off">Barang Sedang Dikirim</option>
                  <option value="delivery">Sedang Dikirim</option>
                  <option value="delivered">Telah Terkirim</option>
                  <option value="finished">Selesai</option>
                  <option value="cancelled">Pesanan Dibatalkan</option>
                  <option value="rejected">Pesanan Ditolak</option>
                </select>
              </div>
            </div>
            <v-card class="card mb-3" v-for="order in orders" :key="order.id">
              <div
                class="card-header p-0 px-3 d-flex justify-content-between align-items-center p-1"
              >
                <div>
                  <span>No. Pesanan {{ order.no_pesanan }}</span>
                  <span class="mx-2" :class="['badge', getStatusBadge(order.status)]">{{
                    getStatusText(order.status)
                  }}</span>
                </div>
                <div>
                  <span
                    ><a style="font-size: 12px"> {{ formatDate(order.created_at) }} </a></span
                  >
                </div>
              </div>

              <div class="card-body px-4 py-0">
                <div v-for="item in order.menu" :key="item.id" class="d-flex m-3">
                  <img
                    :src="item.imagePath ? item.imagePath : `https://picsum.photos/200`"
                    alt="Product Image"
                    class="img-fluid"
                    style="width: 100px; height: 100px; margin-right: 30px"
                  />
                  <div>
                    <h6 class="mt-4">{{ item.nama_menu }}</h6>
                    <p>{{ item.quantity }} Barang x Rp {{ formatPrice(item.harga_menu) }}</p>
                    <a
                      class="lihat"
                      v-if="item.customization && item.customization.length > 0"
                      @click="toggleDropdown(orderIndex + '-' + index)"
                    >
                      {{
                        currentDropdownIndex === orderIndex + '-' + index
                          ? 'Sembunyikan Kustomisasi'
                          : 'Lihat Kustomisasi Anda'
                      }}
                    </a>
                    <div class="show" v-if="currentDropdownIndex === orderIndex + '-' + index">
                      <ul>
                        <li v-for="custom in item.customization" :key="custom.nama">
                          {{ custom.nama }}: {{ custom.quantity }}
                        </li>
                      </ul>
                    </div>
                  </div>
                </div>
                <hr />
                <div class="row">
                  <div class="col-6">
                    <a
                      class="text-bold"
                      @click="lihatDetail(order)"
                      style="color: #5e72e4; cursor: pointer"
                      >Lihat Detail Pesanan</a
                    >
                  </div>
                  <div class="col-6 text-end">
                    <a>Total Pesanan: Rp {{ formatPrice(order.total) }}</a>
                  </div>
                </div>
                <hr />
                <div class="d-flex justify-content-end mb-2">
                  <button
                    class="btn btn-sm btn-primary"
                    @click="payNow(order)"
                    v-if="order.status === 'pending'"
                  >
                    Bayar Sekarang
                  </button>
                  <button
                    class="btn btn-sm btn-warning"
                    @click="cancelOrder(order)"
                    v-if="order.status === 'pending'"
                  >
                    Batalkan Pesanan
                  </button>
                  <button
                    class="btn btn-sm btn-success"
                    @click="finishOrder(order)"
                    v-if="order.status === 'delivered'"
                  >
                    Selesaikan Pesanan
                  </button>
                </div>
              </div>
            </v-card>
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

.card {
  border-radius: 15px;
  box-shadow: 1px 1px 15px #cccccc40;
  transition: 0.5s ease-in;
  background-color: white;
  margin: 1rem;
}

.card:hover {
  box-shadow: 1px 1px 28.5px -7px #d6d6d6;
}
.list-group-item.active {
  background-color: #007bff;
  border-color: #007bff;
}
</style>

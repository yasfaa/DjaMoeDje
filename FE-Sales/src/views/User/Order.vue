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
    getStatusBadge(status) {
      switch (status) {
        case 'pending':
          return 'text-bg-danger'
        case 'process':
          return 'text-bg-success'
        case 'packing':
          return 'text-bg-warning'
        case 'delivery':
          return 'text-bg-warning'
        case 'delivered':
          return 'text-bg-info'
        case 'finished':
          return 'text-bg-success'
        case 'expired':
          return 'text-bg-secondary'
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
        case 'packing':
          return 'Pesanan Dikemas'
        case 'delivery':
          return 'Sedang Dikirim'
        case 'delivered':
          return 'Telah Terkirim'
        case 'finished':
          return 'Pesanan Selesai'
        case 'expired':
          return 'Expired'
        case 'onsite':
          return 'On Site'
        case 'failed':
          return 'Pembayaran Gagal'
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
                  <option value="pending">Pending</option>
                  <option value="process">Process</option>
                  <option value="packing">Dikemas</option>
                  <option value="delivery">Sedang Dikirim</option>
                  <option value="delivered">Terkirim</option>
                  <option value="finished">Selesai</option>
                  <option value="onsite">OnSite</option>
                  <option value="expired">Expired</option>
                </select>
              </div>
            </div>
            <div class="card mb-3" v-for="order in orders" :key="order.id">
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
                </div>
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

.transaction-card {
  max-width: 100%;
  margin: auto;
}
.card-header {
  background-color: #f8f9fa;
  border-bottom: 1px solid #e9ecef;
}
.card-header .badge {
  margin-left: 10px;
}
.card-body {
  background-color: #fff;
}
.list-group-item.active {
  background-color: #007bff;
  border-color: #007bff;
}
</style>

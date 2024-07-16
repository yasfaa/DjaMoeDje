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
      orders: {},
      items: [],
      address: '',
      showDetail: false,
      riwayat: []
    }
  },

  async mounted() {
    await this.retrieveDetail()
    if (this.orders && this.orders.tracking_id) {
      await this.retrieveBsOrder()
      this.overlay = false
    }
  },
  methods: {
    formatPrice(price) {
      const numericPrice = parseFloat(price)
      return numericPrice.toLocaleString('id-ID')
    },
    formatDate(date) {
      if (!date) return ''
      const options = {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }
      return new Date(date).toLocaleDateString('id-ID', options)
    },
    getAddressPart(index) {
      const addressParts = this.address.split(',')
      return addressParts[index] || ''
    },
    async retrieveDetail() {
      this.overlay = true
      try {
        const response = await axios.get(`${BASE_URL}/order/detail/` + this.$route.params.id, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })
        const data = response.data.order
        this.orders = {
          id: data.id_transaksi,
          no_pesanan: data.no_pesanan,
          status: data.status,
          total: data.total,
          biaya_kirim: data.biaya_kirim,
          payment: data.payment,
          courier_type: data.courier_type,
          tracking_id: data.tracking_id,
          alamat: data.alamat,
          no_resi: data.no_resi,
          created_at: data.created_at
        }
        this.address = data.alamat
        this.items = data.menu.map((item) => ({
          ...item,
          image: item.imagePath,
          nama_menu: item.nama_menu,
          harga_menu: item.harga_menu,
          quantity: item.quantity
        }))
      } catch (error) {
        console.error(error)
        this.$notify({
          type: 'danger',
          title: 'Notif',
          text: 'Error Accessing Page',
          color: 'green'
        })
      } finally {
        this.overlay = false
      }
    },
    async retrieveBsOrder() {
      this.overlay = true
      try {
        const response = await axios.get(`${BASE_URL}/order/tracking/` + this.orders.id, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })
        const data = response.data.tracking
        this.riwayat = data.history
      } catch (error) {
        console.error('Error retrieving order details:', error)
        this.$notify({
          type: 'danger',
          title: 'Error',
          text: 'Failed to retrieve order details!',
          color: 'red'
        })
      } finally {
        this.overlay = false
      }
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
        case 'confirmed':
          return 'text-bg-success'
        case 'allocated':
        case 'picking_up':
        case 'picked':
        case 'dropping_off':
        case 'return_in_transit':
          return 'text-bg-warning'
        case 'delivered':
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
    getStatusIcon(status) {
      switch (status) {
        case 'pending':
        case 'rejected':
        case 'courierNotFound':
        case 'returned':
        case 'cancelled':
        case 'disposed':
        case 'on_hold':
          return 'mdi-close-circle'
        case 'process':
        case 'confirmed':
        case 'allocated':
        case 'picking_up':
        case 'picked':
        case 'dropping_off':
        case 'return_in_transit':
          return 'mdi-truck-fast'
        case 'delivered':
          return 'mdi-check-circle'
        default:
          return 'mdi-alert-circle'
      }
    },
    getStatusText(status) {
      switch (status) {
        case 'pending':
          return 'Menunggu Pembayaran'
        case 'process':
          return 'Pesanan Diproses'
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
    },
    getNoteDescription(status) {
      switch (status) {
        case 'confirmed':
          return 'Pesanan telah dikonfirmasi. Mencari driver terdekat untuk pick up.'
        case 'allocated':
          return 'Kurir telah dialokasikan, menunggu pick up.'
        case 'picking_up':
          return 'Kurir menuju lokasi pick up.'
        case 'picked':
          return 'Pesanan telah diambil oleh kurir.'
        case 'dropping_off':
          return 'Pesanan Anda dalam proses pengiriman.'
        case 'return_in_transit':
          return 'Pesanan dalam perjalanan kembali ke penjual.'
        case 'delivered':
          return 'Pesanan telah terkirim.'
        case 'on_hold':
          return 'Pengiriman Anda sedang ditahan sementara.'
        case 'rejected':
          return 'Pengiriman Anda telah ditolak. Silakan hubungi Biteship untuk informasi lebih lanjut.'
        case 'courier_not_found':
          return 'Pengiriman dibatalkan karena tidak ada kurir yang tersedia saat ini.'
        case 'returned':
          return 'Pesanan berhasil dikembalikan.'
        case 'cancelled':
          return 'Pesanan dibatalkan.'
        case 'disposed':
          return 'Pesanan berhasil dibuang.'
        default:
          return 'Status tidak dikenal.'
      }
    }
  }
}
</script>

<template>
  <div>
    <navbar />
    <div class="py-4 mt-6 container">
      <div class="row mt-6">
        <v-overlay :model-value="overlay" class="d-flex align-items-center justify-content-center">
          <v-progress-circular
            color="amber"
            indeterminate
            :size="69"
            :width="6"
          ></v-progress-circular>
        </v-overlay>

        <div class="col-md-12 mb-4">
          <v-card class="card mb-2 equal-height">
            <div class="card-body">
              <h5 class="mb-3 d-flex justify-content-between align-items-center">
                <div>
                  Status Pesanan
                  <p :class="['badge', getStatusBadge(orders.status)]">
                    {{ getStatusText(orders.status) }}
                  </p>
                </div>
                <button class="btn btn-sm btn-primary" small @click="showDetail = !showDetail">
                  {{ showDetail ? 'Sembunyikan' : 'Lihat Detail' }}
                </button>
              </h5>
              <p>No. Invoice: {{ orders.no_pesanan }}</p>
              <p>Tanggal Pembelian: {{ formatDate(orders.created_at) }}</p>
              <v-expand-transition>
                <div v-show="showDetail">
                  <v-timeline side="end">
                    <v-timeline-item
                      v-for="(item, index) in riwayat"
                      :key="index"
                      :color="getStatusBadge(item.status)"
                      :icon="getStatusIcon(item.status)"
                      dot-color="yellow"
                      size="small"
                    >
                      <div>{{ formatDate(item.updated_at) }}</div>
                      <div>{{ getNoteDescription(item.status) }}</div>
                    </v-timeline-item>
                  </v-timeline>
                </div>
              </v-expand-transition>
            </div>
          </v-card>

          <v-card class="card mb-2 equal-height">
            <div class="card-body">
              <h5 class="mb-3">Rincian Pesanan</h5>
              <ul class="list-group list-group-flush">
                <li
                  v-for="(item, index) in items"
                  :key="index"
                  class="list-group-item d-flex justify-content-between align-items-center"
                >
                  <div class="d-flex align-items-center">
                    <img :src="item.image" alt="" style="width: 100px" class="img-fluid rounded" />
                    <div class="ms-3">
                      <h6 class="mb-0">{{ item.nama_menu }}</h6>
                      <small class="text-muted"
                        >Rp{{ formatPrice(item.harga_menu) }} x {{ item.quantity }}</small
                      >
                    </div>
                  </div>
                  <span>Rp{{ formatPrice(item.total_menu) }}</span>
                </li>
              </ul>
            </div>
          </v-card>
          <v-card class="card mb-2 equal-height">
            <div class="card-body">
              <h5 class="mb-3">Info Pengiriman</h5>
              <div class="info-row">
                <span>Tipe</span>
                <span>:</span>
                <span>Paxel {{ orders.courier_type }}</span>
              </div>
              <div class="info-row">
                <span>No Resi </span>
                <span>:</span>
                <span>{{ orders.no_resi }}</span>
              </div>
              <div class="info-row">
                <span>Alamat</span>
                <span>:</span>
                <span>
                  {{ getAddressPart(0) }}<br />
                  {{ getAddressPart(1) }}<br />
                  {{ getAddressPart(2) }}, {{ getAddressPart(3) }} <br />
                  {{ getAddressPart(4) }}, {{ getAddressPart(5) }} <br />
                  {{ getAddressPart(6) }}, {{ getAddressPart(7) }}
                </span>
              </div>
            </div>
          </v-card>
          <v-card class="card mb-4 equal-height">
            <div class="card-body">
              <h5 class="mb-3">Rincian Pembayaran</h5>
              <div class="info-row">
                <span>Total Harga </span>
                <span>:</span>
                <span>Rp{{ formatPrice(orders.total - orders.biaya_kirim) }}</span>
              </div>
              <div class="info-row">
                <span>Biaya Kirim </span>
                <span>:</span>
                <span>Rp{{ formatPrice(orders.biaya_kirim) }}</span>
              </div>
              <div class="info-row">
                <span>Total Pembayaran</span>
                <span>:</span>
                <span>Rp{{ formatPrice(orders.total) }}</span>
              </div>
              <div class="row justify-content-end">
                <button
                class="btn btn-sm btn-primary"
                @click="payNow(orders)"
                v-if="orders.status === 'pending'"
              >
                Bayar Sekarang
              </button>
              </div>
            </div>
          </v-card>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.user-select-none {
  user-select: none;
}

a {
  text-decoration: none;
  color: unset;
}

.card.equal-height {
  display: flex;
  flex-direction: column;
  border-radius: 5px;
  box-shadow: 1px 1px 15px #cccccc40;
  transition: 0.5s ease-in;
  background-color: white;
  margin: 1rem;
}

.info-row {
  display: flex;
  justify-content: flex-start;
  margin-bottom: 0.5rem;
}

.info-row span:first-child {
  width: 100px;
  font-weight: bold;
}

.info-row span:last-child {
  flex: 1;
  text-align: left;
}

.mb-4 {
  margin-bottom: 1.5rem !important;
}


@media (max-width: 576px) {
  .row .col-1,
  .row .col-2,
  .row .col-3,
  .row .col-4,
  .row .col-2 a,
  .row .col-3 a,
  .row .col-4 a,
  .row .col-2 strong,
  .row .col-3 strong,
  .row .col-4 strong {
    font-size: 10px;
  }

  .row img {
    width: 60px;
    margin-right: 10px;
  }

  .text-mobile {
    font-size: 12px;
  }
}
</style>

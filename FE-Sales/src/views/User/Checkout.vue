<script>
import axios from 'axios'
import 'leaflet/dist/leaflet.css'
import L from 'leaflet'
const BASE_URL = import.meta.env.VITE_BASE_URL_API
import Navbar from '@/components/DashboardNavbar.vue'

export default {
  components: {
    Navbar
  },
  data() {
    return {
      hideConfigButton: true,
      showNavbar: true,
      showSidenav: false,
      showFooter: false,
      overlay: false,
      orders: [],
      totalPayment: '',
      alamat: [],
      mapCenter: [-6.17511, 106.865036],
      zoom: 13,
      markerPosition: null,
      map: null,
      marker: null,
      loadingRegist: false,
      loadingKurir: false,
      searchQuery: '',
      searchResults: [],
      selectedAddresses: null,
      address: {
        nama_penerima: '',
        nomor_telepon: '',
        jalan: '',
        kecamatan: '',
        kota: '',
        provinsi: '',
        kode_pos: '',
        latitude: '',
        longitude: ''
      },
      users: {
        name: '',
        email: ''
      },
      selectedAddressId: null,
      selectedAddress: null,
      shippingRates: [],
      dialog: false,
      selectedCourier: null
    }
  },

  mounted() {
    this.retrieveCart()
    this.fetchUserAddresses()
    this.initMap()
  },
  watch: {
    selectedAddressId(newVal) {
      this.fillAddress()
      this.fetchShippingRates()
    },
    selectedCourier() {
      this.updateTotalPayment()
    },
    searchQuery(newQuery) {
      this.searchWithDelay()
    },
    selectedAddress(newAddress) {
      if (newAddress) {
        this.mapCenter = [parseFloat(newAddress.lat), parseFloat(newAddress.lon)]
        this.markerPosition = [parseFloat(newAddress.lat), parseFloat(newAddress.lon)]
      }
    }
  },
  methods: {
    async getUser() {
      try {
        const response = await axios.get(`${BASE_URL}/user`, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })
        this.users = response.data
      } catch (error) {
        console.error(error)
        if (error.response && error.response.data.message) {
          const errorMessage = error.response.data.message
          console.log(errorMessage)
        }
      }
    },
    formatPrice(price) {
      const numericPrice = parseFloat(price)
      return numericPrice.toLocaleString('id-ID')
    },
    updateTotalPayment() {
      const courierPrice = this.selectedCourier ? this.selectedCourier.price : 0
      const totalHarga = this.orders.reduce((total, order) => {
        return total + parseFloat(order.harga_total_item)
      }, 0)
      this.totalPayment = totalHarga + courierPrice
    },
    selectCourier(rate) {
      this.selectedCourier = rate
      this.updateTotalPayment()
    },
    initMap() {
      this.$nextTick(() => {
        if (document.getElementById('mapid')) {
          this.map = L.map('mapid').setView(this.mapCenter, this.zoom)
          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {}).addTo(this.map)
          this.map.on('click', this.updateMarkerPosition)

          // Initialize marker if markerPosition is set
          if (this.markerPosition) {
            this.marker = L.marker(this.markerPosition, { draggable: true }).addTo(this.map)
            this.marker.on('dragend', this.onMarkerDragEnd)
          }
        }
      })
    },
    openModal() {
      this.dialog = true
      this.$nextTick(() => {
        this.initMap()
      })
    },
    searchWithDelay() {
      this.loadingRegist = true
      if (this.searchTimeout) {
        clearTimeout(this.searchTimeout)
      }
      this.searchTimeout = setTimeout(this.searchAddress, 2000)
    },
    async searchAddress() {
      if (this.searchQuery && this.searchQuery.length > 2) {
        try {
          const response = await axios.get(`https://nominatim.openstreetmap.org/search`, {
            params: {
              q: this.searchQuery,
              format: 'json'
            }
          })
          this.searchResults = response.data
        } catch (error) {
          console.error('Error fetching location:', error)
          this.searchResults = []
        } finally {
          this.loadingRegist = false
        }
      } else {
        this.searchResults = []
      }
    },
    selectAddress(address) {
      const displayName = address.display_name
      const parts = displayName.split(',')

      let kode_pos = ''
      let kecamatan = ''
      let kota = ''
      let provinsi = ''

      if (parts.length >= 6) {
        kecamatan = parts[parts.length - 6].trim()
        kota = parts[parts.length - 5].trim()
        provinsi = parts[parts.length - 4].trim()
        kode_pos = parts[parts.length - 2].trim()
      }

      this.selectedAddress = address
      this.mapCenter = [parseFloat(address.lat), parseFloat(address.lon)]
      this.markerPosition = [parseFloat(address.lat), parseFloat(address.lon)]
      this.address.latitude = address.lat
      this.address.longitude = address.lon
      this.address.kode_pos = kode_pos
      this.address.kecamatan = kecamatan
      this.address.kota = kota
      this.address.provinsi = provinsi

      // Update map view and marker position, and set zoom to 16
      this.map.setView(this.mapCenter, 22)
      if (this.marker) {
        this.marker.setLatLng(this.markerPosition)
      } else {
        this.marker = L.marker(this.markerPosition, { draggable: true }).addTo(this.map)
        this.marker.on('dragend', this.onMarkerDragEnd)
      }
    },
    async saveAddress() {
      const addressData = {
        id: this.selectedAddresses.id,
        nama_penerima: this.address.nama_penerima,
        nomor_telepon: this.address.nomor_telepon,
        jalan: this.address.jalan,
        kecamatan: this.address.kecamatan,
        kota: this.address.kota,
        provinsi: this.address.provinsi,
        kode_pos: this.address.kode_pos,
        user_id: this.users.id,
        latitude: this.address.latitude,
        longitude: this.address.longitude
      }

      try {
        await axios.post(`${BASE_URL}/address/add`, addressData, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })
        this.dialog = false
        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Successfully Added!',
          color: 'green'
        })
        this.resetForm()
        this.fetchUserAddresses()
      } catch (error) {
        console.error('Error saving address:', error)
      }
    },
    resetForm() {
      this.searchQuery = ''
      this.searchResults = []
      this.address = {
        nama_penerima: '',
        nomor_telepon: '',
        jalan: '',
        kecamatan: '',
        kota: '',
        provinsi: '',
        kode_pos: '',
        latitude: '',
        longitude: ''
      }
      if (this.marker) {
        this.marker.remove()
        this.marker = null
      }
    },
    updateMarkerPosition(event) {
      const { lat, lng } = event.latlng
      this.markerPosition = [lat, lng]
      this.address.latitude = lat
      this.address.longitude = lng
      this.getAddressFromCoordinates(lat, lng)

      if (this.marker) {
        this.marker.setLatLng([lat, lng])
      } else {
        this.marker = L.marker([lat, lng], { draggable: true }).addTo(this.map)
        this.marker.on('dragend', this.onMarkerDragEnd)
      }
    },
    onMarkerDragEnd(event) {
      const { lat, lng } = event.target.getLatLng()
      this.markerPosition = [lat, lng]
      this.address.latitude = lat
      this.address.longitude = lng
      this.getAddressFromCoordinates(lat, lng)
    },
    async getAddressFromCoordinates(lat, lng) {
      try {
        const response = await fetch(
          `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`
        )
        const data = await response.json()

        const address = data.address
        this.address.kecamatan = address.suburb || ''
        this.address.kota = address.city || ''
        this.address.provinsi = address.state || ''
        this.address.jalan = address.road || ''
        this.address.kode_pos = address.postcode || ''

        this.searchQuery = `${this.address.jalan}, ${this.address.kecamatan}, ${this.address.kota}, ${this.address.provinsi}`
      } catch (error) {
        console.error('Error fetching address:', error)
      }
    },
    closeModal() {
      this.resetForm()
      this.dialog = false
    },
    async proceedToCheckout() {
      if (!this.selectedCourier) {
        this.$notify({
          type: 'error',
          title: 'Error',
          text: 'Pilih alamat pengiriman terlebih dahulu'
        })
        return
      }
      this.overlay = true
      try {
        const response = await axios.post(
          `${BASE_URL}/order/checkout`,
          {
            amount: this.totalHarga,
            selectedCourier: this.selectedCourier,
            address_id: this.selectedAddress.id,
            items: this.orders.map((order) => ({
              cart_item_id: order.id,
              quantity: order.quantity,
              totalPrice: order.harga_total_item
            }))
          },
          {
            headers: {
              Authorization: 'Bearer ' + localStorage.getItem('access_token'),
              'Content-Type': 'application/json'
            }
          }
        )

        const { paymentUrl } = response.data
        window.open(paymentUrl, '_blank')
        setTimeout(() => {
          this.$router.push('/order')
        }, 1000) // Adjust the delay as needed
      } catch (error) {
        console.error('Error proceeding to checkout:', error)
      } finally {
        this.overlay = false
      }
    },
    async fetchShippingRates() {
      this.loadingKurir = true
      if (!this.selectedAddress) {
        return
      }
      const payload = {
        latitude: this.selectedAddress.latitude,
        longitude: this.selectedAddress.longitude,
        items: this.orders.map((order) => ({
          name: order.name,
          description: order.deskripsi,
          value: order.harga_dasar,
          length: 10,
          width: 10,
          height: 5,
          weight: 200,
          quantity: order.quantity
        }))
      }

      try {
        const response = await axios.post(`${BASE_URL}/biteship/kurir`, payload, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token'),
            'Content-Type': 'application/json'
          }
        })

        this.shippingRates = response.data.data.pricing
        // Select the cheapest courier
        this.selectedCourier = this.shippingRates.reduce((cheapest, current) => {
          return !cheapest || current.price < cheapest.price ? current : cheapest
        }, null)
      } catch (error) {
        console.error('Error fetching shipping rates:', error)
      } finally {
        this.loadingKurir = false
      }
    },
    fillAddress() {
      this.selectedAddress =
        this.alamat.find((address) => address.id === this.selectedAddressId) || {}
    },
    async fetchUserAddresses() {
      try {
        const response = await axios.get(`${BASE_URL}/address/get`, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })
        this.alamat = response.data
      } catch (error) {
        console.error('Error fetching addresses:', error)
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

        this.orders = response.data.items
        this.totalPayment = parseFloat(response.data.total_harga)
      } catch (error) {
        console.error(error)
      } finally {
        this.overlay = false
      }
    }
  },
  computed: {
    totalHarga() {
      return this.orders.reduce((total, order) => {
        return total + parseFloat(order.harga_total_item)
      }, 0)
    },
    totalBayar() {
      return this.totalHarga + (this.selectedCourier ? this.selectedCourier.price : 0)
    }
  }
}
</script>

<template>
  <navbar />
  <div class="py-3 mt-5 container">
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
          <div class="col-lg-8">
            <div class="row mb-2">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">
                    Pilih Alamat
                    <v-icon
                      class="icon-btn justify-content-end"
                      icon="mdi-plus"
                      color="blue"
                      @click="openModal"
                    ></v-icon>
                  </h5>
                  <div class="row">
                    <select
                      class="form-select"
                      aria-label="Default select example"
                      v-model="selectedAddressId"
                      @change="fillAddress"
                    >
                      <option value="" disabled>Pilih alamat</option>
                      <option
                        v-for="item in alamat"
                        :key="item.id"
                        :value="item.id"
                      >
                      {{ item.nama_penerima }} | {{ item.kecamatan }}, {{ item.kota }}
                      </option>
                    </select>
                  </div>
                  <div v-if="selectedAddress">
                    <hr class="horizontal dark" />
                    <div class="row">
                      <div class="row">
                        <div class="col">Nama Penerima</div>
                        <div class="col">: {{ selectedAddress.nama_penerima }}</div>
                        <div class="col">Nomor Penerima</div>
                        <div class="col">: {{ selectedAddress.nomor_telepon }}</div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="row">
                        <div class="col">Provinsi</div>
                        <div class="col">: {{ selectedAddress.provinsi }}</div>
                        <div class="col">Kota</div>
                        <div class="col">: {{ selectedAddress.kota }}</div>
                      </div>
                    </div>
                    <div class="row">
                      <div class="row">
                        <div class="col">Kecamatan</div>
                        <div class="col">: {{ selectedAddress.kecamatan }}</div>
                        <div class="col">Kode Pos</div>
                        <div class="col">: {{ selectedAddress.kode_pos }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div v-for="(order, index) in orders" :key="index" class="mb-2 card">
                <div class="card-body">
                  <h5 class="card-title">Pesanan {{ index + 1 }}</h5>
                  <div class="row">
                    <div class="col-md-3 col-4">
                      <div class="row">
                        <div class="col">
                          <img :src="order.foto" class="img-fluid" alt="Book image" />
                        </div>
                      </div>
                    </div>
                    <div class="col-md-9 col-8">
                      <div class="row">
                        <div class="col-12">
                          <h5 class="text-truncate">{{ order.name }}</h5>
                          <p class="d-inline">
                            <span class="">{{ order.quantity }} porsi</span> x Rp
                            {{ formatPrice(order.harga_dasar) }}
                          </p>
                        </div>
                        <div class="col-12 d-flex justify-content-end align-items-center mt-2">
                          <span
                            ><strong>Rp {{ formatPrice(order.harga_total_item) }}</strong></span
                          >
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="card sticky-menu">
              <div class="card-body">
                <h3 class="card-title">Rincian Belanja</h3>
                <hr class="horizontal dark" />
                <div class="row ring-bayar">
                  <div class="col-7">Total Harga</div>
                  <div class="col">
                    <p>: Rp {{ formatPrice(totalHarga) }}</p>
                  </div>
                </div>
                <div class="row ring-bayar">
                  <div class="col-7">Biaya pengiriman</div>
                  <div class="col">
                    <p>: Rp. {{ formatPrice(selectedCourier ? selectedCourier.price : 0) }}</p>
                  </div>
                </div>
                <hr class="horizontal dark" />
                <div class="row ring-bayar">
                  <div class="col-7 font-weight-bold">Total Pembayaran</div>
                  <div class="col">
                    <p>
                      : Rp
                      {{ formatPrice(totalBayar) }}
                    </p>
                  </div>
                </div>
                <button class="btn btn-primary w-100" @click="proceedToCheckout">
                  Buat Pesanan
                </button>
              </div>
            </div>
          </div>
        </div>
        <div v-if="dialog">
          <div class="modal-backdrop fade show"></div>
          <div class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header">
                  <h5 class="modal-title">Tambah Alamat</h5>
                  <button type="button" class="btn-close" @click="closeModal"></button>
                </div>
                <div class="modal-body">
                  <!-- Leaflet Map -->
                  <div id="mapid" style="height: 300px"></div>
                  <div class="">
                    <label for="searchQuery" class="form-label">Cari Lokasi</label>
                    <input
                      type="text"
                      class="form-control"
                      id="searchQuery"
                      v-model="searchQuery"
                      @input="searchWithDelay"
                    />
                  </div>
                  <div v-if="searchResults.length" class="mb-3 mt-3">
                    <label for="addressSelect" class="form-label">Pilih Alamat</label>
                    <select
                      class="form-select"
                      id="addressSelect"
                      v-model="selectedAddress"
                      @change="selectAddress(selectedAddress)"
                    >
                      <option
                        v-for="result in searchResults"
                        :key="result.place_id"
                        :value="result"
                      >
                        {{ result.display_name }}
                      </option>
                    </select>
                  </div>
                  <div v-else>
                    <a style="font-size: 11px; color: red"
                      ><i class="fas fa-info-circle" style="color: #ff0000"></i>&nbsp;Masukkan kode
                      pos atau kelurahan atau kota anda</a
                    >
                  </div>
                  <v-progress-linear v-if="loadingRegist" indeterminate></v-progress-linear>
                  <label for="">Nama Penerima</label>
                  <input
                    type="text"
                    v-model="address.nama_penerima"
                    class="form-control mb-2"
                    placeholder="Masukkan nama penerima"
                  />
                  <label for="">Nomor Telepon</label>
                  <input
                    type="text"
                    v-model="address.nomor_telepon"
                    class="form-control mb-2"
                    placeholder="Masukkan nomor telepon"
                  />
                  <label for="">Jalan</label>
                  <input
                    type="text"
                    v-model="address.jalan"
                    class="form-control mb-2"
                    placeholder="Masukkan nama jalan dan nomor jalannya"
                  />
                  <label for="">Kecamatan</label>
                  <input
                    type="text"
                    v-model="address.kecamatan"
                    class="form-control mb-2"
                    placeholder="Masukkan kecamatan penerima"
                  />
                  <label for="">Kota</label>
                  <input
                    type="text"
                    v-model="address.kota"
                    class="form-control mb-2"
                    placeholder="Masukkan kota penerima"
                  />
                  <label for="">Provinsi</label>
                  <input
                    type="text"
                    v-model="address.provinsi"
                    class="form-control mb-2"
                    placeholder="Masukkan provinsi penerima"
                  />
                  <label for="">Kode Pos</label>
                  <input
                    type="text"
                    v-model="address.kode_pos"
                    class="form-control mb-2"
                    placeholder="Masukkan kode pos penerima"
                  />
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" @click="closeModal">Close</button>
                  <button type="button" class="btn btn-primary" @click="saveAddress">
                    Save changes
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

<style scoped>
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

.ring-bayar {
  font-size: 14px;
}

.sticky-menu {
  position: sticky;
  top: 100px;
  padding: 3px;
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
    width: 30px;
    margin-right: 10px;
  }
}

.modal-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.5);
  z-index: 1040;
}

.modal {
  display: block;
  z-index: 1050;
}

.modal-header {
  background-color: #f8f9fa;
}

.modal-footer {
  background-color: #f8f9fa;
}

hr.horizontal {
  border-top: 1px solid #dee2e6;
}

hr.horizontal.dark {
  border-top: 1px solid #343a40;
}

#mapid {
  height: 300px;
}
</style>

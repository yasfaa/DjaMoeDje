<script>
import axios from 'axios'
import 'leaflet/dist/leaflet.css'
import { LMap, LTileLayer, LMarker } from '@vue-leaflet/vue-leaflet'
const BASE_URL = import.meta.env.VITE_BASE_URL_API
import Navbar from '@/components/DashboardNavbar.vue'

export default {
  components: {
    Navbar,
    LMap,
    LTileLayer,
    LMarker
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
      selectedAddressId: '',
      selectedAddress: {},
      shippingRates: [],
      dialog: false,
      selectedCourier: null
    }
  },

  mounted() {
    this.retrieveCart()
    this.fetchUserAddresses()
  },
  watch: {
    selectedAddressId() {
      console.log('selectedAddressId changed:', this.selectedAddressId)
      this.fillAddress()
      this.fetchShippingRates()
    },
    selectedCourier() {
      this.updateTotalPayment()
    }
  },
  methods: {
    setupPage() {
      this.hideConfigButton = true
      this.showNavbar = true
      this.showSidenav = false
      this.showFooter = false
      this.body.classList.remove('bg-gray-100')
    },
    restorePage() {
      this.hideConfigButton = false
      this.showNavbar = true
      this.showSidenav = true
      this.showFooter = true
      this.body.classList.add('bg-gray-100')
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

      // Mengatur variabel berdasarkan urutan dari belakang
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

      // Update map view and marker position
      this.map.setView(this.mapCenter, this.zoom)
      this.marker.setLatLng(this.markerPosition)
    },

    async saveAddress() {
      const addressData = {
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
    },
    updateMarkerPosition(event) {
      const { lat, lng } = event.latlng
      this.markerPosition = [lat, lng]
      this.address.latitude = lat
      this.address.longitude = lng
      this.getAddressFromCoordinates(lat, lng)
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
        this.address.kecamatan = address.suburb || address.village || ''
        this.address.kota = address.city || address.town || address.county || ''
        this.address.provinsi = address.state || address.region || ''
        this.address.kode_pos = address.postcode || ''
        this.address.latitude = lat
        this.address.longitude = lng
      } catch (error) {
        console.error('Error fetching address from coordinates:', error)
      }
    },
    closeModal() {
      this.resetForm()
      this.dialog = false
    },
    async proceedToCheckout() {
      this.overlay = true
      try {
        const response = await axios.post(
          `${BASE_URL}/order/checkout`,
          {
            amount: this.totalPayment,
            selectedCourier: this.selectedCourier,
            address_id: this.selectedAddress.id,
            items: this.orders.map((order) => ({
              buku_id: order.buku.id,
              quantity: order.quantity,
              totalPrice: order.totalPrice
            })),
            first_name: 'Farrel',
            last_name: '',
            email: 'farrel@example.com',
            phone: '815559800895'
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
          this.$router.push('/orders')
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
        this.alamat.find((address) => address.selected_address_id === this.selectedAddressId) || {}
      console.log('Selected address:', this.selectedAddress)
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
    async updateQuantity(index, id, newQuantity) {
      if (newQuantity < 1) return

      try {
        await axios.put(
          `${BASE_URL}/cart/update/` + id,
          { quantity: newQuantity },
          {
            headers: {
              Authorization: 'Bearer ' + localStorage.getItem('access_token')
            }
          }
        )
        this.orders[index].quantity = newQuantity
        this.orders[index].totalPrice = newQuantity * this.orders[index].harga

        this.retrieveCart()
      } catch (error) {
        console.error(error)
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
                      @click="dialog = true"
                    ></v-icon>
                  </h5>
                  <div class="row">
                    <select
                      class="form-select"
                      aria-label="Default select example"
                      v-model="selectedAddressId"
                    >
                      <option value="" disabled>Pilih alamat</option>
                      <option
                        v-for="item in alamat"
                        :key="item.selected_address_id"
                        :value="item.selected_address_id"
                      >
                        {{ item.nama_penerima }} | {{ item.kecamatan }}, {{ item.kota }}
                      </option>
                    </select>
                  </div>
                  <div v-if="selectedAddressId">
                    <hr class="horizontal dark" />
                    <div class="row">
                      <div class="row">
                        <div class="col">Nama Penerima</div>
                        <div class="col">: {{ selectedAddress.penerima }}</div>
                        <div class="col">Nomor Penerima</div>
                        <div class="col">: {{ selectedAddress.no_penerima }}</div>
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
                        <div class="col">: {{ selectedAddress.postal_code }}</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row mb-2" v-if="selectedAddressId">
              <div class="card">
                <div class="card-body">
                  <h5 class="card-title">Pilih Kurir</h5>
                  <v-progress-linear
                    v-if="loadingKurir"
                    color="amber"
                    indeterminate
                    :size="69"
                    :width="6"
                  ></v-progress-linear>
                  <div
                    v-else
                    v-for="(rate, index) in shippingRates"
                    :key="index"
                    class="row border mb-3"
                    style="border-radius: 10px"
                  >
                    <div class="col-sm-12">
                      <div class="p-2">
                        <div class="row align-items-center py-2">
                          <div class="col-1 d-flex align-items-center">
                            <input
                              type="checkbox"
                              class="large-checkbox"
                              :checked="selectedCourier === rate"
                              @change="selectCourier(rate)"
                            />
                          </div>
                          <div class="col-2 d-flex align-items-center">
                            <img
                              v-if="rate.company === 'jne'"
                              src="../../assets/img/jne.png"
                              alt="jne"
                              class="img-fluid"
                              style="width: 50px; margin-right: 20px"
                            />
                            <img
                              v-if="rate.company === 'sicepat'"
                              src="../../assets/img/sicepat.png"
                              alt="sicepat"
                              class="img-fluid"
                              style="width: 50px; margin-right: 20px"
                            />
                          </div>
                          <div class="col-3">
                            <div class="row">
                              <strong class="d-block d-sm-inline">Jenis Layanan</strong>
                            </div>
                            <div class="row">
                              <a class="d-block d-sm-inline"
                                >{{ rate.courier_name }} {{ rate.courier_service_name }}</a
                              >
                            </div>
                          </div>
                          <div class="col-4">
                            <div class="row">
                              <strong class="d-block d-sm-inline">Estimasi Pengiriman</strong>
                            </div>
                            <div class="row">
                              <a class="d-block d-sm-inline">{{ rate.duration }}</a>
                            </div>
                          </div>
                          <div class="col-2">
                            <div class="row">
                              <strong class="d-block d-sm-inline">Tarif</strong>
                            </div>
                            <div class="row">
                              <a class="d-block d-sm-inline">Rp. {{ formatPrice(rate.price) }}</a>
                            </div>
                          </div>
                        </div>
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
                        <!-- <div class="col">
                          <img :src="order.foto" class="img-fluid" alt="Book image" />
                        </div> -->
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
                <div class="py-2">
                  <h3 class="card-title">Rincian Belanja</h3>
                </div>
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
                  <div id="mapid">
                    <LMap
                      :zoom="zoom"
                      :center="mapCenter"
                      style="height: 300px"
                      @click="updateMarkerPosition"
                    >
                      <LTileLayer
                        url="https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png"
                      ></LTileLayer>
                      <LMarker v-if="markerPosition" :lat-lng="markerPosition"></LMarker>
                    </LMap>
                  </div>
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
</style>

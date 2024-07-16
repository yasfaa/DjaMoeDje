<script>
import { ref, onMounted, watch } from 'vue'
import { useStore } from 'vuex'
import { useRouter } from 'vue-router'
import axios from 'axios'
import Navbar from '@/components/Navbar.vue'
import { formatPrice } from '@/utils/formatPrice'
import L from 'leaflet'
import 'leaflet/dist/leaflet.css'

export default {
  components: {
    Navbar
  },
  setup() {
    const store = useStore()
    const router = useRouter()

    const alamat = ref([])
    const selectedAddress = ref(null)
    const selectedAddressId = ref('')

    const totalHarga = ref(0)
    const totalBayar = ref(0)
    const orders = ref([])
    const selectedCourier = ref(null)
    const overlay = ref(false)
    const dialog = ref(false)

    const searchQuery = ref('')
    const searchResults = ref([])
    const loadingRegist = ref(false)

    const address = ref({
      nama_penerima: '',
      nomor_telepon: '',
      jalan: '',
      kecamatan: '',
      kota: '',
      provinsi: '',
      kode_pos: '',
      latitude: '',
      longitude: ''
    })

    const formatPrice = (value) => {
      return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR'
      }).format(value)
    }

    const openModal = () => {
      dialog.value = true
    }

    const closeModal = () => {
      dialog.value = false
    }

    const saveAddress = async () => {
      try {
        loadingRegist.value = true
        await axios.post('/api/address', address.value)
        loadingRegist.value = false
        dialog.value = false
        loadAddresses()
      } catch (error) {
        console.error(error)
        loadingRegist.value = false
      }
    }

    const loadAddresses = async () => {
      try {
        const response = await axios.get('/api/addresses')
        alamat.value = response.data
        if (alamat.value.length) {
          selectedAddressId.value = alamat.value[0].id
        }
      } catch (error) {
        console.error(error)
      }
    }

    const updateLocation = (location) => {
      address.value.latitude = location.lat
      address.value.longitude = location.lng
    }

    const searchWithDelay = () => {
      if (searchQuery.value.length > 3) {
        setTimeout(async () => {
          const response = await axios.get(
            `https://nominatim.openstreetmap.org/search?q=${searchQuery.value}&format=json&addressdetails=1`
          )
          searchResults.value = response.data
        }, 1000) 
      }
    }

    const selectAddress = (address) => {
      searchResults.value = []
      searchQuery.value = address.display_name
      address.value.latitude = address.lat
      address.value.longitude = address.lon
    }

    const proceedToCheckout = () => {
      router.push('/checkout')
    }

    watch(selectedAddressId, (newVal) => {
      selectedAddress.value = alamat.value.find((address) => address.id === newVal)
    })

    onMounted(() => {
      loadAddresses()
      totalHarga.value = store.getters.getTotalHarga
      totalBayar.value = store.getters.getTotalBayar
      orders.value = store.getters.getOrders
      selectedCourier.value = store.getters.getSelectedCourier
    })

    return {
      formatPrice,
      alamat,
      selectedAddress,
      selectedAddressId,
      totalHarga,
      totalBayar,
      orders,
      selectedCourier,
      overlay,
      dialog,
      searchQuery,
      searchResults,
      loadingRegist,
      address,
      openModal,
      closeModal,
      saveAddress,
      updateLocation,
      searchWithDelay,
      selectAddress,
      proceedToCheckout
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
                    >
                      <option value="" disabled>Pilih alamat</option>
                      <option
                        v-for="item in alamat"
                        :key="item.id"  <!-- Use a unique key here -->
                        :value="item.id"  <!-- Use the actual address ID -->
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
                  <label for="">Latitude</label>
                  <input
                    type="text"
                    v-model="address.latitude"
                    class="form-control mb-2"
                    readonly
                  />
                  <label for="">Longitude</label>
                  <input
                    type="text"
                    v-model="address.longitude"
                    class="form-control mb-2"
                    readonly
                  />
                </div>
                <div class="modal-footer">
                  <button
                    type="button"
                    class="btn btn-secondary"
                    @click="closeModal"
                  >
                    Batal
                  </button>
                  <button type="button" class="btn btn-primary" @click="saveAddress">
                    Simpan Alamat
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <leaflet-map @locationSelected="updateLocation"></leaflet-map>
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

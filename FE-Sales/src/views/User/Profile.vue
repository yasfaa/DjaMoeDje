<script>
import axios from 'axios'
import 'leaflet/dist/leaflet.css'
import L from 'leaflet'
const BASE_URL = import.meta.env.VITE_BASE_URL_API
import Navbar from '@/components/DashboardNavbar.vue'
import markerIconUrl from '@/assets/img/marker.png'

const defaultIcon = L.icon({
  iconUrl: markerIconUrl,
  iconSize: [38, 45],
  iconAnchor: [20, 50],
  popupAnchor: [-1, -1]
})

export default {
  name: 'Profile',
  components: {
    Navbar
  },
  data() {
    return {
      dialog1: false,
      dialog2: false,
      users: {
        name: '',
        email: ''
      },
      loadingRegist: false,
      searchQuery: '',
      searchResults: [],
      selectedAddress: null,
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
      alamat: [],
      selectedAddressId: null,
      confirmdeletion: false,
      mapCenter: [-6.17511, 106.865036],
      zoom: 13,
      markerPosition: null,
      map: null,
      marker: null
    }
  },
  mounted() {
    this.getUser()
    this.fetchUserAddresses()
    this.initMap()
  },
  methods: {
    initMap() {
      this.$nextTick(() => {
        if (document.getElementById('mapid')) {
          this.map = L.map('mapid').setView(this.mapCenter, this.zoom)
          L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {}).addTo(this.map)
          this.map.on('click', this.updateMarkerPosition)

          if (this.markerPosition) {
            this.marker = L.marker(this.markerPosition, {
              draggable: true,
              icon: defaultIcon
            }).addTo(this.map)
            this.marker.on('dragend', this.onMarkerDragEnd)
          }
        }
      })
    },
    openModal() {
      this.dialog1 = true
      this.loadingRegist = false
      this.$nextTick(() => {
        this.initMap()
      })
    },
    openManualFormModal() {
      this.dialog1 = false
      this.dialog2 = true
      this.resetForm()
      this.$nextTick(() => {
        this.initMap()
      })
    },
    openAddressFormModal(selectedAddress) {
      this.dialog1 = false
      this.dialog2 = true
      this.$nextTick(() => {
        this.initMap()
        this.selectAddress(selectedAddress)
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

      this.$nextTick(() => {
        if (this.map) {
          this.map.setView(this.mapCenter, 22)

          if (this.marker) {
            this.marker.setLatLng(this.markerPosition)
          } else {
            this.marker = L.marker(this.markerPosition, {
              draggable: true,
              icon: defaultIcon
            }).addTo(this.map)
            this.marker.on('dragend', this.onMarkerDragEnd)
          }
        } else {
          console.error('Map is still not initialized.')
        }
      })
    },
    async saveAddress() {
      if (
        !this.address.nama_penerima ||
        !this.address.nomor_telepon ||
        !this.address.jalan ||
        !this.address.kecamatan ||
        !this.address.kota ||
        !this.address.provinsi ||
        !this.address.kode_pos ||
        !this.address.latitude ||
        !this.address.longitude
      ) {
        this.$notify({
          type: 'error',
          title: 'Error',
          text: 'Pastikan semua kolom sudah terisi.',
          color: 'red'
        })
      }
      if (!this.address.latitude || !this.address.longitude) {
        this.$notify({
          type: 'error',
          title: 'Error',
          text: 'Pastikan telah menaruh titik lokasi pengiriman.',
          color: 'red'
        })
      }
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
        this.dialog2 = false
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
      ;(this.searchResults = []),
        (this.address = {
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
      if (this.marker) {
        this.marker.remove()
        this.marker = null
      }
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
    async deleteAddress(id) {
      try {
        await axios.delete(`${BASE_URL}/address/delete/` + id, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })
        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Address successfully deleted',
          color: 'green'
        })
        this.fetchUserAddresses()
      } catch (error) {
        console.error(error)
      }
    },
    openDeleteConfirmation(id) {
      this.selectedAddressId = id
      this.confirmdeletion = true
    },
    confirmDelete() {
      if (this.selectedAddressId) {
        this.deleteAddress(this.selectedAddressId)
        this.confirmdeletion = false
      }
    },
    closeModal() {
      this.resetForm()
      this.dialog1 = false
      this.dialog2 = false
    },
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
    async updateUser() {
      try {
        await axios.post(`${BASE_URL}/auth/update`, this.users, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })
        localStorage.setItem('name', this.users.name)
        window.location.reload()
        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Successfully Updated!',
          color: 'green'
        })
      } catch (error) {
        console.error(error)
        if (error.response && error.response.data.message) {
          this.$notify({
            type: 'error',
            title: 'Error',
            text: 'isi email dengan format email'
          })
          console.log(errorMessage)
        }
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
        this.marker = L.marker([lat, lng], { draggable: true, icon: defaultIcon }).addTo(this.map)
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
    }
  },
  watch: {
    searchQuery(newQuery) {
      this.searchWithDelay()
    },
    selectedAddress(newAddress) {
      if (newAddress) {
        this.mapCenter = [parseFloat(newAddress.lat), parseFloat(newAddress.lon)]
        this.markerPosition = [parseFloat(newAddress.lat), parseFloat(newAddress.lon)]
      }
    }
  }
}
</script>
<template>
  <navbar />
  <div class="border-main">
    <div class="py-4 container-fluid">
      <div class="row">
        <div class="col-md-7 mt-6">
          <!-- Profil Card -->
          <div class="card shadow-lg">
            <div class="card-header pb-0">
              <div class="d-flex align-items-center">
                <h4>Edit Profil</h4>
              </div>
            </div>
            <div class="card-body">
              <!-- Form untuk mengedit profil -->
              <p class="text-uppercase fw-bolder">Informasi Pengguna</p>
              <div class="row">
                <div class="col-md-6">
                  <label for="username" class="form-control-label">Username</label>
                  <input
                    type="text"
                    v-model="users.name"
                    class="form-control"
                    id="username"
                    required
                  />
                </div>
                <div class="col-md-6">
                  <label for="email" class="form-control-label">Email</label>
                  <input
                    type="email"
                    v-model="users.email"
                    class="form-control"
                    id="email"
                    required
                  />
                </div>
              </div>
              <button class="btn btn-primary btn-sm ms-auto mt-3" @click="updateUser">
                Update Profil
              </button>
            </div>
          </div>
        </div>
        <div class="col-md-5 mt-5">
          <!-- Alamat Card -->
          <div class="card shadow-lg mt-4">
            <div class="card-header pb-0">
              <div class="d-flex align-items-center">
                <h4>Alamat</h4>
              </div>
            </div>
            <div class="card-body">
              <!-- Daftar Alamat -->
              <div v-for="alamatItem in alamat" :key="alamatItem.id" class="mb-3">
                <div class="row">
                  <p class="text-xs">Nama Penerima: {{ alamatItem.nama_penerima }}</p>
                  <p class="text-xs">Nomor Telepon: {{ alamatItem.nomor_telepon }}</p>
                  <p class="text-xs">
                    Alamat: {{ alamatItem.jalan }}, {{ alamatItem.kecamatan }},
                    {{ alamatItem.kota }}, {{ alamatItem.provinsi }}, {{ alamatItem.kode_pos }}
                  </p>
                </div>
                <button
                  class="btn btn-danger btn-sm"
                  @click="openDeleteConfirmation(alamatItem.id)"
                >
                  Delete
                </button>
                <hr class="horizontal dark" />
              </div>
              <button class="btn btn-primary btn-sm ms-auto" @click="openModal">
                Tambah Alamat
              </button>
              <!-- Modal untuk menambah alamat -->
              <div v-if="dialog1">
                <div class="modal-backdrop fade show"></div>
                <div class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
                  <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Cari Alamat</h5>
                        <v-icon size="large" @click="closeModal" color="red">mdi-close</v-icon>
                      </div>
                      <div class="modal-body">
                        <div class="">
                          <label for="searchQuery" class="form-label">Cari Lokasi</label>
                          <input
                            type="text"
                            class="form-control"
                            id="searchQuery"
                            v-model="searchQuery"
                            @input="searchWithDelay"
                            placeholder="Cari Alamat Anda"
                          />
                        </div>
                        <div v-if="searchResults.length" class="mb-3 mt-3">
                          <label for="addressSelect" class="form-label">Pilih Alamat</label>
                          <select
                            class="form-select"
                            id="addressSelect"
                            v-model="selectedAddress"
                            @change="openAddressFormModal(selectedAddress)"
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
                        <div v-else></div>
                        <v-progress-linear v-if="loadingRegist" indeterminate></v-progress-linear>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" @click="closeModal">
                          Cancel
                        </button>
                        <button type="button" class="btn btn-primary" @click="openManualFormModal">
                          Isi Manual
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div v-if="dialog2">
                <div class="modal-backdrop fade show"></div>
                <div class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
                  <div class="modal-dialog modal-dialog-centered modal-lg">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Tambah Alamat</h5>
                        <v-icon size="large" @click="closeModal" color="red">mdi-close</v-icon>
                      </div>
                      <div class="modal-body">
                        <!-- Leaflet Map -->
                        <div id="mapid" style="height: 300px"></div>
                        <v-progress-linear v-if="loadingRegist" indeterminate></v-progress-linear>
                        <a style="font-size: 11px; color: grey">
                          Pastikan titik sesuai dengan alamat pengiriman</a
                        >
                        <br />
                        <!-- Input untuk detail alamat -->
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
                        <button type="button" class="btn btn-secondary" @click="closeModal">
                          Close
                        </button>
                        <button type="button" class="btn btn-primary" @click="saveAddress">
                          Save changes
                        </button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- Modal untuk konfirmasi penghapusan -->
              <div v-if="confirmdeletion">
                <div class="modal-backdrop fade show"></div>
                <div class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-body">
                        <p>Are you sure you want to delete this address?</p>
                      </div>
                      <div class="modal-footer">
                        <button
                          type="button"
                          class="btn btn-secondary"
                          @click="confirmdeletion = false"
                        >
                          Cancel
                        </button>
                        <button type="button" class="btn btn-danger" @click="confirmDelete">
                          Delete
                        </button>
                      </div>
                    </div>
                  </div>
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
.border-main {
  padding: 20px;
}

.card {
  margin-bottom: 1.5rem;
  margin-top: 10px;
}

.card-header {
  background-color: #fff;
  border-bottom: 1px solid #dee2e6;
}

.card-body {
  background-color: #fff;
}

.text-xs {
  font-size: 1rem;
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

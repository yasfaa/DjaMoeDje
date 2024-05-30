<script>
import axios from 'axios'
const BASE_URL = import.meta.env.VITE_BASE_URL_API
import Navbar from '@/components/DashboardNavbar.vue'

export default {
  name: 'Profile',
  components: {
    Navbar
  },
  data() {
    return {
      store: null,
      body: null,
      dialog: false,
      users: {
        name: '',
        email: '',
      },
      loadingRegist: false,
      searchQuery: '',
      searchResults: [],
      selectedAddress: null,
      address: {
        id: '',
        nama_penerima: '',
        nomor_telepon: '',
        jalan: '',
        kecamatan: '',
        kota: '',
        provinsi: '',
        kode_pos: ''
      },
      alamat: [],
      selectedAddressId: null,
      confirmdeletion: false
    }
  },
  created() {
    this.store = this.$store
    this.body = document.getElementsByTagName('body')[0]
  },
  beforeUnmount() {
    this.restorePage()
  },
  mounted() {
    this.getUser()
    this.fetchUserAddresses()
  },
  methods: {
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
          const response = await axios.get(`${BASE_URL}/biteship/areas`, {
            params: {
              countries: 'ID',
              input: this.searchQuery,
              type: 'single'
            }
          })
          this.searchResults = response.data.areas
        } catch (error) {
          console.error('Error fetching address:', error)
          this.searchResults = []
        } finally {
          this.loadingRegist = false
        }
      } else {
        this.searchResults = []
      }
    },
    selectAddress(address) {
      this.selectedAddress = address
      this.fillAddress()
      this.searchResults = []
      this.searchQuery = address.name
    },
    fillAddress() {
      if (this.selectedAddress) {
        this.address.id = this.selectedAddress.id
        this.address.provinsi = this.selectedAddress.administrative_division_level_1_name
        this.address.kota = this.selectedAddress.administrative_division_level_2_name
        this.address.kecamatan = this.selectedAddress.administrative_division_level_3_name
        this.address.kode_pos = this.selectedAddress.postal_code
      }
    },
    async saveAddress() {
      const addressData = {
        id: this.address.id,
        nama_penerima: this.address.nama_penerima,
        nomor_telepon: this.address.nomor_telepon,
        jalan: this.address.jalan,
        kecamatan: this.address.kecamatan,
        kota: this.address.kota,
        provinsi: this.address.provinsi,
        kode_pos: this.address.kode_pos,
        user_id: this.users.id
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
        kode_pos: ''
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
        const response = await axios.delete(`${BASE_URL}/address/delete/` + id, {
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
        const response = await axios.post(`${BASE_URL}/auth/update`, this.users, {
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
          const errorMessage = error.response.data.message
          console.log(errorMessage)
        }
      }
    },
  }
}
</script>

<template>
  <div class="border-main">
    <navbar />
    <div class="py-4 container-fluid">
      <div class="row">
        <div class="col-md-7 mt-6">
          <div class="card shadow-lg">
            <div class="card-header pb-0">
              <div class="d-flex align-items-center">
                <h4>Edit Profil</h4>
              </div>
            </div>
            <div class="card-body">
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
                  <label for="email" class="form-control-label">Email address</label>
                  <input
                    type="email"
                    v-model="users.email"
                    class="form-control"
                    id="email"
                    required
                  />
                </div>
              </div>
              <hr class="horizontal dark" />
              <div class="col-md-12 text-end">
                <button class="btn btn-success btn-sm ms-auto" @click="updateUser">Update</button>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-5 mt-6">
          <div class="card shadow-lg">
            <div class="card-header pb-0">
              <div class="d-flex align-items-center">
                <h4>Alamat</h4>
              </div>
            </div>
            <div class="card-body">
              <div v-for="alamatItem in alamat" :key="alamatItem.id" class="mb-3">
                <div class="row">
                  <p class="text-xs">Nama Penerima: {{ alamatItem.nama_penerima }}</p>
                  <p class="text-xs">Nomor Telepon: {{ alamatItem.nomor_telepon }}</p>
                  <p class="text-xs">
                    Alamat: {{ alamatItem.jalan }}, {{ alamatItem.kecamatan }},
                    {{ alamatItem.kota }},  {{ alamatItem.provinsi }}, {{ alamatItem.kode_pos }}
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
              <button class="btn btn-primary btn-sm ms-auto" @click="dialog = true">
                Tambah Alamat
              </button>
              <!-- Modal for adding address -->
              <div v-if="dialog">
                <div class="modal-backdrop fade show"></div>
                <div class="modal fade show d-block" tabindex="-1" aria-modal="true" role="dialog">
                  <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Tambah Alamat</h5>
                        <button type="button" class="btn-close" @click="dialog = false"></button>
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
                          />
                        </div>

                        <div v-if="searchResults.length" class="mb-3 mt-3">
                          <label for="addressSelect" class="form-label">Pilih Alamat</label>
                          <select
                            class="form-select"
                            id="addressSelect"
                            v-model="selectedAddress"
                            @change="fillAddress"
                          >
                            <option
                              v-for="result in searchResults"
                              :key="result.id"
                              :value="result"
                            >
                              {{ result.name }}
                            </option>
                          </select>
                        </div>
                        <div v-else>
                          <a style="font-size: 11px; color: red"
                            ><i class="fas fa-info-circle" style="color: #ff0000"></i>&nbsp;Masukkan
                            kode pos atau kelurahan atau kota anda</a
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
                        <button type="button" class="btn btn-secondary" @click="dialog = false">
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
              <!-- Modal for confirming deletion -->
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

.btn-close {
  border: none;
  background: transparent;
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
</style>

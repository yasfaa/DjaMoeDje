<template>
  <Navbar>
    <div class="dashboard-admin">
      <div class="container px-4 py-2">
        <v-dialog v-model="dialog1" hide-overlay persistent width="300" lazy>
          <v-card color="light">
            <v-card-text>
              Saving personalization
              <v-progress-linear indeterminate color="red" class="mb-0"></v-progress-linear>
            </v-card-text>
          </v-card>
        </v-dialog>
        <div class="row " style="min-height: 264px ;">
          <div class="col-md-4">
            <div class="card mb-4 pt-4 border-0 px-2">
              <div v-if="loadingProfile">
                <div class="d-flex justify-content-center align-itemsx-center">
                  <v-progress-circular indeterminate></v-progress-circular>
                </div>
              </div>
              <div v-else>
                <div class="col px-4">
                  <form @submit.prevent="saveProfile">
                    <div class="row">
                    </div>
                    <label for="judul">Nama</label>
                    <input class="form-control" type="text" v-model="users.name" id="judul" />
                    <br>
                    <label for="exampleKategori" class="form-label">Jenis Kelamin</label>
                    <br>
                    <label for="desc">Email</label>
                    <input type="email" disabled class="form-control" v-model="users.email" id="desc"/>
                    <br>
                    <button-custom class="btn btn-info mb-2" type="submit" @click="saveProfile">Save
                      Profile</button-custom>
                  </form>
                </div>
              </div>
            </div>
          </div>
          <div class="col-md-8 ">
            <div class="card border-0">
              <div class="row d-flex align-items-center justify-content-center my-2 mx-2" v-if="addresses.length < 2">
                <div class="col-md-2">
                  <v-btn color="red" rounded="xl" @click="openDialog">
                    Add Address
                  </v-btn>
                </div>
              </div>
              <div class="row mx-2 ">
                <div class="col-md-6 mt-2 " v-for="(address, index) in addresses" :key="index">
                  <h3 style="font-weight: bold; font-size: 20px;">Address {{ index + 1 }} <v-icon size="large"
                      @click="confirmDelete(index)" color="red">
                      mdi-delete
                    </v-icon></h3>
                  <a>City: {{ address.city }}</a>
                  <br>
                  <a>Province: {{ address.province }}</a>
                  <br>
                  <a>Kode Pos: {{ address.postal_code }}</a>
                  <br>
                  <a>Alamat: {{ address.address_detail }}</a>
                  <br>
                  <a>No Telpon: {{ address.phone_number }}</a>
                  <br>
                  <a>Penerima: {{ address.receiver }}</a>
                  <br>
                  <v-chip class="my-2">{{ address.label }}</v-chip>
                </div>
              </div>
            </div>
          </div>
          <v-dialog v-model="deleteDialog" max-width="500px">
            <v-card title="Confirm Delete">
              <v-card-text>
                Are you sure you want to remove this address?
              </v-card-text>
              <v-card-actions>
                <v-btn text @click="deleteDialog = false">Cancel</v-btn>
                <v-btn color="red" @click="deleteItem()">Delete</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
          <v-dialog v-model="dialogForm" max-width="600">
            <v-card>
              <v-card-title>
                Choose Province and City
              </v-card-title>
              <v-card-text>
                <a>Select Province :</a>
                <select v-model="selectedProvinceId" class="form-select" @change="handleProvinceChange">
                  <option value="" disabled>Select Province</option>
                  <option v-for="province in provinces" :key="province.province_id" :value="province.province_id">{{
          province.province }}</option>
                </select>
                <br>
                <a>Select Cities :</a>
                <div v-if="loadingCity">
                  <v-progress-linear indeterminate></v-progress-linear>
                </div>
                <div v-else>
                  <select v-model="selectedCityId" class="form-select" @change="updatePostalCode">
                    <option value="" disabled>Select City</option>
                    <option v-for="city in cities" :key="city.city_id" :value="city.city_id">{{ city.city_name }}
                    </option>
                  </select>
                </div>
                <br>
                <label for="postalCode">Postal Code:</label>
                <input type="text" class="form-control" id="postalCode" v-model="postalCode" :disabled="true">
                <br>
                <label for="addressDetail">Address Detail:</label>
                <textarea class="form-control" id="addressDetail" v-model="addressDetail"></textarea>
                <br>
                <label for="phoneNumber">Phone Number:</label>
                <input type="text" class="form-control" id="phoneNumber" v-model="phoneNumber">
                <br>
                <label for="receiver">Receiver:</label>
                <input type="text" class="form-control" id="receiver" v-model="receiver">
                <br>
                <label for="label">Label:</label>
                <input type="text" class="form-control" id="label" v-model="label">
              </v-card-text>
              <v-card-actions>
                <v-btn color="blue darken-1" text @click="dialogForm = false">Cancel</v-btn>
                <v-btn color="blue darken-1" text @click="saveAddress">Save</v-btn>
              </v-card-actions>
            </v-card>
          </v-dialog>
        </div>
      </div>
    </div>
  </Navbar>
</template>

<script>
import Navbar from '@/components/DashboardNavbar.vue'
import axios from 'axios';
const BASE_URL = import.meta.env.VITE_BASE_URL_API;


export default {
  name: 'profile',
  components: {
    Navbar,
  },
  data() {
    return {
      users: {
        id: '',
        name: '',
        email: '',
      },
      dialog1: false,
      deleteDialog: false,
      deleteIndex: null,
      dialogForm: false,
      provinces: [],
      selectedProvince: null,
      cities: [],
      selectedProvinceId: null,
      selectedCityId: null,
      loadingCity: false,
      loadingProfile: false,

      addressDetail: '',
      phoneNumber: '',
      receiver: '',
      label: '',
      postalCode: '',
      addresses: [],
    }
  },
  async mounted() {
    try {
      this.loadingProfile = true;
      const response = await axios.get(BASE_URL + '/user', {
        headers: {
          Authorization: "Bearer " + localStorage.getItem('access_token')
        }
      });
      this.users = response.data
    } catch (error) {
      console.error(error);

      if (error.response && error.response.data.message) {
        const errorMessage = error.response.data.message;
        this.$notify({
          type: 'error',
          title: 'Error',
          text: errorMessage,
          color: 'red'
        });
      }
    } finally {
      this.loadingProfile = false;
    };
  },
  methods: {
    async saveProfile() {
      try {
        this.dialog1 = true;
        const user = {
          name: this.users.name,
          email: this.users.email,
          jenis_kelamin: this.users.jenis_kelamin
        };
        await axios.put(BASE_URL + `user/update/${this.users.id}`, user, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        this.dialog1 = false;
      } catch (error) {
        console.error(error);
        this.dialog1 = false;
      }
    },
    async retrieveAddress() {
      try {
        const token = localStorage.getItem('access_token');
        const response = await axios.get(BASE_URL + '/address', {
          headers: {
            Authorization: 'Bearer ' + token
          }
        });
        this.addresses = response.data;
      } catch (error) {
        console.error('Error fetching addresses:', error);
      }
    },
    async saveProfile() {
      try {
        const updatedUserData = {
          name: this.users.name,
          jenis_kelamin: this.users.jenis_kelamin
        };
        const response = await axios.put(BASE_URL + '/user/update', updatedUserData, {
          headers: {
            Authorization: "Bearer " + localStorage.getItem('access_token')
          }
        });
        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Namaberhasil diubah',
          color: 'green'
        });
        console.log('Profile updated successfully:', response.data);
      } catch (error) {
        console.error('Error updating profile:', error);
        if (error.response && error.response.data.message) {
          const errorMessage = error.response.data.message;
          this.$notify({
            type: 'error',
            title: 'Error',
            text: errorMessage,
            color: 'red'
          });
        }
      }
    },
    openDialog() {
      this.dialogForm = true;
    },
    async saveAddress() {
      try {
        const address = {
          province_id: this.selectedProvinceId,
          city_id: this.selectedCityId,
          jalan: this.addressDetail,
          kode_pos: this.postalCode,
          nomor_telepon: this.phoneNumber,
          nama_penerima: this.receiver,
          label: this.label,
        };
        const response = await axios.post(BASE_URL + 'address', address, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        this.addresses.push({
          ...address,
          kota: response.data.city_name,
          kecamatan: response.data.province_name,
        });
        this.dialogForm = false;
      } catch (error) {
        console.error(error);
      }
    },
    confirmDelete(index) {
      this.deleteIndex = index;
      this.deleteDialog = true;
    },
    async deleteItem() {
      try {
        const addressId = this.addresses[this.deleteIndex].id;
        await axios.delete(BASE_URL + `address/${addressId}`, {
          headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}`
          }
        });
        this.addresses.splice(this.deleteIndex, 1);
        this.deleteDialog = false;
      } catch (error) {
        console.error(error);
        this.deleteDialog = false;
      }
    },
  }
}
</script>

<style scoped>
.card {
  box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.15);
}

a {
  text-decoration: none;
  color: black;
}
</style>

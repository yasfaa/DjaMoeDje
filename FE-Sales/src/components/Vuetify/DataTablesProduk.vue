<template>
  <v-card flat>
    <v-card-title class="d-flex align-center pe-2">
      Daftar Menu
      <v-spacer></v-spacer>
      <v-text-field
        v-model="search"
        density="compact"
        label="Search"
        variant="solo-filled"
        flat
        hide-details
        single-line
      ></v-text-field>
      <div class="button-set mx-4">
        <v-btn
          color="#b9a119"
          rounded="l"
          data-bs-toggle="modal"
          data-bs-target="#addMenu"
          class=""
        >
          Tambah Menu
        </v-btn>
      </div>
    </v-card-title>

    <v-divider></v-divider>

    <v-data-table v-model:search="search" :items="menus">
      <template v-slot:item.id="{ item }">
        <div v-if="false">{{ item.id }}</div>
      </template>
      <template v-slot:header.id> </template>

      <template v-slot:item.nama="{ item }">
        <div class="text-start">{{ item.nama }}</div>
      </template>

      <template v-slot:item.harga="{ item }">
        <div class="text-start">Rp. {{ formatPrice(item.harga) }}</div>
      </template>

      <template v-slot:item.deskripsi="{ item }">
        <div>{{ item.deskripsi }}</div>
      </template>
      <template v-slot:item.gambar="{ item }">
        <v-row>
          <v-col
            v-for="(gambar, index) in item.gambar"
            :key="index"
            cols="12"
            sm="6"
            md="4"
            lg="3"
            class="mx-2"
          >
            <img
              :src="gambar ? gambar : 'https://via.placeholder.com/150'"
              height="150"
              alt="Menu Image"
            />
          </v-col>
        </v-row>
      </template>
      <template v-slot:item.actions="{ item }">
        <v-icon size="large" class="me-2" @click="editProduk(item)" color="blue">
          mdi-pencil
        </v-icon>
        <v-icon size="large" @click="confirmDelete(item)" color="red"> mdi-delete </v-icon>
      </template>
    </v-data-table>
    <v-dialog v-model="dialogDelete" max-width="500px">
      <v-card>
        <v-card-title>Confirm Delete</v-card-title>
        <v-card-text>Are you sure you want to delete this item?</v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="dialogDelete = false">Cancel</v-btn>
          <v-btn color="red" @click="deleteMenu()">OK</v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-card>
</template>

<script>
import { ref } from 'vue'
import axios from 'axios'

export default {
  data() {
    return {
      BASE_URL: import.meta.env.VITE_BASE_URL_API,
      search: '',
      menus: [],
      itemToDeleteId: null,
      dialogDelete: false
    }
  },
  methods: {
    async retrieveMenus() {
      try {
        const response = await axios.get(this.BASE_URL + '/menu/get', {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })
        this.menus = response.data.menus.map((menu) => {
          return {
            id: menu.id,
            nama: menu.nama,
            harga: menu.total,
            deskripsi: menu.deskripsi,
            gambar: menu.imagePaths,
            actions: ''
          }
        })
      } catch (error) {
        console.error('Error fetching menus:', error)
      }
    },
    formatPrice(price) {
      const numericPrice = parseFloat(price)
      return numericPrice.toLocaleString('id-ID')
    },
    confirmDelete(item) {
      this.itemToDeleteId = item.id
      this.dialogDelete = true
    },
    async deleteMenu() {
      try {
        const response = await axios.delete(this.BASE_URL + '/menu/delete/' + this.itemToDeleteId, {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })
        this.$notify({
          type: 'success',
          title: 'Success',
          text: 'Menu berhasil dihapus',
          color: 'green'
        })
        this.retrieveMenus()
        this.dialogDelete = false
        this.itemToDeleteId = null
      } catch (error) {
        console.error(error)
      }
    },
    goToMenu(item) {
      this.$router.push('/menu/' + item.id)
    }
  },
  mounted() {
    this.retrieveMenus()
  }
}
</script>

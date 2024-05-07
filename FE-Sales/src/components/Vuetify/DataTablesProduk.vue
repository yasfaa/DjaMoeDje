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
    </v-data-table>
  </v-card>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'
const BASE_URL = import.meta.env.VITE_BASE_URL_API

const search = ref('')
const menus = ref([])

const retrieveMenus = async () => {
  try {
    const response = await axios.get(BASE_URL + '/menu/get', {
      headers: {
        Authorization: 'Bearer ' + localStorage.getItem('access_token')
      }
    })
    menus.value = response.data.menus.map((menu) => {
      return {
        nama: menu.nama,
        harga: menu.total,
        deskripsi: menu.deskripsi,
        gambar: menu.imagePaths
      }
    })
  } catch (error) {
    console.error('Error fetching menus:', error)
  }
}

const formatPrice = (price) => {
  const numericPrice = parseFloat(price)
  return numericPrice.toLocaleString('id-ID')
}

onMounted(() => {
  retrieveMenus()
})

const goToMenu = (menuId) => {
  this.$router.push(`/menu/${menuId}`)
}
</script>
<template>
  <v-card flat>
    <v-card-title class="d-flex align-center pe-2">
      <v-icon icon="mdi-video-input-component"></v-icon> &nbsp; Daftar Menu

      <v-spacer></v-spacer>

      <v-text-field
        v-model="search"
        density="compact"
        label="Search"
        prepend-inner-icon="mdi-magnify"
        variant="solo-filled"
        flat
        hide-details
        single-line
      ></v-text-field>
    </v-card-title>

    <v-divider></v-divider>
    <v-data-table v-model:search="search" :items="menus">
      <template v-slot:item.imagePath="{ item }">
        <img
          :src="item.imagePath ? item.imagePath : 'https://via.placeholder.com/150'"
          height="64"
          alt="Menu Image"
        />
      </template>

      <template v-slot:item.total="{ item }">
        <div class="text-end">{{ item.total }}</div>
      </template>

      <template v-slot:item.deskripsi="{ item }">
        <div>{{ item.deskripsi }}</div>
      </template>

      <template v-slot:item.actions="{ item }">
        <v-btn small color="primary" text @click="goToMenu(item.id)">View</v-btn>
        <v-btn small color="primary" text>Edit</v-btn>
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
    menus.value = response.data.menus
  } catch (error) {
    console.error('Error fetching menus:', error)
  }
}

onMounted(() => {
  retrieveMenus()
})

const goToMenu = (menuId) => {
  // Ganti ini dengan navigasi ke halaman detail menu sesuai dengan ID
  console.log('Navigasi ke halaman menu dengan ID:', menuId)
}
</script>

<style></style>

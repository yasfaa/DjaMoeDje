<template>
  <v-card flat>
    <v-card-title class="d-flex align-center pe-0">
      Daftar Menu
      <v-spacer></v-spacer>
      <v-text-field
        class="search-input"
        v-model="search"
        density="compact"
        label="Search"
        variant="solo-filled"
        flat
        hide-details
        single-line
      ></v-text-field>
      <div class="button-set mx-4">
        <button class="btn btn-primary" @click="emitOpenDialog">Tambah Menu</button>
      </div>
    </v-card-title>
    <v-divider></v-divider>
    <v-data-table v-model:search="search" :items="menus">
      <template v-slot:item.id="{ item }">
        <div v-if="false">{{ item.id }}</div>
      </template>
      <template v-slot:header.id> </template>
      <template v-slot:item.no="{ item }">
        <div class="text-start">
          {{ item.no }}
        </div>
      </template>
      <template v-slot:item.nama="{ item }">
        <div class="text-start">{{ item.nama }}</div>
      </template>

      <template v-slot:item.harga="{ item }">
        <div class="text-start">Rp. {{ formatPrice(item.harga) }}</div>
      </template>

      <template v-slot:item.deskripsi="{ item }">
        <div class="deskripsi">{{ item.deskripsi }}</div>
      </template>

      <template v-slot:item.stock_harian="{ item }">
        <div class="deskripsi">{{ item.stock_harian }}</div>
      </template>

      <template v-slot:item.gambar="{ item }">
        <div class="gambar-cotainer">
          <v-row>
            <v-col
              v-for="(gambar, index) in item.gambar"
              :key="index"
              cols="12"
              sm="6"
              md="4"
              lg="3"
              class="mx-4"
            >
              <img
                :src="gambar ? gambar : 'https://via.placeholder.com/150'"
                height="150"
                alt="Menu Image"
              />
            </v-col>
          </v-row>
        </div>
      </template>
      <template v-slot:item.actions="{ item }">
        <v-icon size="large" class="me-2" @click="detailMenu(item)"> mdi-eye </v-icon>
        <v-icon size="large" class="me-2" @click="editMenu(item)" color="blue"> mdi-pencil </v-icon>
        <v-icon size="large" @click="confirmDelete(item)" color="red"> mdi-delete </v-icon>
      </template>
    </v-data-table>
    <v-dialog v-model="dialogDelete" max-width="500px">
      <v-card>
        <v-card-title>Konfirmasi Hapus</v-card-title>
        <v-card-text>Anda yakin ingin menghapus menu ini?</v-card-text>
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn text @click="dialogDelete = false">Batal</v-btn>
          <v-btn color="red" @click="deleteMenu()">Hapus</v-btn>
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
  mounted() {
    this.retrieveMenus()
  },
  computed: {
    getitems() {
      return this.items.map((item, index) => {
        return { ...item, no: index + 1 }
      })
    }
  },
  methods: {
    emitOpenDialog() {
      this.$emit('open-dialog')
    },
    async retrieveMenus() {
      try {
        const response = await axios.get(this.BASE_URL + '/menu/get', {
          headers: {
            Authorization: 'Bearer ' + localStorage.getItem('access_token')
          }
        })
        this.menus = response.data.menus.map((menu, index) => {
          return {
            id: menu.id,
            no: index + 1,
            nama: menu.nama,
            harga: menu.total,
            deskripsi: menu.deskripsi,
            stok_harian: menu.stok_harian,
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
    editMenu(item) {
      const id = item.id
      localStorage.setItem('editMenuId', id)
      this.$router.push('/admin/edit')
    },
    detailMenu(item) {
      const id = item.id
      localStorage.setItem('editMenuId', id)
      this.$router.push(`/admin/menu/${id}`)
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
    }
  }
}
</script>

<style scoped>
.button-set {
  display: flex;
  gap: 10px;
}

.v-data-table .v-data-table__actions {
  display: flex;
  justify-content: center;
  align-items: center;
}

.deskripsi {
  max-width: 300px;
  white-space: normal;
}

.gambar-container {
  display: flex;
  flex-wrap: wrap;
  justify-content: flex-start;
}

.v-icon {
  cursor: pointer;
}

.v-table__wrapper {
  overflow: none !important;
}

@media (max-width: 600px) {
  .search-input {
    display: none;
  }
}
</style>

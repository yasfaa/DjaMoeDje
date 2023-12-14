<template>
  <main>
    <Navbar />
    <div>
      <v-img
        class="mx-auto my-6"
        max-width="228"
        src="https://d3j2s6hdd6a7rg.cloudfront.net/v2/JE-705/lfc/images/logo.png"
      ></v-img>

      <v-card class="mx-auto pa-12 pb-8" elevation="8" rounded="lg" style="width: 500px">
        <div class="text-subtitle-1 text-medium-emphasis">Email</div>

        <v-text-field
          density="compact"
          placeholder="Email address"
          prepend-inner-icon="mdi-email-outline"
          variant="outlined"
        ></v-text-field>

        <div class="text-subtitle-1 text-medium-emphasis d-flex align-center justify-space-between">
          Password

          <a
            class="text-caption text-decoration-none text-blue"
            href="#"
            rel="noopener noreferrer"
            target="_blank"
          >
            Forgot login password?</a
          >
        </div>

        <v-text-field
          :append-inner-icon="visible ? 'mdi-eye-off' : 'mdi-eye'"
          :type="visible ? 'text' : 'password'"
          density="compact"
          placeholder="Enter your password"
          prepend-inner-icon="mdi-lock-outline"
          variant="outlined"
          @click:append-inner="visible = !visible"
        ></v-text-field>

        <v-btn block class="mb-8" color="blue" size="large" variant="tonal"> Log In </v-btn>

        <v-card-text class="text-center">
          <a
            class="text-blue text-decoration-none"
            href="#"
            rel="noopener noreferrer"
            target="_blank"
          >
            Sign up now <v-icon icon="mdi-chevron-right"></v-icon>
          </a>
        </v-card-text>
      </v-card>
    </div>
  </main>
</template>


<script>
import Navbar from '@/components/Navbar.vue';
export default {
  name: 'LoginPage',
    components: {
        Navbar,
    },
  data() {
    return {
      show1: false,
      show2: true,
      password: 'Password',
      rules: {
        required: (value) => !!value || 'Required.',
        min: (v) => v.length >= 8 || 'Min 8 characters'
      }
    }
  }
}
</script>

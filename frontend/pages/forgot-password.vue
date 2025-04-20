<template>
    <v-container class="fill-height" fluid style="background-color: #fdf3e4;">
      <v-row align="center" justify="center">
        <v-col cols="12" sm="8" md="6" lg="4">
          <v-card class="elevation-12 rounded-lg" style="border-left: 5px solid #34495e;">
            <v-card-title class="primary--text d-flex justify-center align-center flex-column py-6 mb-5">
              <v-img
                src="/img/logoBIG.svg"
                alt="Do!t Logo"
                max-width="120"
                class="mb-4 transition-swing"
                style="filter: brightness(0) invert(0.3);"
              ></v-img>
              <h3 class="font-weight-bold ml-3">Recupero Password</h3>
            </v-card-title>
  
            <v-card-text class="px-8 pb-8">
           
  
              <v-form @submit.prevent="requestOtp" ref="form">
                <v-text-field
                  v-model="email"
                  label="Email"
                  type="email"
                  prepend-inner-icon="mdi-email-outline"
                  outlined
                  rounded
                  :rules="emailRules"
                  class="mb-4"
                  style="border-radius: 28px;"
                ></v-text-field>
  
                <v-btn
                  type="submit"
                  color="primary"
                  x-large
                  block
                  :loading="loading"
                  :disabled="loading"
                  class="font-weight-bold rounded-pill elevation-4"
                  style="letter-spacing: 1px; transform: scale(1); transition: all 0.3s;"
                  @mouseover="hover = true"
                  @mouseleave="hover = false"
                  :style="{ transform: hover ? 'scale(1.02)' : 'scale(1)' }"
                >
                  <v-icon left>mdi-email-send</v-icon>
                  Invia Codice OTP
                </v-btn>
              </v-form>
  
              <div class="text-center mt-6">
                <v-btn
                  text
                  color="grey darken-2"
                  @click="$router.push('/')"
                  class="text-caption font-weight-medium"
                >
                  <v-icon left small>mdi-arrow-left</v-icon>
                  Torna al Login
                </v-btn>
              </div>
            </v-card-text>
  
            <v-divider></v-divider>
  
            <v-card-actions class="pa-4 grey lighten-4">
              <span class="text-caption grey--text text--darken-1 mx-auto">
                © {{ new Date().getFullYear() }} Do!t. Tutti i diritti riservati.
              </span>
            </v-card-actions>
          </v-card>
        </v-col>
      </v-row>
      <v-snackbar 
  v-model="snackbar" 
  :color="snackbarColor" 
  timeout="3000" 
  top 
  shaped 
  elevation="6"
>
  <div class="d-flex align-center">
    <v-icon left dark>{{ snackbarIcon }}</v-icon>
    {{ snackbarText }}
  </div>
  <template v-slot:action="{ attrs }">
    <v-btn text v-bind="attrs" @click="snackbar = false">
      <v-icon>mdi-close</v-icon>
    </v-btn>
  </template>
</v-snackbar>
    </v-container>
  </template>
  
  <script>
  export default {
    data: () => ({
        email: '',
  loading: false,
  hover: false,
  successMessage: '',
  snackbar: false,
  snackbarText: '',
  snackbarColor: 'error',
  snackbarIcon: 'mdi-alert-circle',
  emailRules: [
    v => !!v || 'Email obbligatoria',
    v => /.+@.+\..+/.test(v) || "Inserisci un'email valida"
  ]
    }),
    methods: {
        showSnackbar(message, color = 'error') {
    this.snackbarText = message
    this.snackbarColor = color
    this.snackbarIcon = color === 'success' ? 'mdi-check-circle' : 'mdi-alert-circle'
    this.snackbar = true
  },

  async requestOtp() {
    if (!this.$refs.form.validate()) return;
    
    this.loading = true;
    try {
      await this.$axios.post('http://localhost:8000/api/password/otp', { email: this.email });
      this.successMessage = `Codice OTP inviato a ${this.email}`;
      setTimeout(() => {
        this.$router.push(`/reset-password?email=${encodeURIComponent(this.email)}`);
      }, 2000);
    } catch (error) {
      this.successMessage = '';
      const errorMessage = error.response?.data?.message || 'Errore nell\'invio del codice OTP'
      this.showSnackbar(errorMessage);
    } finally {
      this.loading = false;
    }
  }
    }
  }
  </script>
  
  <style scoped>
  .animate__animated {
    animation-duration: 0.5s;
  }
  
  .transition-swing {
    transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  }
  
  .v-card {
    overflow: hidden;
  }
  
  .v-card__title {
    background: linear-gradient(45deg, #fdf3e4 0%, #fff2b8 100%);
  }
  
  .v-text-field--outlined >>> fieldset {
    border-color: rgba(52, 73, 94, 0.3) !important;
  }
  
  .v-btn--disabled {
    opacity: 0.7;
    transform: scale(0.98) !important;
  }
  
  @media (max-width: 600px) {
    .v-card__title {
      flex-direction: column;
      text-align: center;
    }
    
    .v-card__title h1 {
      font-size: 1.8rem !important;
    }
  }

.v-snackbar__wrapper {
  font-family: "Uto-Bold", sans-serif !important;
}

.v-snackbar__content {
  align-items: center;
  padding: 14px 16px;
}
  </style>
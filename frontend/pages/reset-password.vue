<template>
    <v-container class="fill-height" fluid style="background-color: #fdf3e4;">
      <v-row align="center" justify="center">
        <v-col cols="12" sm="8" md="6" lg="4">
          <v-card class="elevation-12 rounded-lg" style="border-left: 5px solid #34495e;">
            <v-card-title class="primary--text d-flex justify-center align-center flex-column py-6 mb-4">
              <v-img
                src="/img/logoBIG.svg"
                alt="Do!t Logo"
                max-width="120"
                class="mb-4 transition-swing"
                style="filter: brightness(0) invert(0.3);"
              ></v-img>
              <h3 class="font-weight-bold ml-3 text-center">Resetta la tua Password</h3>
            </v-card-title>
  
            <v-card-text class="px-8 pb-8">
              <v-alert
                v-if="errorMessage"
                type="error"
                dense
                class="mb-6 animate__animated animate__headShake"
              >
                <v-icon left>mdi-alert-circle</v-icon>
                {{ errorMessage }}
              </v-alert>
  
              <v-form @submit.prevent="resetPassword" ref="form">
                <v-text-field
                  v-model="email"
                  label="Email"
                  type="email"
                  prepend-inner-icon="mdi-email-lock"
                  outlined
                  rounded
                  disabled
                  class="mb-4"
                  style="border-radius: 28px;"
                ></v-text-field>
  
                <v-text-field
                  v-model="otp"
                  label="Codice OTP"
                  type="text"
                  prepend-inner-icon="mdi-shield-key"
                  outlined
                  rounded
                  counter="6"
                  :rules="[v => !!v || 'Campo obbligatorio', v => v.length === 6 || '6 caratteri richiesti']"
                  class="mb-4"
                  style="border-radius: 28px;"
                ></v-text-field>
  
                <v-text-field
                  v-model="password"
                  label="Nuova Password"
                  type="password"
                  prepend-inner-icon="mdi-form-textbox-password"
                  outlined
                  rounded
                  :rules="passwordRules"
                  class="mb-4"
                  style="border-radius: 28px;"
                ></v-text-field>
  
                <v-text-field
                  v-model="passwordConfirmation"
                  label="Conferma Password"
                  type="password"
                  prepend-inner-icon="mdi-form-textbox-password"
                  outlined
                  rounded
                  :rules="[v => !!v || 'Campo obbligatorio', v => v === password || 'Le password devono coincidere']"
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
                  <v-icon left>mdi-lock-reset</v-icon>
                  Imposta Nuova Password
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
    </v-container>
  </template>
  
  <script>
  export default {
    data: () => ({
      email: '',
      otp: '',
      password: '',
      passwordConfirmation: '',
      loading: false,
      hover: false,
      errorMessage: '',
      passwordRules: [
        v => !!v || 'Password obbligatoria',
        v => v.length >= 8 || 'Minimo 8 caratteri',
      ]
    }),
    mounted() {
      this.email = this.$route.query.email || ''
    },
    methods: {
      async resetPassword() {
        this.errorMessage = ''
        if (!this.$refs.form.validate()) return
        
        this.loading = true
        try {
          await this.$axios.post('http://localhost:8000/api/password/reset-with-otp', {
            email: this.email,
            otp: this.otp,
            password: this.password,
            password_confirmation: this.passwordConfirmation
          })
          
          this.$router.push({
            path: '/',
            query: { passwordReset: 'true' }
          })
        } catch (error) {
          this.errorMessage = error.response?.data?.message || 'Errore nel reset della password'
        } finally {
          this.loading = false
        }
      }
    }
  }
  </script>
  
  <style scoped>
  .animate__animated {
    animation-duration: 0.5s;
  }
  
  .v-text-field--outlined >>> fieldset {
    border-color: rgba(52, 73, 94, 0.3) !important;
  }
  
  .v-btn--disabled {
    opacity: 0.7;
    transform: scale(0.98) !important;
  }
  
  @media (max-width: 600px) {
    .v-card__title h3 {
      font-size: 1.3rem !important;
    }
    
    .v-btn {
      font-size: 0.9rem !important;
    }
  }
  </style>
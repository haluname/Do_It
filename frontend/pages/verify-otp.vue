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
              <h3 class="font-weight-bold ml-3 text-center">Verifica Email</h3>
            </v-card-title>
  
            <v-card-text class="px-8 pb-8">
              <v-alert
                v-if="errorMessage"
                type="error"
                dense
                class="mb-6"
              >
                {{ errorMessage }}
              </v-alert>
  
              <v-form @submit.prevent="verifyOtp" ref="form">
                <v-text-field
                  v-model="otp"
                  label="Codice OTP"
                  prepend-inner-icon="mdi-shield-key"
                  outlined
                  rounded
                  :rules="otpRules"
                  class="mb-4"
                  style="border-radius: 28px;"
                ></v-text-field>
  
                <v-btn
                  type="submit"
                  color="primary"
                  x-large
                  block
                  :loading="loading"
                  class="font-weight-bold rounded-pill elevation-4"
                >
                  <v-icon left>mdi-check-decagram</v-icon>
                  Verifica Account
                </v-btn>
              </v-form>
  
              <div class="text-center mt-6">
                <v-btn
                  text
                  color="grey darken-2"
                  @click="resendOtp"
                  :disabled="resendDisabled"
                  class="text-caption font-weight-medium"
                >
                  <v-icon left small>mdi-email-sync</v-icon>
                  {{ resendButtonText }}
                </v-btn>
              </div>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </template>
  
  <script>
  export default {
    data: () => ({
      otp: '',
      loading: false,
      errorMessage: '',
      resendDisabled: false,
      resendCountdown: 60,
      otpRules: [
        v => !!v || 'Codice OTP obbligatorio',
        v => v.length === 6 || 'Il codice OTP deve essere di 6 caratteri'
      ]
    }),
    
    computed: {
      resendButtonText() {
        return this.resendDisabled ? `Riprova tra ${this.resendCountdown}s` : 'Invia nuovo codice'
      }
    },
  
    methods: {
      async verifyOtp() {
        if (!this.$refs.form.validate()) return
        
        this.loading = true
        this.errorMessage = ''
        
        try {
          await this.$axios.post('http://localhost:8000/api/verify-otp', {
            email: this.$route.query.email,
            otp: this.otp
          })
          
          await this.$auth.loginWith('laravelSanctum', {
            data: {
              email: this.$route.query.email,
              password: this.$route.query.password
            }
          })
          
          sessionStorage.removeItem("isPostBack");
          this.$router.push('/home')
        } catch (error) {
          this.errorMessage = error.response?.data?.message || 'Verifica fallita'
        } finally {
          this.loading = false
        }
      },
  
      async resendOtp() {
        if (this.resendDisabled) return
        
        try {
          await this.$axios.post('http://localhost:8000/api/resend-otp', {
            email: this.$route.query.email
          })
          this.startResendCountdown()
        } catch (error) {
          this.errorMessage = error.response?.data?.message || 'Errore nell\'invio del codice'
        }
      },
  
      startResendCountdown() {
        this.resendDisabled = true
        const interval = setInterval(() => {
          this.resendCountdown--
          if (this.resendCountdown <= 0) {
            clearInterval(interval)
            this.resendDisabled = false
            this.resendCountdown = 60
          }
        }, 1000)
      }
    },
  
    created() {
      if (!this.$route.query.email || !this.$route.query.password) {
        this.$router.push('/')
      }
    }
  }
  </script>
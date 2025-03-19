<template>
  <v-container fluid class="login-container">

    <v-row class="fill-height">
      <!-- Sezione sinistra (solo desktop) -->
      <v-col cols="12" md="4" class="left-section d-none d-md-flex align-center justify-center">
        <v-img
          src="/img/logoBIG.svg"
          alt="do_it Logo"
          max-width="300"
          contain
          class="desktop-logo"
        ></v-img>
      </v-col>

      <!-- Sezione destra -->
      <v-col cols="12" md="8" class="right-section d-flex align-center justify-center">
        <v-card class="login-card" elevation="10" shaped>
          <!-- Logo mobile -->
          <div class="text-center mb-6 d-flex d-md-none">
            <v-img
              src="/img/logoBIG.svg"
              alt="Do!t Logo"
              max-width="120"
              class="mobile-logo"
            ></v-img>
          </div>

          <v-form @submit.prevent="login" ref="form" lazy-validation>
            <v-card-text>
              <h1 class="text-h4 font-weight-bold mb-6 primary--text">Welcome Back!</h1>

              <v-text-field
                v-model="email"
                label="Email"
                prepend-inner-icon="mdi-email"
                outlined
                rounded
                :rules="emailRules"
                required
              ></v-text-field>

              <v-text-field
                v-model="password"
                label="Password"
                prepend-inner-icon="mdi-lock"
                outlined
                rounded
                color="primary"
                :append-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'"
                :type="showPassword ? 'text' : 'password'"
                @click:append="showPassword = !showPassword"
                :rules="passwordRules"
                required
              ></v-text-field>

              <v-btn 
                block 
                x-large 
                color="primary" 
                class="mt-6"
                type="submit"
                :loading="loading"
                depressed
                rounded
              >
                Login 
              
              </v-btn>

              <!-- <div class="text-center mt-4">
                <v-btn text small color="grey darken-1" @click="forgotPassword">
                  Password Dimenticata?
                </v-btn>
              </div> -->
            </v-card-text>

            <v-divider class="mx-4"></v-divider>

            <div class="text-center py-4">
              <span class="grey--text">Non sei registrato? </span>
              <v-btn text color="primary" @click="signUp">REGISTRATI</v-btn>
            </div>
          </v-form>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<script>
export default {
  data: () => ({
    email: '',
    password: '',
    showPassword: false,
    loading: false,
    emailRules: [
      v => !!v || 'Email obbligatoria',
      v => /.+@.+\..+/.test(v) || 'l\'Email deve essere valida',
    ],
    passwordRules: [
      v => !!v || 'Password obbligatoria',
      v => v.length >= 8 || 'Min 8 caratteri',
    ],

  }),

  methods: {
    async login() {
      if (this.$refs.form.validate()) {
        this.loading = true
        try {
          await new Promise(resolve => setTimeout(resolve, 1500))
          this.$router.push('/dashboard')
        } catch (error) {
          console.error('Login error:', error)
        } finally {
          this.loading = false
        }
      }
    },
    forgotPassword() {
      console.log('Forgot password clicked')
    },
    signUp() {
      this.$router.push('/register')
    }
  },

  mounted() {
    
  }
}
</script>

<style scoped>

/*
COLORS: 

#34495e BLU NAVY
#222222 NERO
#ffd166 GIALLO
#fdf3e4 BEIGE
#444444 GRIGIO
#e8f7f7 AZZURRO PALLIDO
#fffacd  GIALLO PASTELLO
#fff2b8 GIALLO LEGGERMENTE PIU SCURO DEL PASTELLO
#ffe599 GIALLO ANCORA PIU SCURETTO
#ffd966 GIALLO SCURO

*/
.login-container {
  height: 100vh;
  background-color: #ffe599;
}

.left-section {
  background-color: #34495e;
  min-height: 100vh;
}

.right-section {
  background-color: #ffe599;
  padding: 2rem;
}

.desktop-logo {
  filter: brightness(0) invert(1);
}

.mobile-logo {
  filter: brightness(0) invert(0.3);
}

.login-card {
  width: 100%;
  max-width: 500px;
  padding: 2rem;
  border-radius: 20px !important;
  transition: all 0.3s ease;
  height: 500px;
}

.social-login {
  justify-content: center;
  padding: 1.5rem 0;
}

.theme-toggle {
  transition: all 0.3s ease;
  z-index: 999;
}

@media (max-width: 960px) {
  .login-container {
    background-color: #fdf3e4;
  }
  
  .right-section {
    background-color: transparent;
    padding: 1rem;
  }
  
  .login-card {
    box-shadow: none !important;
    background-color: transparent !important;
  }
  
  .v-text-field >>> fieldset {
    background-color: rgba(255, 255, 255, 0.8) !important;
  }
}
</style>
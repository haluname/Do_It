<template>
  <v-container fluid class="login-container">
    <!-- Pulsante Toggle Tema -->
    <v-btn 
      fab
      small
      dark
      fixed
      top
      right
      class="ma-4 theme-toggle"
      @click="toggleTheme"
      :color="isDark ? 'grey darken-3' : 'yellow darken-2'"
    >
      <v-icon v-if="isDark">mdi-weather-sunny</v-icon>
      <v-icon v-else>mdi-weather-night</v-icon>
    </v-btn>

    <v-row justify="center" align="center" class="fill-height">
      <v-col cols="12" md="6" lg="4">
        <v-card class="login-card" elevation="10" shaped>
          <div class="login-header">
            <v-img
              src="/img/logoBIG.svg"
              alt="Logo"
              max-width="120"
              class="logo"
            ></v-img>
            <h1 class="text-h4 font-weight-bold primary--text">Welcome Back!</h1>
          </div>

          <v-form @submit.prevent="login" ref="form" lazy-validation>
            <v-card-text>
              <v-text-field
                v-model="email"
                label="Email"
                prepend-inner-icon="mdi-email"
                outlined
                rounded
                color="primary"
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
                <template v-slot:loader>
                  <v-progress-circular indeterminate size="24"></v-progress-circular>
                </template>
              </v-btn>

              <div class="text-center mt-4">
                <v-btn text small color="grey darken-1" @click="forgotPassword">
                  Forgot Password?
                </v-btn>
              </div>
            </v-card-text>
          </v-form>

          <v-divider class="mx-4"></v-divider>

 

          <div class="text-center py-4">
            <span class="grey--text">Don't have an account? </span>
            <v-btn text color="primary" @click="signUp">Sign Up</v-btn>
          </div>
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
    isDark: false,
    emailRules: [
      v => !!v || 'Email is required',
      v => /.+@.+\..+/.test(v) || 'Email must be valid',
    ],
    passwordRules: [
      v => !!v || 'Password is required',
      v => v.length >= 8 || 'Min 8 characters',
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
    },
    socialLogin(provider) {
      console.log('Social login:', provider)
    },
    toggleTheme() {
      this.isDark = !this.isDark
      this.$vuetify.theme.dark = this.isDark
      localStorage.setItem('darkTheme', this.isDark)
    }
  },

  mounted() {
    const savedTheme = localStorage.getItem('darkTheme')
    if (savedTheme !== null) {
      this.isDark = savedTheme === 'true'
      this.$vuetify.theme.dark = this.isDark
    }
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
  background-color: var(--v-primary-base);
  height: 100vh;
  transition: background-color 0.3s ease;
}

.login-card {
  padding: 2rem;
  border-radius: 20px !important;
  transform: translateY(0);
  transition: all 0.3s ease;
}

.login-card:hover {
  transform: translateY(-5px);
}

.login-header {
  text-align: center;
  margin-bottom: 2rem;
}

.logo {
  margin: 0 auto 1rem;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,0.1));
}

.social-login {
  justify-content: center;
  padding: 1.5rem 0;
}

.v-btn--fab {
  transition: transform 0.3s ease;
}

.v-btn--fab:hover {
  transform: scale(1.1);
}

.v-text-field >>> fieldset {
  border-radius: 50px !important;
}

.v-input__prepend-inner {
  margin-right: 12px !important;
}

.theme-toggle {
  transition: all 0.3s ease;
  z-index: 999;
}
</style>
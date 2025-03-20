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
        <Login v-if="isRegistered" @toggleAuth="toggle" />
        <Register v-if="!isRegistered" @toggleAuth="toggle" />
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
    isRegistered : true,
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
    },
    toggle() {
      this.isRegistered = !this.isRegistered;
    },
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
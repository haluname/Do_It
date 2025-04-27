<template>
  <v-card class="login-card" elevation="10" shaped>
    <div class="text-center mb-6 d-flex d-md-none">
      <v-img src="/img/logoBIG.svg" alt="Do!t Logo" max-width="120" class="mobile-logo"></v-img>
    </div>

    <v-form @submit.prevent="login" ref="form" lazy-validation>
      <v-card-text>
        <h1 class="text-h4 font-weight-bold mb-6 primary--text text-center title">Welcome Back!</h1>

        <v-slide-y-transition>
          <v-alert v-if="errorMessage" dense text class="mb-4 text-center custom-error">
            <span class="caption">{{ errorMessage }}</span>
          </v-alert>
        </v-slide-y-transition>

        <v-text-field v-model="email" label="Email" prepend-inner-icon="mdi-email" outlined rounded :rules="emailRules"
          required></v-text-field>

        <v-text-field v-model="password" label="Password" prepend-inner-icon="mdi-lock" outlined rounded color="primary"
          :append-icon="showPassword ? 'mdi-eye' : 'mdi-eye-off'" :type="showPassword ? 'text' : 'password'"
          @click:append="showPassword = !showPassword" :rules="passwordRules" required></v-text-field>

        <v-btn block x-large color="primary" class="mt-6 white--text" type="submit" :loading="loading" depressed
          rounded>
          Login
        </v-btn>
      </v-card-text>

      <v-divider class="mx-4"></v-divider>

      <div class="text-center py-4">
        <span class="grey--text">Non sei registrato? </span>
        <v-btn text color="primary" @click="$emit('toggleAuth')">REGISTRATI</v-btn>
      </div>
      <div class="text-center py-2">
  <v-btn text color="primary" @click="$router.push('/forgot-password')">Password dimenticata?</v-btn>
</div>  
    </v-form>
  </v-card>
     
</template>

<script>
export default {
  data: () => ({
    email: '',
    password: '',
    showPassword: false,
    loading: false,
    errorMessage: '',
    emailRules: [
      v => !!v || 'Email obbligatoria',
      v => /.+@.+\..+/.test(v) || "L'Email deve essere valida",
    ],
    passwordRules: [
      v => !!v || 'Password obbligatoria',
      v => v.length >= 8 || 'Min 8 caratteri',
    ],
  }),

  methods: {
    async login() {
      this.errorMessage = '';
      this.loading = true;

      const isValid = this.$refs.form.validate();
      if (!isValid) {
        this.loading = false;
        return;
      }

      try {
        await this.$auth.loginWith('laravelSanctum', {
          data: {
            email: this.email,
            password: this.password,
          },
        });
        this.$router.push('/home');
      } catch (error) {
        this.errorMessage = 'CREDENZIALI ERRATE';
        console.error('Login failed:', error);
      } finally {
        this.loading = false;
      }
    },
  },
};
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


.custom-error {
  background-color: #ffebee !important;
  /* Sfondo rosso chiaro */
  color: #c62828 !important;
  /* Testo rosso scuro */
  font-size: 0.8em;
  font-weight: bold;
  padding: 8px 16px;
  border-radius: 9px;
  font-family: 'Uto-Bold', sans-serif !important;

}

.custom-error .v-alert__wrapper {
  margin: 0;
  padding: 0;
}

.title,
.caption {
  font-family: 'Uto-Bold', sans-serif !important;
}

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
  height: fit-content !important;
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

  .v-text-field>>>fieldset {
    background-color: rgba(255, 255, 255, 0.8) !important;
  }
}
</style>
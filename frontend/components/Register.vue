<template>
  <v-card class="login-card" elevation="10" shaped>
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3000" top>
  {{ snackbar.message }}
</v-snackbar>
       <!-- Logo mobile -->
       <div class="text-center mb-6 d-flex d-md-none">
         <v-img
           src="/img/logoBIG.svg"
           alt="Do!t Logo"
           max-width="120"
           class="mobile-logo"
         ></v-img>
       </div>

       <v-form @submit.prevent="register" ref="form" lazy-validation>
         <v-card-text>
          <h1 class="text-h4 font-weight-bold mb-6 primary--text text-center title">Welcome!</h1>

           <v-text-field
             v-model="username"
             label="Username"
             prepend-inner-icon="mdi-account"
             outlined
             rounded
             :rules="usernameRules"
             required
           ></v-text-field>

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

           <v-text-field
             v-model="confirmPassword"
             label="Conferma Password"
             prepend-inner-icon="mdi-lock-check"
             outlined
             rounded
             color="primary"
             :append-icon="showPassword2 ? 'mdi-eye' : 'mdi-eye-off'"
             :type="showPassword2 ? 'text' : 'password'"
             @click:append="showPassword2 = !showPassword2"
             :rules="confirmPasswordRules"
             required
           ></v-text-field>

           <v-select
             v-model="gender"
             label="Genere"
             :items="['Male', 'Female']"
             prepend-inner-icon="mdi-gender-male-female"
             outlined
             rounded
             required
             :rules="genderRules"

           ></v-select>

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
             REGISTRATI 
           </v-btn>
         </v-card-text>

         <v-divider class="mx-4"></v-divider>

         <div class="text-center py-4">
           <span class="grey--text">Già registrato? </span>
           <v-btn text color="primary" @click="$emit('toggleAuth')">ACCEDI</v-btn>
         </div>
       </v-form>
     
     </v-card>
</template>

<script>
export default {
data: () => ({
 email: '',
 password: '',
 confirmPassword: '',
 username: '',
 gender: '',
 showPassword: false,
 showPassword2: false,
 loading: false,
 snackbar: {
      show: false,
      message: '',
      color: 'error' 
    },
 emailRules: [
   v => !!v || 'Email obbligatoria',
   v => /.+@.+\..+/.test(v) || 'l\'Email deve essere valida',
 ],
  genderRules: [
    v => !!v || 'Genere obbligatorio',
  ],
 passwordRules: [
   v => !!v || 'Password obbligatoria',
   v => v.length >= 8 || 'Min 8 caratteri',
 ],
 usernameRules: [
   v => !!v || 'Username obbligatorio',
   v => v.length >= 4 || 'Min 4 caratteri',
   v => v.length <= 16 || 'Max 16 caratteri',

 ],
}),

computed: {
 confirmPasswordRules() {
   return [
     v => !!v || 'Conferma password obbligatoria',
     v => v === this.password || 'Le password non coincidono',
   ]
 }
},

methods: {
  async register() {
  if (this.$refs.form.validate()) {
    this.loading = true;
    try {
      const response = await this.$axios.post("http://localhost:8000/api/register", {
        name: this.username,
        email: this.email,
        password: this.password,
        gender: this.gender,
      });

      // Mostra snackbar di successo
      this.showSnackbar("Registrazione avvenuta con successo!", "success");

      // Attendi 2 secondi prima di effettuare il login e il redirect
      setTimeout(async () => {
        await this.$auth.loginWith('laravelSanctum', {
          data: { 
            email: this.email,
            password: this.password,
          }
        });
        this.$router.push('/dashboard');
      }, 2000);
      
    } catch (error) {
      if (error.response?.status === 422) {
        const errors = error.response.data.errors;
        if (errors.email) {
          this.showSnackbar("Email già in uso", "error");
        }
        if (errors.name) {
          this.showSnackbar("Nome utente già preso", "error");
        }
      } else {
        this.showSnackbar(
            error.response?.data?.message || "Errore durante la registrazione",
            "error"
        );
      }
    } finally {
      this.loading = false;
    }
  }
}

,

showSnackbar(message, color = "error") {
    this.snackbar.message = message;
    this.snackbar.color = color;
    this.snackbar.show = true;
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
.login-container {
  height: 100vh;
  background-color: #efca5b;
}


.left-section {
 background-color: #34495e;
 min-height: 100vh;
}

.right-section {
 background-color: #ffe599;
 padding: 2rem;
}

.title{
  font-family: 'Uto-Bold', sans-serif !important;
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
 
  max-height: 100vh; 
  overflow-y: auto; 
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

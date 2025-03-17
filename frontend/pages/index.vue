<template>
  <v-container fluid class="login-container">
    <v-row justify="center" align="center" class="fill-height">
      <v-col cols="12" md="6" lg="4">
        <v-card class="login-card" elevation="10" shaped>
          <div class="login-header">
            <v-img
              src="/img/fullLogo.svg"
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

          <v-card-actions class="social-login">
            <v-btn 
              v-for="(social, i) in socialLogins" 
              :key="i"
              fab 
              dark 
              :color="social.color"
              class="mx-2"
              @click="socialLogin(social.provider)"
            >
              <v-icon>{{ social.icon }}</v-icon>
            </v-btn>
          </v-card-actions>

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
    emailRules: [
      v => !!v || 'Email is required',
      v => /.+@.+\..+/.test(v) || 'Email must be valid',
    ],
    passwordRules: [
      v => !!v || 'Password is required',
      v => v.length >= 8 || 'Min 8 characters',
    ],
    socialLogins: [
      { provider: 'google', icon: 'mdi-google', color: '#DB4437' },
      { provider: 'facebook', icon: 'mdi-facebook', color: '#1877F2' },
      { provider: 'github', icon: 'mdi-github', color: '#333' },
    ]
  }),

  methods: {
    async login() {
      if (this.$refs.form.validate()) {
        this.loading = true
        try {
          // Simulate API call
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
    }
  }
}
</script>

<style scoped>
.login-container {
  background: linear-gradient(135deg, #FE8267 0%, #FEE267 100%);
  height: 100vh;
}

.login-card {
  padding: 2rem;
  border-radius: 20px !important;
  transform: translateY(0);
  transition: transform 0.3s ease;
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
</style>
<template>
    <v-container>
      <v-row>
        <v-col cols="12" md="8" lg="6">
          <v-card>
            <v-card-title>
              <h2>Benvenuto, {{ user.name }}</h2>
            </v-card-title>
            <v-card-text>
              <p>Email: {{ user.email }}</p>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>
  </template>
  
  <script>
  export default {
    middleware: 'auth',
    computed: {
      user() {
        return this.$store.state.user
      }
    },
    data() {
      return {
        message: ''
      }
    },
    async mounted() {
      
    }
  }
  </script>
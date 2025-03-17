<template>
  <div class="page-container">
  <v-row justify="center" align="center">
    <v-col cols="12" sm="8" md="6">
      <v-card>
        <v-card-title class="headline">
          Scrivi il tuo nomeadada
        </v-card-title>

        <v-card-text>
          <v-text-field filled label="Nome" v-model="name"></v-text-field>
          <v-divider class="mb-4"></v-divider>
          <template v-if="name">
            <h1>CIAO {{ nameUppercase }}!</h1>
            <v-btn class="mt-4" color="primary" @click="reset">Reset</v-btn>
          </template>
        </v-card-text>
      </v-card>
    </v-col>
    <v-btn @click="greet">Greet</v-btn>
    <v-btn @click="greet">Greet</v-btn>
    <v-img src="/img/fullLogo.svg" alt="Logo" max-width="300px"></v-img>


  </v-row>
</div>

</template>

<script>
export default {
  
  data: () => ({
    name: ''
  }),
  computed: {
    nameUppercase() {
      return this.name.toUpperCase()
    }
  },
  methods: {
    reset() {
      this.name = ''
    },
    async greet() {
      try {
        const response = await this.$axios.get('https://jsonplaceholder.typicode.com/todos/1')
        console.log(response.data)
      } catch (error) {
        console.error('Errore nella richiesta:', error)
      }
    }
  }
}
</script>


<style scoped>

.page-container {
  background-color: #FEE267 
  ; /* Sostituisci con il colore desiderato */
  min-height: 100%;
  overflow-y: hidden;
  width: 100vw;
  margin: 0;
  padding: 20px; /* Regola il padding secondo necessità */
}



</style>
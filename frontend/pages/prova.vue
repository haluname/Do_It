<template>
  <v-container>
    <v-card class="pa-4" :style="{ backgroundColor: themeColor }">
      <h2>Benvenuto, {{ userName }}</h2>

      <!-- Cambia Nome -->
      <v-text-field v-model="newName" label="Cambia nome"></v-text-field>
      <v-btn color="primary" @click="updateUserName">Salva Nome</v-btn>

      <!-- Cambia Colore -->
      <v-color-picker v-model="newColor"></v-color-picker>
      <v-btn color="success" @click="updateThemeColor">Cambia Colore</v-btn>
    </v-card>
  </v-container>
</template>

<script>
export default {
  data() {
    return {
      newName: '',
      newColor: '#ffffff'
    };
  },
  computed: {
    userName() {
      return this.$store.state.userName;
    },
    themeColor() {
      return this.$store.state.themeColor;
    }
  },
  methods: {
    updateUserName() {
      this.$store.commit('SET_USER_NAME', this.newName);
    },
    updateThemeColor() {
      this.$store.commit('SET_THEME_COLOR', this.newColor);
    }
  }
};
</script>

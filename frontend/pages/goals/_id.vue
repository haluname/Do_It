<template>
  <v-app>
    <v-main style="background-color: #fdf3e4;">
      <v-container class="py-8">
        <!-- Loader -->
        <v-row v-if="loading" class="d-flex justify-center">
          <v-progress-circular indeterminate color="primary" />
        </v-row>

        <v-card v-else class="mx-auto" max-width="800" elevation="6">
          <v-card-title class="headline d-flex justify-space-between align-center">
            <div>
              <v-text-field
                v-if="isEditing"
                v-model="editedGoal.title"
                outlined
                dense
                :rules="[v => !!v || 'Il titolo è obbligatorio']"
              ></v-text-field>
              <template v-else>
                {{ goal.title }}
              </template>
              <v-chip :color="priorityColor(goal.priority)" class="ml-3" dark>
                {{ priorityLabel(goal.priority) }}
              </v-chip>
            </div>
            <v-btn icon @click="toggleEditMode">
              <v-icon v-if="!isEditing">mdi-pencil</v-icon>
              <v-icon v-else color="error">mdi-close</v-icon>
            </v-btn>
          </v-card-title>

          <v-card-subtitle>
            <v-menu v-if="isEditing" v-model="dateMenu" :close-on-content-click="false">
              <template v-slot:activator="{ on, attrs }">
                <v-btn text v-bind="attrs" v-on="on">
                  <v-icon left>mdi-calendar-edit</v-icon>
                  {{ formatDate(editedGoal.exp) }}
                </v-btn>
              </template>
              <v-date-picker
                v-model="editedGoal.exp"
                :min="new Date().toISOString().substr(0, 10)"
              ></v-date-picker>
            </v-menu>
            <template v-else>
              <v-icon left>mdi-calendar</v-icon>
              Scadenza: {{ formatDate(goal.exp) }}
            </template>
          </v-card-subtitle>

          <v-card-text>
            <v-divider class="my-4"></v-divider>

            <v-textarea
              v-if="isEditing"
              v-model="editedGoal.description"
              outlined
              rows="3"
              label="Descrizione"
              :rules="[v => !!v || 'La descrizione è obbligatoria']"
            ></v-textarea>
            <div v-else class="description-text">
              {{ goal.description }}
            </div>

            <v-divider class="my-4"></v-divider>

            <v-select
              v-if="isEditing"
              v-model="editedGoal.priority"
              :items="priorityOptions"
              label="Priorità"
              item-text="text"
              item-value="value"
              outlined
            ></v-select>
          </v-card-text>

          <v-card-actions class="d-flex justify-space-between">
            <v-btn color="primary" @click="$router.go(-1)">
              <v-icon left>mdi-arrow-left</v-icon>
              Torna indietro
            </v-btn>
            
            <v-btn
              v-if="isEditing"
              color="success"
              @click="saveChanges"
              :loading="saving"
            >
              <v-icon left>mdi-content-save</v-icon>
              Salva modifiche
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-container>
    </v-main>

    <!-- Snackbar -->
    <v-snackbar v-model="snackbar" :color="snackbarColor" timeout="3000">
      {{ snackbarText }}
      <v-btn text @click="snackbar = false">
        <v-icon>mdi-close</v-icon>
      </v-btn>
    </v-snackbar>
  </v-app>
</template>

<script>
export default {
  middleware: 'auth',
  data() {
    return {
      goal: null,
      editedGoal: null,
      loading: true,
      isEditing: false,
      saving: false,
      dateMenu: false,
      snackbar: false,
      snackbarText: '',
      snackbarColor: 'success',
      priorityOptions: [
        { text: 'Alta', value: 2 },
        { text: 'Media', value: 1 },
        { text: 'Bassa', value: 0 }
      ]
    }
  },
  async fetch() {
    try {
      const response = await this.$axios.get(
        `http://localhost:8000/api/goals/${this.$route.params.id}`
      )
      this.goal = response.data
      this.editedGoal = { ...response.data }
    } catch (error) {
      console.error('Error fetching goal:', error)
      this.$nuxt.error({ statusCode: 404, message: 'Goal non trovato' })
    } finally {
      this.loading = false
    }
  },
  methods: {
    toggleEditMode() {
      this.isEditing = !this.isEditing
      if (!this.isEditing) {
        this.editedGoal = { ...this.goal }
      }
    },

    async saveChanges() {
      if (!this.editedGoal.title || !this.editedGoal.description) {
        this.showSnackbar('Compila tutti i campi obbligatori', 'error')
        return
      }

      this.saving = true
      try {
        const response = await this.$axios.put(
          `http://localhost:8000/api/goals/${this.$route.params.id}`,
          this.editedGoal
        )
        
        this.goal = response.data
        this.showSnackbar('Modifiche salvate con successo!', 'success')
        this.isEditing = false
      } catch (error) {
        console.error('Error updating goal:', error)
        this.showSnackbar("Errore durante il salvataggio", 'error')
      } finally {
        this.saving = false
      }
    },

    showSnackbar(text, color) {
      this.snackbarText = text
      this.snackbarColor = color
      this.snackbar = true
    },

    formatDate(date) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' }
      return new Date(date).toLocaleDateString('it-IT', options)
    },
    
    priorityLabel(priority) {
      const labels = { 2: 'Alta', 1: 'Media', 0: 'Bassa' }
      return labels[priority] || 'Sconosciuta'
    },
    
    priorityColor(priority) {
      return { 2: 'red', 1: 'orange', 0: 'green' }[priority] || 'gray'
    }
  }
}
</script>
<style scoped>
.headline {
  font-family: "Uto-Bold", sans-serif !important;
  font-size: 1.8rem !important;
}

.description-text {
  font-size: 1.1rem;
  line-height: 1.6;
  white-space: pre-wrap;
}

.v-chip {
  font-family: "Uto-Bold", sans-serif !important;
}

.v-text-field, .v-textarea {
  font-size: 1.1rem !important;
}

.v-select {
  max-width: 200px;
}

</style>
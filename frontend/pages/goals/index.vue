<template>
  <v-app>
    <NavBar />
    <v-main style="background-color: #fdf3e4;">
      <v-container class="py-8">

        <!-- Loader -->
        <v-row v-if="loading" class="d-flex justify-center">
          <v-progress-circular indeterminate color="primary" />
        </v-row>

        <!-- Quando ha finito di caricare -->
        <v-row v-else>
          <!-- Nessun goal -->
          <template v-if="goals.length === 0">
            <v-col cols="12" class="text-center">
              <v-alert type="info" color="amber lighten-4" border="left" elevation="2">
                Non ci sono goals.
              </v-alert>
            </v-col>
          </template>

          <!-- Lista goals -->
          <template v-else>
            <v-col v-for="goal in goals" :key="goal.id" cols="12" md="3">
              <NuxtLink :to="`/goals/${goal.id}`" style="text-decoration: none;">
                <v-card class="goal-card" :class="priorityClass(goal.priority)" elevation="6" hover>
                
                  <v-card-title class="goal-title">
                    {{ goal.title }}
                  </v-card-title>
                  <v-card-subtitle class="goal-priority">
                    <v-chip :color="priorityColor(goal.priority)" dark>
                      Priorità: {{ priorityLabel(goal.priority) }}
                    </v-chip>
                  </v-card-subtitle>
                  <v-card-text>
                    <p>{{ goal.description }}</p>
                    <p class="goal-expiry">
                      <v-icon color="red">mdi-calendar</v-icon>
                      Scadenza: {{ formatDate(goal.exp) }}
                    </p>
                  </v-card-text>
                  <v-card-actions class="justify-end pa-2">
                    <v-btn icon @click.prevent="openDeleteDialog(goal.id)">
                      <v-icon color="error">mdi-delete</v-icon>
                    </v-btn>
                  </v-card-actions>
                </v-card>
              </NuxtLink>
            </v-col>
          </template>
        </v-row>


        <v-dialog v-model="addDialog" max-width="600" persistent>
          <v-card class="rounded-lg">
            <v-card-title class="headline success white--text pa-4">
              <v-icon large left dark>mdi-plus-box</v-icon>
              Nuovo Goal
            </v-card-title>

            <v-card-text class="pa-4">
              <v-form ref="addForm" v-model="valid">
                <v-text-field
                  v-model="newGoal.title"
                  label="Titolo"
                  :rules="[v => !!v || 'Il titolo è obbligatorio']"
                  required
                  outlined
                  class="mb-4"
                ></v-text-field>

                <v-textarea
                  v-model="newGoal.description"
                  label="Descrizione"
                  :rules="[v => !!v || 'La descrizione è obbligatoria']"
                  outlined
                  rows="3"
                  class="mb-4"
                ></v-textarea>

                <v-select
                  v-model="newGoal.priority"
                  :items="priorityOptions"
                  label="Priorità"
                  item-text="text"
                  item-value="value"
                  outlined
                  class="mb-4"
                ></v-select>

                <v-menu
                  v-model="dateMenu"
                  :close-on-content-click="false"
                  transition="scale-transition"
                  offset-y
                  min-width="auto"
                >
                  <template v-slot:activator="{ on, attrs }">
                    <v-text-field
                      v-model="newGoal.exp"
                      label="Data scadenza"
                      prepend-icon="mdi-calendar"
                      readonly
                      outlined
                      v-bind="attrs"
                      v-on="on"
                    ></v-text-field>
                  </template>
                  <v-date-picker
                    v-model="newGoal.exp"
                    @input="dateMenu = false"
                    :min="new Date().toISOString().substr(0, 10)"
                    locale="it-IT"
                  ></v-date-picker>
                </v-menu>
              </v-form>
            </v-card-text>

            <v-divider></v-divider>

            <v-card-actions class="pa-4 d-flex justify-end grey lighten-4">
              <v-btn
                color="grey darken-2"
                text
                @click="closeAddDialog"
                class="mr-3"
                outlined
                large
              >
                <v-icon left>mdi-close</v-icon>
                Annulla
              </v-btn>

              <v-btn
                color="success darken-1"
                @click="saveGoal"
                :loading="saving"
                depressed
                large
                class="px-6 white--text"
              >
                <v-icon left>mdi-content-save</v-icon>
                Salva
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>



        <v-dialog v-model="deleteDialog" max-width="500" persistent>
          <v-card class="rounded-lg">
            <v-card-title class="headline error white--text pa-4">
              <v-icon large left dark>mdi-alert-octagram</v-icon>
              Conferma eliminazione
            </v-card-title>

            <v-card-text class="pa-4">
              <div class="text-center">
                <v-icon color="error" x-large class="mb-3">mdi-delete-alert</v-icon>
                <div class="text-h6 font-weight-bold mb-2">Attenzione!</div>
                <p class="body-1">
                  Stai per eliminare definitivamente questo goal.<br>
                  L'azione è <span class="error--text font-weight-bold">irreversibile</span>.
                </p>
              </div>
            </v-card-text>

            <v-divider></v-divider>

            <v-card-actions class="pa-4 d-flex justify-end" style="background-color: #f5f5f5;">
              <v-btn color="grey darken-2" text @click="deleteDialog = false" class="mr-3" outlined large>
                <v-icon left>mdi-close-circle</v-icon>
                Annulla
              </v-btn>

              <v-btn color="error darken-1" @click="confirmDelete" :loading="deleting" depressed large
                class="px-6 white--text">
                <v-icon left>mdi-delete-forever</v-icon>
                Elimina
              </v-btn>
            </v-card-actions>
          </v-card>
        </v-dialog>

        <!-- Snackbar per feedback -->
        <v-snackbar v-model="snackbar" :color="snackbarColor" timeout="3000" top shaped elevation="6">
          <div class="d-flex align-center">
            <v-icon left dark v-if="snackbarColor === 'success'">mdi-check-circle</v-icon>
            <v-icon left dark v-else>mdi-alert-circle</v-icon>
            {{ snackbarText }}
          </div>
          <template v-slot:action="{ attrs }">
            <v-btn text v-bind="attrs" @click="snackbar = false">
              <v-icon>mdi-close</v-icon>
            </v-btn>
          </template>
        </v-snackbar>

      </v-container>
      <v-btn
        fab
        dark
        color="primary"
        fixed
        bottom
        right
        class="mb-10 mr-10 elevation-12"
        @click="openAddDialog"
      >
        <v-icon>mdi-plus</v-icon>
      </v-btn>
    </v-main>
  </v-app>
</template>

<script>
export default {
  middleware: 'auth',
  data() {
    return {
      goals: [],
      loading: true,
      deleteDialog: false,
      selectedGoalId: null,
      deleting: false,
      snackbar: false,
      snackbarText: '',
      snackbarColor: 'success',
      addDialog: false,
      valid: false,
      saving: false,
      dateMenu: false,
      newGoal: {
        title: '',
        description: '',
        priority: 2, // Default media priorità
        exp: new Date().toISOString().substr(0, 10)
      },
      priorityOptions: [
        { text: 'Alta', value: 1 },
        { text: 'Media', value: 2 },
        { text: 'Bassa', value: 3 }
      ]
    };
  },
  async mounted() {
    await this.fetchGoals();
  },
  methods: {
    async fetchGoals() {
      try {
        const res = await this.$axios.get('http://localhost:8000/api/goals');
        this.goals = res.data;
      } catch (error) {
        console.error('Error fetching goals:', error);
        this.showSnackbar('Errore nel caricamento dei goals', 'error');
      } finally {
        this.loading = false;
      }
    },
    openDeleteDialog(goalId) {
      this.selectedGoalId = goalId;
      this.deleteDialog = true;
    },
    async confirmDelete() {
      this.deleting = true;
      try {
        await this.$axios.delete(`http://localhost:8000/api/goals/${this.selectedGoalId}`);
        this.goals = this.goals.filter(goal => goal.id !== this.selectedGoalId);
        this.showSnackbar('Goal eliminato con successo', 'success');
      } catch (error) {
        console.error('Error deleting goal:', error);
        this.showSnackbar('Errore durante l\'eliminazione del goal', 'error');
      } finally {
        this.deleting = false;
        this.deleteDialog = false;
        this.selectedGoalId = null;
      }
    },
    showSnackbar(text, color) {
      this.snackbarText = text;
      this.snackbarColor = color;
      this.snackbar = true;
    },
    formatDate(date) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(date).toLocaleDateString('it-IT', options);
    },
    priorityLabel(priority) {
      const labels = { 1: 'Alta', 2: 'Media', 3: 'Bassa' };
      return labels[priority] || 'Sconosciuta';
    },
    priorityColor(priority) {
      return { 1: 'red', 2: 'orange', 3: 'green' }[priority] || 'gray';
    },
    priorityClass(priority) {
      return { 1: 'high-priority', 2: 'medium-priority', 3: 'low-priority' }[priority] || '';
    },

    openAddDialog() {
      this.addDialog = true;
    },

    closeAddDialog() {
      this.addDialog = false;
      this.$refs.addForm.reset();
    },

    async saveGoal() {
      if (!this.$refs.addForm.validate()) return;

      this.saving = true;
      try {
        const response = await this.$axios.post('http://localhost:8000/api/goals', {
          title: this.newGoal.title,
          description: this.newGoal.description,
          priority: this.newGoal.priority,
          exp: this.newGoal.exp
        });

        this.goals.unshift(response.data);
        this.showSnackbar('Goal creato con successo', 'success');
        this.closeAddDialog();
      } catch (error) {
        console.error('Error creating goal:', error);
        this.showSnackbar("Errore durante il salvataggio del goal", 'error');
      } finally {
        this.saving = false;
      }
    },
  },
};
</script>

<style scoped>
* {
  font-family: "Uto-Bold", sans-serif !important;

}

/* Aggiungi questo stile per il pulsante */
.v-btn--fab.v-btn--fixed {
  z-index: 999;
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.v-btn--fab.v-btn--fixed:hover {
  transform: scale(1.1) rotate(90deg);
}

.v-btn--fab.v-btn--fixed:active {
  transform: scale(0.9);
}

.goal-card {
  position: relative;
}

.v-card {
  display: flex;
  flex-direction: column;
  min-height: 300px;
}

.v-card__text {
  flex: 1;
}

.v-card__actions {
  margin-top: auto;
  border-radius: 0 0 8px 8px;
}

/* Effetto hover per i pulsanti */
.v-btn:not(.v-btn--disabled):hover {
  transform: translateY(-2px);
  transition: transform 0.2s;
}

.goal-card {
  border-radius: 12px;
  padding: 16px;
  transition: transform 0.2s ease-in-out;
}

.goal-card:hover {
  transform: scale(1.05);
}

.goal-title {
  font-size: 1.2rem;
  font-weight: bold;
  overflow-wrap: break-word;
  word-break: keep-all;
  white-space: normal;
  line-height: 1.3;
}

.goal-priority {
  margin-bottom: 8px;
  overflow-wrap: break-word;

}

.goal-expiry {
  font-size: 0.9rem;
  font-weight: bold;
  color: #d32f2f;
}

.goal-card p {
  overflow-wrap: break-word;
  word-break: keep-all;
  white-space: normal;
  line-height: 1.5;
}

.high-priority {
  border-left: 5px solid red;
}

.medium-priority {
  border-left: 5px solid orange;
}

.low-priority {
  border-left: 5px solid green;
}

.v-card__title.error {
  background: linear-gradient(45deg, #ff5252, #d32f2f);
}

.v-card__actions .v-btn {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.v-btn--outlined {
  border-width: 2px;
}

.v-btn:not(.v-btn--disabled):hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.v-card__title.success {
  background: linear-gradient(45deg, #4CAF50, #388E3C);
}

/* Animazione bottone aggiunta */
.v-btn--fab {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
}

.v-btn--fab:hover {
  transform: rotate(90deg) scale(1.1);
}

.v-btn--fab:active {
  transform: rotate(90deg) scale(0.9);
}

</style>
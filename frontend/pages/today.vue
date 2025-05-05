<template>
  <v-app>
    <NavBar />
    <v-main style="background-color: #fdf3e4;">
      <v-card class="progress-card" elevation="4" style="border-radius: 12px;">
  <v-card-text class="d-flex align-center">
    <v-icon large color="success" class="mr-3">mdi-progress-check</v-icon>
    <div class="flex-grow-1">
      <div class="text-caption success--text font-weight-bold mb-1">PROGRESSO GIORNALIERO</div>
      <v-progress-linear
        :value="progressPercentage"
        color="success"
        height="24"
        
        rounded
        class="elevation-2"
        style="border-radius: 12px;"
      >
        <template v-slot:default="{ value }">
          <span class="white--text font-weight-bold" style="font-size: 0.8rem;">
            {{ Math.ceil(value) }}% COMPLETATO
          </span>
        </template>
      </v-progress-linear>
      <div class="text-right mt-1">
        <span class="text-caption grey--text">
          {{ completedGoalIds.length }}/{{ completedGoalIds.length + filteredGoals.length }} completati
        </span>
      </div>
    </div>
  </v-card-text>
</v-card>

      <v-container class="py-8 ">
        <v-row class="mb-6">
          <v-col cols="12" class="text-center">
            <h1 class="display-1 primary--text" style="font-weight: 500">Goals per Oggi</h1>
            <v-chip color="primary" text-color="white" class="mt-2">
              {{ formattedToday }}
            </v-chip>
          </v-col>
        </v-row>

        <v-row v-if="loading" class="d-flex justify-center">
          <v-progress-circular indeterminate color="primary" />
        </v-row>

        <div v-else class="carousel-container">
          <v-slide-group 
            v-if="filteredGoals.length > 0"
            class="pa-4"
            show-arrows
            center-active
          >
            <v-slide-item
              v-for="goal in filteredGoals"
              :key="goal.id"
              v-slot="{ toggle }"
            >
              <div class="carousel-item mx-2">
                <NuxtLink 
                  :to="`/goals/${goal.id}`" 
                  style="text-decoration: none;"
                  @click.native="toggle"
                >
                  <v-card 
                    class="goal-card" 
                    :class="priorityClass(goal.priority)"
                    elevation="6" 
                    width="300"
                    height="400"
                  >
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
                        <v-icon color="red">mdi-clock-alert</v-icon>
                        Scade oggi alle 23:59
                      </p>

                      <v-divider class="my-3"></v-divider>
                      <div class="task-section" v-if="goal.tasks && goal.tasks.length">
                        <v-list dense class="task-list">
                          <v-list-item 
                            v-for="task in goal.tasks.slice(0, 3)" 
                            :key="task.id"
                            class="px-0"
                          >
                            <v-list-item-icon class="mr-2">
                              <v-checkbox
                                @click.stop.prevent="deleteTask(task)"
                                dense
                                hide-details
                                color="error"
                              ></v-checkbox>
                            </v-list-item-icon>
                            <v-list-item-content>
                              <v-list-item-title 
                                style="font-size: 0.9rem; white-space: normal; word-break: break-word;"
                              >
                                {{ task.title }}
                              </v-list-item-title>
                            </v-list-item-content>
                          </v-list-item>
                        </v-list>

                        <div 
                          v-if="goal.tasks.length > 3" 
                          class="text-caption text-right grey--text mt-1"
                        >
                          + altri {{ goal.tasks.length - 3 }} task...
                        </div>
                      </div>

                      <v-alert
                        v-else
                        type="info"
                        color="amber lighten-4"
                        dense
                        class="mt-2"
                      >
                        Nessun task presente
                      </v-alert>
                    </v-card-text>
                    <v-card-actions class="justify-end pa-2">
                      <v-btn 
                        icon 
                        @click.stop.prevent="completeGoal(goal.id)"
                        color="success"
                      >
                        <v-icon>mdi-check-circle</v-icon>
                      </v-btn>
                      <v-btn icon @click.prevent="openDeleteDialog(goal.id)">
                        <v-icon color="error">mdi-delete</v-icon>
                      </v-btn>
                    </v-card-actions>
                  </v-card>
                </NuxtLink>
              </div>
            </v-slide-item>
          </v-slide-group>

          <v-col 
            v-else 
            cols="12" 
            class="text-center"
          >
            <v-alert 
              type="info" 
              color="amber lighten-4" 
              border="left" 
              elevation="2"
            >
              Nessun goal in scadenza oggi!
            </v-alert>
          </v-col>
        </div>

        <v-dialog v-model="addDialog" max-width="600" persistent>
          <v-card class="rounded-lg">
            <v-card-title class="headline success white--text pa-4">
              <v-icon large left dark>mdi-plus-box</v-icon>
              Nuovo Goal per Oggi
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

                <v-text-field
                  :value="todayDate"
                  label="Data scadenza"
                  prepend-icon="mdi-calendar"
                  readonly
                  outlined
                ></v-text-field>
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
      todayDate: new Date().toISOString().substr(0, 10),
      goals: [],
      completedGoalIds: [], 
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
      newGoal: {
        title: '',
        description: '',
        priority: 2,
        exp: new Date().toISOString().substr(0, 10)
      },
      priorityOptions: [
        { text: 'Alta', value: 2 },
        { text: 'Media', value: 1 },
        { text: 'Bassa', value: 0 }
      ]
    };
  },
  computed: {
    formattedToday() {
      return new Date().toLocaleDateString('it-IT', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric'
      });
    },
    progressPercentage() {
      const total = this.completedGoalIds.length + this.filteredGoals.length;
      return total > 0 ? Math.round((this.completedGoalIds.length / total) * 100) : 0;
    },
    filteredGoals() {
      return this.goals.filter(goal => 
        new Date(goal.exp).toDateString() === new Date().toDateString() &&
        !this.completedGoalIds.includes(goal.id)
      );
    }
  },
  async mounted() {
    await this.fetchGoals();
    this.loadCompletedGoals();
  },
  methods: {
    async fetchGoals() {
      try {
        const res = await this.$axios.get('http://localhost:8000/api/goals/today');
        this.goals = res.data;
      } catch (error) {
        console.error('Error fetching goals:', error);
        this.showSnackbar('Errore nel caricamento dei goals', 'error');
      } finally {
        this.loading = false;
      }
    },

    loadCompletedGoals() {
      const cookieData = this.$cookies.get('todayCompletedGoals');
      if (cookieData && Array.isArray(cookieData)) {
        this.completedGoalIds = cookieData;
      }
    },

    saveCompletedGoals() {
      const expiryDate = new Date();
      expiryDate.setHours(23, 59, 59, 999);
      this.$cookies.set('todayCompletedGoals', this.completedGoalIds, {
        path: '/',
        expires: expiryDate,
        sameSite: 'lax'
      });
    },

    async completeGoal(goalId) {
      try {
        await this.$axios.delete(`http://localhost:8000/api/goals/${goalId}`);
        
        if (!this.completedGoalIds.includes(goalId)) {
          this.completedGoalIds.push(goalId);
          this.saveCompletedGoals();
        }
        
        this.goals = this.goals.filter(g => g.id !== goalId);
        this.showSnackbar('Goal completato!', 'success');
      } catch (error) {
        console.error('Error completing goal:', error);
        this.showSnackbar('Errore durante il completamento', 'error');
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

    priorityLabel(priority) {
      const labels = { 2: 'Alta', 1: 'Media', 0: 'Bassa' };
      return labels[priority] || 'Sconosciuta';
    },

    priorityColor(priority) {
      return { 2: 'red', 1: 'orange', 0: 'green' }[priority] || 'gray';
    },

    priorityClass(priority) {
      return { 2: 'high-priority', 1: 'medium-priority', 0: 'low-priority' }[priority] || '';
    },

    openAddDialog() {
      this.newGoal = {
        title: '',
        description: '',
        priority: 2,
        exp: new Date().toISOString().substr(0, 10)
      };
      this.valid = false;
      this.addDialog = true;
    },

    closeAddDialog() {
      this.addDialog = false;
      this.$refs.addForm.reset();
    },

    async deleteTask(task) {
      try {
        await this.$axios.delete(`http://localhost:8000/api/tasks/${task.id}`);
        this.goals.forEach(goal => {
          if (goal.tasks) {
            const index = goal.tasks.findIndex(t => t.id === task.id);
            if (index !== -1) goal.tasks.splice(index, 1);
          }
        });
        this.showSnackbar('Task rimossa!', 'success');
      } catch (error) {
        console.error('Errore eliminazione task:', error);
        this.showSnackbar('Errore eliminazione task', 'error');
      }
    },

    async saveGoal() {
      if (!this.$refs.addForm.validate()) return;

      this.saving = true;
      try {
        const response = await this.$axios.post('http://localhost:8000/api/goals', {
          ...this.newGoal,
          exp: this.todayDate
        });
        this.goals.push(response.data);
        this.showSnackbar('Goal creato!', 'success');
        this.closeAddDialog();
      } catch (error) {
        console.error('Error creating goal:', error);
        this.showSnackbar("Errore creazione goal", 'error');
      } finally {
        this.saving = false;
      }
    }
  }
};
</script>
  
  <style scoped>
  *{
    font-family: 'Uto-Bold', sans-serif;
  }
  .carousel-container {
    max-width: 1200px;
    margin: 0 auto;
    width: 100%;
    padding: 20px 0;
  }
  
  .goal-card {
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    border-radius: 12px;
    margin: 0 8px;
  }
  
  .goal-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2) !important;
  }
  
  .v-slide-group::-webkit-scrollbar {
    height: 8px;
    background-color: #f5f5f5;
  }
  
  .v-slide-group::-webkit-scrollbar-thumb {
    background-color: #ffd166;
    border-radius: 10px;
  }
  
  .v-slide-group__prev,
  .v-slide-group__next {
    background-color: white !important;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
    border-radius: 50% !important;
  }

  .progress-card {
  background: linear-gradient(135deg, #D3EFBD 0%, #D3EFBD 100%);
  border: 1px solid rgba(0, 0, 0, 0.05) !important;
}

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

.v-progress-linear__background {
  border-radius: 12px !important;
}

.v-progress-linear__determinate {
  border-radius: 12px !important;
  background: linear-gradient(90deg, #4CAF50 0%, #8BC34A 100%) !important;
  box-shadow: 0 2px 4px rgba(76, 175, 80, 0.3);
}
  
  @media (max-width: 960px) {
    .carousel-container {
      padding: 0 16px;
    }
    
    .goal-card {
      width: 280px !important;
      margin: 0 4px;
    }
  
    .v-slide-group::-webkit-scrollbar {
      display: none;
    }
  }
  
  .task-section {
    border: 1px solid #eee;
    border-radius: 8px;
    padding: 8px;
    margin-top: 12px;
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
  
  .v-main {
    overflow-y: auto !important;
    height: 100vh;
  }
  
  .task-list {
    max-height: 150px;
    overflow-y: auto;
  }
  
  .v-btn--fab.v-btn--fixed {
    z-index: 999;
    transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  }
  
  .goal-title {
    font-size: 1.2rem;
    font-weight: bold;
    overflow-wrap: break-word;
  }
  
  .goal-expiry {
    font-size: 0.9rem;
    font-weight: bold;
    color: #d32f2f;
  }

  .no-goals-alert {
  position: relative;
  font-weight: 500;
  text-shadow: 
    0.5px 0.5px 0.5px rgba(0,0,0,0.2),
    -0.5px -0.5px 0.5px rgba(0,0,0,0.2),
    0.5px -0.5px 0.5px rgba(0,0,0,0.2),
    -0.5px 0.5px 0.5px rgba(0,0,0,0.2);
  letter-spacing: 0.3px;
}
  </style>
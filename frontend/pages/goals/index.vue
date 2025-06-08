<template>
  <v-app>
    <NavBar />
    <v-main style="background-color: #fdf3e4">
      <v-container class="py-8">

        <!-- Loader -->
        <v-row v-if="loading" class="d-flex justify-center">
          <v-progress-circular indeterminate color="primary" />
        </v-row>

        <!-- Quando ha finito di caricare -->
        <div v-else class="carousel-container">
          <v-slide-group 
          v-if="activeGoals.length > 0"
          class="pa-4"
            show-arrows
            center-active
          >
            <v-slide-item
            v-for="goal in activeGoals"
              
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
                        <v-icon color="red">mdi-calendar</v-icon>
                        Scadenza: {{ formatDate(goal.exp) }}
                      </p>

                      <v-divider class="my-3"></v-divider>
                      <div class="task-section" v-if="goal.tasks && goal.tasks.length">
                        <v-list dense class="task-list">
                          <v-list-item 
                            v-for="task in goal.tasks" 
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
          <div v-if="expiredGoals.length > 0" class="mt-12">
      <h2 class="section-title error--text mb-4">Goals Scaduti</h2>
      <v-slide-group class="pa-4" show-arrows center-active>
        <v-slide-item
  v-for="goal in expiredGoals"
  :key="'expired-'+goal.id"
  v-slot="{ toggle }"
>
  <div class="carousel-item mx-2">
  
      <v-card 
        class="goal-card expired-card"
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
            <v-icon color="red">mdi-calendar</v-icon>
            Scadenza: {{ formatDate(goal.exp) }}
          </p>

          <v-divider class="my-3"></v-divider>

          <div class="task-section" v-if="goal.tasks && goal.tasks.length">
            <v-list dense class="task-list">
              <v-list-item 
                v-for="task in goal.tasks" 
                :key="task.id"
                class="px-0"
              >
                <v-list-item-icon class="mr-2">
                  <v-checkbox
                    disabled
                    :input-value="false"
                    dense
                    hide-details
                    color="grey"
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
          <v-chip color="error" dark small class="mr-2">
            <v-icon small left>mdi-alert</v-icon>
            SCADUTO
          </v-chip>
          <v-btn icon @click.prevent="openDeleteDialog(goal.id)">
            <v-icon color="error">mdi-delete</v-icon>
          </v-btn>
        </v-card-actions>
      </v-card>
  </div>
</v-slide-item>

      </v-slide-group>
    </div>
          <!-- Nessun goal -->
          <v-col 
          v-else-if="!loading && activeGoals.length === 0"
          cols="12" 
          class="text-center"
        >
            <v-alert 
              type="info" 
              color="amber lighten-4" 
              border="left" 
              elevation="2"
            >
              Non ci sono goals.
            </v-alert>
          </v-col>
        </div>


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
   expiredGoals() {
    const today = new Date().setHours(0,0,0,0);
    return this.goals.filter(goal => {
      const expDate = new Date(goal.exp).setHours(0,0,0,0);
      return expDate < today;
    });
  },
    activeGoals() {
      const today = new Date();
      today.setHours(0,0,0,0);
      return this.goals.filter(goal => {
        const expDate = new Date(goal.exp);
        expDate.setHours(0,0,0,0);
        return expDate >= today;
      });
    }
  },
  async mounted() {
    await this.fetchGoals();
  },
  methods: {
    async fetchGoals() {
      try {
        const res = await this.$axios.get('http://localhost:8000/api/goals');
        this.goals = res.data;
          const expiredUnpenalized = this.expiredGoals.filter(g => !g.penalty_applied);
    if(expiredUnpenalized.length > 0) {
      await this.applyExpiredPenalties(expiredUnpenalized);
    }
      } catch (error) {
        console.error('Error fetching goals:', error);
        this.showSnackbar('Errore nel caricamento dei goals', 'error');
      } finally {
        this.loading = false;
      }
    },

    async applyExpiredPenalties(goals) {
      try {
        const penaltyPoints = goals.reduce((sum, g) => {
        return sum + (g.priority === 2 ? 200 : g.priority === 1 ? 100 : 50);
      }, 0);

      const newExperience = Math.max(this.$auth.user.experience - penaltyPoints, 0);
        
        await this.$axios.put('http://localhost:8000/api/apply-penalties', {
          goal_ids: goals.map(g => g.id),
          penalty_points: penaltyPoints
        });

        await this.$auth.fetchUser();
        
        this.showSnackbar(`Penalità applicata per ${goals.length} goal scaduti`, 'warning');
        
      } catch (error) {
        console.error('Error applying penalties:', error);
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

    async completeGoal(goalId) {
      try {
         await this.$axios.delete(`http://localhost:8000/api/goals/${goalId}`, {
            params: {
              complete: 'true'
            }
         });        
        this.goals = this.goals.filter(g => g.id !== goalId);
        this.showSnackbar('Goal completato!', 'success');
      } catch (error) {
        console.error('Error completing goal:', error);
        this.showSnackbar('Errore durante il completamento', 'error');
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
      
      this.showSnackbar('Task rimossa con successo!', 'success');
    } catch (error) {
      console.error('Errore nell\'eliminazione del task:', error);
      this.showSnackbar('Errore durante l\'eliminazione del task', 'error');
    }
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

    const newGoal = response.data;
    const index = this.goals.findIndex(g => new Date(g.exp) > new Date(newGoal.exp));
    if (index === -1) {
      this.goals.push(newGoal);
    } else {
      this.goals.splice(index, 0, newGoal);
    }

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

.section-title {
  font-size: 1.5rem;
  font-weight: 600;
  margin-left: 24px;
  border-left: 4px solid;
  padding-left: 12px;
}

.expired-card {
  opacity: 0.85;
  position: relative;
  overflow: hidden;
}

.expired-card::after {
  content: "SCADUTO";
  position: absolute;
  top: -0px;
  right: -30px;
  background: #ff5252;
  color: white;
  padding: 15px 30px;
  transform: rotate(45deg);
  font-size: 0.8rem;
  font-weight: bold;
  box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.expired-card .goal-title {
  opacity: 0.7;
}

/* Aggiungi transizione per l'inserimento */
.v-slide-group__content {
  transition: all 0.5s ease-in-out;
}

/* Animazione per il nuovo goal */
.goal-card.new-goal {
  animation: slideIn 0.5s ease-out;
}

@keyframes slideIn {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.expired-card .goal-expiry {
  color: #ff5252 !important;
  font-weight: 800;
}

.expired-card .v-chip {
  opacity: 0.9;
}

.expired-card:hover {
  transform: translateY(-5px) scale(1.02);
  opacity: 0.95;
}
.carousel-container {
  margin: 0 auto;
  padding: 60px 0;
  min-height: 600px;
  overflow: visible;
  position: relative;
}

.v-slide-group {
  overflow: visible !important;
  padding: 20px 0;
}

.goal-card {
  height: 450px !important;
  display: flex;
  flex-direction: column;
  transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
  border-radius: 16px;
  margin: 0 12px;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.3);
  position: relative;
  z-index: 1;
}

.goal-card:hover {
  transform: translateY(-12px) scale(1.02);
  z-index: 3;
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
}

.carousel-item {
  transition: all 0.3s ease;
  margin: 15px 0;
}

.v-container {
  overflow: visible;
}

/* Add decorative elements */
.carousel-container::before {
  content: '';
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 100%;
  height: 100%;
  background: linear-gradient(to right, #ffecd2 0%, #6b4d43 100%);
  opacity: 0.1;
  border-radius: 24px;
  z-index: 0;
}

/* Improve card styling */
.goal-card {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(4px);
  border: 1px solid rgba(255, 255, 255, 0.3);
}

.goal-title {
  font-size: 1.3rem !important;
  line-height: 1.4;
  color: #2c3e50;
}

/* Responsive adjustments */
@media (max-width: 960px) {
  .carousel-container {
    padding: 40px 20px;
    min-height: 500px;
  }
  
  .goal-card {
    width: 280px !important;
    margin: 0 8px;
  }
  
  .goal-card:hover {
    transform: translateY(-8px) scale(1.02);
  }
}

@media (max-width: 600px) {
  .carousel-container {
    padding: 30px 15px;
  }
  
  .goal-card {
    width: 260px !important;
  }
}

.v-slide-group__content{
  height: fit-content !important;
}


/* Scrollbar personalizzata */
.v-slide-group::-webkit-scrollbar {
  height: 8px;
  background-color: #f5f5f5;
}

.v-slide-group::-webkit-scrollbar-thumb {
  background-color: #ffd166;
  border-radius: 10px;
}



/* Frecce di navigazione */
.v-slide-group__prev,
.v-slide-group__next {
  background-color: white !important;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1) !important;
  border-radius: 50% !important;
}

.v-slide-group__prev:hover,
.v-slide-group__next:hover {
  background-color: #ffd166 !important;
}

/* Responsive */
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

/* Mantieni altri stili esistenti */
.v-main {
  overflow-y: auto !important;
  height: 100vh;
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
* {
  font-family: "Uto-Bold", sans-serif !important;

}
.v-main {
  overflow-y: auto !important;
  height: 100vh;
}



.v-container {
  min-height: calc(100vh - 64px); /* 64px è l'altezza della navbar */
  overflow-y: visible !important;
}

/* Modifica la riga esistente */
.v-row {
  min-height: 100%;
  align-content: flex-start;
}

.task-section {
  border: 1px solid #eee;
  border-radius: 8px;
  padding: 8px;
  margin-top: 12px;
}


.task-list {
  max-height: 150px;
  overflow-y: auto;
}

.task-list .v-list-item {
  min-height: 40px;
}

.completed-task {
  opacity: 0.7;
  background-color: #f5f5f5;
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
  min-height: 500px !important; 
  position: relative;
}

.v-card__text {
  flex: 1;
  overflow-y: hidden; 
padding-bottom: 48px;
}

.v-card__actions {
  position: sticky;
  bottom: 0;
  background-color: white;
  z-index: 1;
  box-shadow: 0 -2px 4px rgba(0, 0, 0, 0.05);
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
  height: fit-content;
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

.v-checkbox >>> .v-icon {
  color: #ff5252 !important; /* Colore rosso per l'icona */
}

</style>
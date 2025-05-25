<template>
  <v-app style="height: 100vh">
    <v-main style="background-color: #fdf3e4;">
      <v-container class="py-8">
        <!-- Loader -->
        <v-row v-if="loading" class="d-flex justify-center">
          <v-progress-circular indeterminate color="primary" />
        </v-row>

        <v-card v-else class="mx-auto" max-width="800" elevation="6">
          <v-card-title class="headline d-flex justify-space-between align-center">
            <div>
              <v-text-field v-if="isEditing" v-model="editedGoal.title" outlined dense
                :rules="[v => !!v || 'Il titolo è obbligatorio']"></v-text-field>
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
              <v-date-picker v-model="editedGoal.exp" :min="new Date().toISOString().substr(0, 10)"></v-date-picker>
            </v-menu>
            <template v-else>
              <v-icon left>mdi-calendar</v-icon>
              Scadenza: {{ formatDate(goal.exp) }}
            </template>
          </v-card-subtitle>

          <v-card-text>
            <v-divider class="my-4"></v-divider>

            <v-textarea v-if="isEditing" v-model="editedGoal.description" outlined rows="3" label="Descrizione"
              :rules="[v => !!v || 'La descrizione è obbligatoria']"></v-textarea>
            <div v-else class="description-text">
              {{ goal.description }}
            </div>

            <v-divider class="my-4"></v-divider>
            

            <!-- SEZIONE TASK -->
            <div v-if="goal.tasks && goal.tasks.length">
              <h3 class="mb-3">Task associati:</h3>
              <v-list dense>
                <v-list-item 
                  v-for="(task, index) in goal.tasks" 
                  :key="task.id"
                  class="px-0"
                >
        
                  <v-list-item-content>
                    <v-text-field
                      v-if="task.isEditing"
                      v-model="task.editedTitle"
                      :rules="[v => !!v || 'Il titolo è obbligatorio']"
                      outlined
                      dense
                      @keyup.enter="saveTaskEdit(task)"
                    ></v-text-field>
                    <v-list-item-title 
                      v-else
                      style="font-size: 0.9rem; white-space: normal; word-break: break-word;"
                    >
                     - {{ task.title }}
                    </v-list-item-title>
                  </v-list-item-content>

                  <v-list-item-action class="ml-2">
                    <v-btn 
                      v-if="!task.isEditing"
                      icon
                      @click="startTaskEdit(task)"
                    >
                      <v-icon small>mdi-pencil</v-icon>
                    </v-btn>
                    <v-btn 
                      v-if="task.isEditing"
                      icon
                      color="success"
                      @click="saveTaskEdit(task)"
                    >
                      <v-icon small>mdi-content-save</v-icon>
                    </v-btn>
                    <v-btn 
                      icon
                      color="error"
                      @click="deleteTask(task, index)"
                    >
                      <v-icon small>mdi-delete</v-icon>
                    </v-btn>
                  </v-list-item-action>
                </v-list-item>
              </v-list>

             
            </div>

            <!-- GENERA TASK AUTOMATICI (se non ci sono task) -->
            <div v-else class="text-center">
              <v-btn 
                color="deep-purple" 
                dark 
                @click="generateTasks" 
                :loading="generating"
                class="mt-4"
              >
                <v-icon left>mdi-robot-happy</v-icon>
                Genera task automatici
              </v-btn>
            </div>
            <!-- FINE SEZIONE TASK -->
              <!-- AGGIUNGI NUOVO TASK -->
              <div class="mt-4">
                <v-text-field
                  v-model="newTaskTitle"
                  label="Nuovo task"
                  outlined
                  dense
                  hide-details
                  class="mb-2"
                  @keyup.enter="addNewTask"
                ></v-text-field>
                <v-btn 
                  color="primary"
                  small
                  @click="addNewTask"
                >
                  <v-icon left>mdi-plus</v-icon>
                  Aggiungi Task
                </v-btn>
              </div>

            <v-divider class="my-4"></v-divider>

            <v-select v-if="isEditing" v-model="editedGoal.priority" :items="priorityOptions" label="Priorità"
              item-text="text" item-value="value" outlined></v-select>
          </v-card-text>

          <v-card-actions class="d-flex justify-space-between">
            <v-btn color="primary" @click="$router.go(-1)">
              <v-icon left>mdi-arrow-left</v-icon>
              Torna indietro
            </v-btn>

            <v-btn v-if="isEditing" color="success" @click="saveChanges" :loading="saving">
              <v-icon left>mdi-content-save</v-icon>
              Salva modifiche
            </v-btn>
          </v-card-actions>
        </v-card>

        <!-- TASK SUGGERITI -->
        <v-card v-if="response" class="mx-auto mt-6" max-width="800" elevation="4">
          <v-card-title class="headline">
            <v-icon left color="deep-purple">mdi-checkbox-marked</v-icon>
            Task suggeriti
          </v-card-title>
          <v-card-text>
            <div class="generated-tasks" v-html="parsedResponse"></div>
          </v-card-text>
          <v-card-actions v-if="tasks.length > 0" class="d-flex justify-end mt-4">
            <v-btn color="teal" dark @click="saveTasks" :loading="savingTasks">
              <v-icon left>mdi-content-save-all</v-icon>
              Salva tutti i task ({{ tasks.length }})
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-container>
    </v-main>

    <!-- SNACKBAR -->
    <v-snackbar v-model="snackbar" :color="snackbarColor" timeout="3000">
      {{ snackbarText }}
      <v-btn text @click="snackbar = false">
        <v-icon>mdi-close</v-icon>
      </v-btn>
    </v-snackbar>
  </v-app>
</template>

<script>
import { marked } from 'marked';

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
      generating: false,
      tasks: [],
      savingTasks: false,
      response: null,
      snackbarColor: 'success',
      priorityOptions: [
        { text: 'Alta', value: 2 },
        { text: 'Media', value: 1 },
        { text: 'Bassa', value: 0 }
      ],
      newTaskTitle: '' // Aggiunto per il nuovo task
    }
  },
  computed: {
    parsedResponse() {
      return marked.parse(this.response || '');
    },
  },
  async fetch() {
    try {
      const response = await this.$axios.get(
        `http://localhost:8000/api/goals/${this.$route.params.id}`,
        { params: { with_tasks: true } } // Aggiunto parametro per i task
      );
      
      // Inizializza i task con gli stati per l'editing
      if (response.data.tasks) {
        response.data.tasks = response.data.tasks.map(t => ({
          ...t,
          isEditing: false,
          editedTitle: t.title
        }));
      }

      this.goal = response.data;
      this.editedGoal = { ...response.data };
    } catch (error) {
      console.error('Error fetching goal:', error);
      this.$nuxt.error({ statusCode: 404, message: 'Goal non trovato' });
    } finally {
      this.loading = false;
    }
  },
  methods: {
    // Metodi esistenti per il goal...
    toggleEditMode() {
      this.isEditing = !this.isEditing;
      if (!this.isEditing) {
        this.editedGoal = { ...this.goal };
      }
    },

    async saveChanges() {
      if (!this.editedGoal.title || !this.editedGoal.description) {
        this.showSnackbar('Compila tutti i campi obbligatori', 'error');
        return;
      }

      this.saving = true;
      try {
        const response = await this.$axios.put(
          `http://localhost:8000/api/goals/${this.$route.params.id}`,
          this.editedGoal
        );
        this.goal = response.data;
        this.showSnackbar('Modifiche salvate con successo!', 'success');
        this.isEditing = false;
      } catch (error) {
        console.error('Error updating goal:', error);
        this.showSnackbar("Errore durante il salvataggio", 'error');
      } finally {
        this.saving = false;
      }
    },

    // Metodi per la gestione dei task...
    startTaskEdit(task) {
      task.isEditing = true;
      task.editedTitle = task.title;
    },

    async saveTaskEdit(task) {
      if (!task.editedTitle.trim()) {
        this.showSnackbar('Il titolo del task è obbligatorio', 'error');
        return;
      }

      try {
        const response = await this.$axios.put(`http://localhost:8000/api/tasks/${task.id}`, {
          title: task.editedTitle,
          completed: task.completed
        });
        
        Object.assign(task, {
          ...response.data,
          isEditing: false,
          editedTitle: response.data.title
        });
        
        this.showSnackbar('Task modificato con successo!', 'success');
      } catch (error) {
        console.error('Errore modifica task:', error);
        this.showSnackbar('Errore durante la modifica del task', 'error');
      }
    },

    async deleteTask(task, index) {
      try {
        await this.$axios.delete(`http://localhost:8000/api/tasks/${task.id}`);
        this.goal.tasks.splice(index, 1);
        this.showSnackbar('Task eliminato con successo!', 'success');
      } catch (error) {
        console.error('Errore eliminazione task:', error);
        this.showSnackbar('Errore durante l\'eliminazione del task', 'error');
      }
    },

    async addNewTask() {
      if (!this.newTaskTitle.trim()) {
        this.showSnackbar('Inserisci un titolo per il task', 'error');
        return;
      }

      try {
        const response = await this.$axios.post('http://localhost:8000/api/tasks', {
          title: this.newTaskTitle,
          goal_id: this.$route.params.id,
          exp: this.goal.exp
        });

        if (!this.goal.tasks) this.goal.tasks = [];
        this.goal.tasks.push({
          ...response.data,
          isEditing: false,
          editedTitle: response.data.title
        });
        
        this.newTaskTitle = '';
        this.showSnackbar('Task aggiunto con successo!', 'success');
      } catch (error) {
        console.error('Errore aggiunta task:', error);
        this.showSnackbar('Errore durante l\'aggiunta del task', 'error');
      }
    },

    // Metodi di supporto...
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

    // Metodi esistenti per generare e salvare task automatici...
    async saveTasks() {
      this.savingTasks = true;
      try {
        for (const task of this.tasks) {
          await this.$axios.post('http://localhost:8000/api/tasks', {
            title: task,
            goal_id: this.$route.params.id,
            exp: this.goal.exp
          });
        }

        this.showSnackbar(`${this.tasks.length} task salvati con successo!`, 'success');
        this.tasks = []; 
        this.response = null; 
      } catch (error) {
        console.error('Errore nel salvataggio:', error);
        this.showSnackbar(`Errore durante il salvataggio: ${error.response?.data?.message || ''}`, 'error');
      } finally {
        this.savingTasks = false;
      }
    },

    async generateTasks() {
      this.generating = true;
      try {
        const prompt = `Genera 3 task semplici e concreti per raggiungere questo obiettivo, suddividere questo problema in 3 sottoproblemi ognuno da completare.
        Titolo: ${this.goal.title}
        Descrizione: ${this.goal.description}
        Restituisci SOLO i 3 task in formato elenco puntato markdown senza commenti.`;

        const res = await fetch('https://openrouter.ai/api/v1/chat/completions', {
          method: 'POST',
          headers: {
            Authorization: 'Bearer ',
            'HTTP-Referer': 'https://www.sitename.com',
            'X-Title': 'SiteName',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            model: 'meta-llama/llama-3.3-70b-instruct:free',
            messages: [{ role: 'user', content: prompt }],
          }),
        });

        const data = await res.json();
        this.response = data.choices?.[0]?.message?.content || "";

        const rawTasks = this.response.split('\n').filter(line => line.trim() !== '');
        this.tasks = rawTasks.map(task =>
          task.replace(/^[-*]\s+/g, '').trim()
        );

        console.log("Task generati:", this.tasks);

        if (!this.response) throw new Error("Nessuna risposta dall'AI");

      } catch (error) {
        console.error("Errore nella generazione:", error);
        this.showSnackbar("Errore nella generazione dei task", 'error');
        this.response = null;
      } finally {
        this.generating = false;
      }
    }
  }
}
</script>
<style scoped>
* {
  font-family: "Uto-Bold", sans-serif !important;
}

.v-main {
  overflow-y: auto !important;
  height: 100%;
}

.v-btn--teal {
  background-color: #009688 !important;
  color: white !important;
}

.headline {
  font-family: "Uto-Bold", sans-serif !important;
  font-size: 1.8rem !important;
}

.description-text {
  font-size: 1.1rem;
  white-space: pre-wrap;
}

.v-chip {
  font-family: "Uto-Bold", sans-serif !important;
}

.v-text-field,
.v-textarea {
  font-size: 1.1rem !important;
}

.v-select {
  max-width: 200px;
}

.generated-tasks {
  font-size: 1.1rem;
  line-height: 1.6;
}

.v-list-item__action {
  min-width: 80px;
  justify-content: flex-end;
}

.v-list-item__action .v-btn {
  margin-left: 8px;
}

.generated-tasks ul {
  padding-left: 20px;
}

.generated-tasks li {
  margin-bottom: 8px;
  padding-left: 8px;
}

.generated-tasks li::marker {
  color: #673ab7;
  font-weight: bold;
}
</style>
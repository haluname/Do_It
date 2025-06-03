<template>
  <v-app>
    <NavBarForum />
    <v-main style="background-color: #fdf3e4;">
      <v-container class="py-8">
        <v-row>
          <v-col cols="12">
            <v-card elevation="6" color="#fff5e6" class="mb-6">
              <v-toolbar flat color="orange lighten-5">
                <v-toolbar-title class="font-weight-bold orange--text text--darken-3">
                  <v-icon left>mdi-lightbulb-on</v-icon>
                  Soluzioni Condivise
                </v-toolbar-title>
                <v-spacer></v-spacer>
                <v-btn 
                  v-if="$auth.loggedIn" 
                  color="orange darken-3" 
                  dark 
                  @click="newSolutionDialog = true"
                >
                  <v-icon left>mdi-plus</v-icon>
                  Condividi una Soluzione
                </v-btn>
              </v-toolbar>

              <v-card-text>
                <p class="body-1">
                  In questa sezione, gli utenti condividono problemi comuni e le loro soluzioni. 
                  Se una soluzione ti è stata utile, metti un like per aiutare altri a trovarla più facilmente!
                </p>
              </v-card-text>
            </v-card>

            <!-- Filtri e ordinamento -->
            <div class="d-flex align-center mb-6">
              <v-select
                v-model="sortBy"
                :items="sortOptions"
                label="Ordina per"
                outlined
                dense
                hide-details
                style="max-width: 250px;"
              ></v-select>
              
              <v-text-field
                v-model="searchQuery"
                prepend-inner-icon="mdi-magnify"
                label="Cerca soluzioni..."
                outlined
                dense
                hide-details
                class="ml-4"
                style="max-width: 300px;"
              ></v-text-field>
              
              <v-btn 
                color="orange darken-3" 
                dark 
                class="ml-4"
                @click="loadSolutions"
              >
                <v-icon left>mdi-filter</v-icon>
                Applica
              </v-btn>
            </div>

            <!-- Lista Soluzioni -->
            <v-row>
              <v-col 
                v-for="solution in solutions" 
                :key="solution.id" 
                cols="12" 
                md="6"
              >
                <SolutionCard :solution="solution" @like-toggled="toggleLike" />
              </v-col>
            </v-row>

            <v-pagination 
              v-model="page" 
              :length="totalPages" 
              circle 
              color="orange darken-2" 
              class="mt-6"
              @input="loadSolutions"
            ></v-pagination>
          </v-col>
        </v-row>
      </v-container>
    </v-main>

    <!-- Dialog Nuova Soluzione -->
    <v-dialog v-model="newSolutionDialog" max-width="700" persistent>
      <v-card>
        <v-toolbar color="orange lighten-5" flat>
          <v-toolbar-title class="orange--text text--darken-3">
            Condividi una Soluzione
          </v-toolbar-title>
          <v-spacer></v-spacer>
          <v-btn icon @click="closeDialog" :disabled="validating">
            <v-icon>mdi-close</v-icon>
          </v-btn>
        </v-toolbar>

        <v-card-text class="pt-4">
          <!-- Aggiungi stato di validazione -->
          <v-alert v-if="validationResult" :type="validationResult.valid ? 'success' : 'error'" class="mb-4">
            {{ validationResult.message }}
          </v-alert>

          <!-- Campi del form -->
          <v-text-field 
            v-model="newSolution.title" 
            label="Titolo" 
            outlined 
            :error-messages="titleErrors"
            @input="resetTitleError"
          ></v-text-field>

          <v-textarea 
            v-model="newSolution.problem" 
            label="Problema riscontrato" 
            outlined 
            rows="3"
            :error-messages="problemErrors"
            @input="resetProblemError"
          ></v-textarea>

          <v-textarea 
            v-model="newSolution.solution" 
            label="Soluzione applicata" 
            outlined 
            rows="5"
            :error-messages="solutionErrors"
            @input="resetSolutionError"
          ></v-textarea>
        </v-card-text>

        <v-card-actions>
          <v-spacer></v-spacer>
          <!-- Pulsante con stato di caricamento -->
          <v-btn 
            color="orange darken-3" 
            dark 
            @click="validateAndSubmit" 
            :disabled="validating || submitting"
          >
            <template v-if="validating">
              <v-progress-circular
                indeterminate
                size="20"
                width="2"
                color="white"
                class="mr-2"
              ></v-progress-circular>
              Validazione in corso...
            </template>
            
            <template v-else-if="submitting">
              <v-progress-circular
                indeterminate
                size="20"
                width="2"
                color="white"
                class="mr-2"
              ></v-progress-circular>
              Pubblicazione in corso...
            </template>
            
            <template v-else>
              <v-icon left>mdi-shield-check</v-icon>
              Pubblica Soluzione
            </template>
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </v-app>
</template>

<script>
export default {
  data() {
    return {
      solutions: [],
      validating: false,
      validationResult: null,
      page: 1,
      perPage: 6,
      totalPages: 1,
      sortBy: 'most_liked',
      sortOptions: [
        { text: 'Più apprezzate', value: 'most_liked' },
        { text: 'Più recenti', value: 'newest' },
        { text: 'Più vecchie', value: 'oldest' }
      ],
      searchQuery: '',
      newSolutionDialog: false,
      submitting: false,
      newSolution: {
        title: '',
        problem: '',
        solution: ''
      },
      errors: {}
    }
  },
  computed: {
    titleErrors() {
      return this.errors.title || []
    },
    problemErrors() {
      return this.errors.problem || []
    },
    solutionErrors() {
      return this.errors.solution || []
    }
  },
  methods: {
    async loadSolutions() {
      try {
        const params = {
          page: this.page,
          per_page: this.perPage,
          sort: this.sortBy,
          search: this.searchQuery
        }
        
        const response = await this.$axios.get('/api/solutions', { params })
        
        this.solutions = response.data.data
        this.totalPages = response.data.meta.last_page
      } catch (error) {
        this.$toast.error('Errore nel caricamento delle soluzioni')
      }
    },
    
    resetTitleError() {
      delete this.errors.title
    },
    
    resetProblemError() {
      delete this.errors.problem
    },
    
    resetSolutionError() {
      delete this.errors.solution
    },
    
    async validateContentWithAI() {
      this.validating = true;
      this.validationResult = null;
      
      try {
        const contentToValidate = `
TITOLO: ${this.newSolution.title}
PROBLEMA: ${this.newSolution.problem}
SOLUZIONE: ${this.newSolution.solution}
        `;
        
        const prompt = `Analizza il seguente contributo per un forum di soluzioni. 
Devi rispondere SOLO con JSON: {"valid": boolean, "message": string}

Regole di validazione:
1. VALIDA=true solo se:
   - Contenuto pertinente a studio, produttività o tecnologia
   - Non contiene spam, pubblicità o linguaggio inappropriato
   - Offre valore reale ai lettori
   - Non è duplicato o generico (es. "funziona", "prova così")
   - È scritto in italiano corretto
   - Non essere troppo troppo severo
2. VALIDA=false in tutti gli altri casi
3. Il campo "message" deve spiegare brevemente la decisione

Contenuto da validare:
${contentToValidate}`;

        const response = await fetch('https://openrouter.ai/api/v1/chat/completions', {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${this.$config.openrouterKey}`,
            'HTTP-Referer': 'https://www.doit-app.it',
            'X-Title': 'Do!t Solutions',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            model: 'meta-llama/llama-3.3-70b-instruct:free',
            messages: [{ 
              role: 'user', 
              content: prompt 
            }],
            response_format: { type: "json_object" },
            max_tokens: 150,
          }),
        });

        const data = await response.json();
        const aiResponse = data.choices?.[0]?.message?.content;
        
        // Estrae il JSON dalla risposta
        const jsonMatch = aiResponse.match(/\{[\s\S]*\}/);
        if (jsonMatch) {
          return JSON.parse(jsonMatch[0]);
        }
        
        return {
          valid: false,
          message: "Formato di risposta AI non valido"
        };
      } catch (error) {
        console.error("Errore nella validazione AI:", error);
        return {
          valid: false,
          message: "Errore nel servizio di validazione"
        };
      } finally {
        this.validating = false;
      }
    },
    
    async validateAndSubmit() {
      // Validazione AI
      this.validationResult = await this.validateContentWithAI();
      
      if (!this.validationResult || !this.validationResult.valid) {
        const errorMessage = this.validationResult?.message || "Il contenuto non è valido per la pubblicazione";
        this.$toast.error(`Validazione fallita: ${errorMessage}`);
        return;
      }
      
      // Se la validazione è OK, procedi con l'invio
      await this.submitSolution();
    },
    
    async submitSolution() {
      this.submitting = true;
      try {
        const response = await this.$axios.post('/api/solutions', this.newSolution, {
          headers: {
            Authorization: `Bearer ${this.$auth.strategy.token.get()}`
          }
        });
        
        this.$toast.success('Soluzione pubblicata con successo!');
        this.closeDialog();
        this.loadSolutions();
      } catch (error) {
        if (error.response?.status === 422) {
          this.errors = error.response.data.errors;
        } else {
          this.$toast.error('Errore durante la pubblicazione');
        }
      } finally {
        this.submitting = false;
      }
    },
    
    closeDialog() {
      this.newSolutionDialog = false;
      this.newSolution = { title: '', problem: '', solution: '' };
      this.errors = {};
      this.validationResult = null;
    },
    
    async toggleLike(solutionId) {
      try {
        const response = await this.$axios.post(`/api/solutions/${solutionId}/like`, {}, {
          headers: {
            Authorization: `Bearer ${this.$auth.strategy.token.get()}`
          }
        })

        console.log("Like = " + response.data.likes_count)
        
        // Aggiorna la soluzione locale
        const solution = this.solutions.find(s => s.id === solutionId)
        if (solution) {
          solution.likes_count = response.data.likes_count
          solution.liked_by_user = response.data.liked
        }
      } catch (error) {
        this.$toast.error('Errore durante il like')
      }
    }
  },
  mounted() {
    this.loadSolutions()
  }
}
</script>

<style scoped>


.v-main {
  overflow-y: auto !important;
  height: 100vh;
}
.solution-card {
  transition: all 0.3s ease;
}

.solution-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1) !important;
}
</style>
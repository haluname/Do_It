<template>
  <v-app>
    <NavBar />

    <v-main style="background-color: #fdf3e4; min-height: 100vh; overflow-y: auto;">
      <v-container class="py-8" style="min-height: 90vh;">
        <v-row>

          <v-col cols="12" md="8">
            <v-card elevation="6" rounded="lg" class="mx-auto pa-6" color="#fff2b8">
              <div class="text-center mb-6">
                <h2 class="notebook-title">Note Settimanali</h2>
                <v-chip class="mt-2" color="orange lighten-3">
                  Settimana {{ currentWeek }}
                </v-chip>
              </div>

              <v-row>
                <v-col v-for="(day, index) in notes" :key="index" cols="12" sm="6" md="4">
                  <v-card class="mb-4 pa-3" elevation="2">
                    <div class="day-header">{{ day.name }}</div>
                    <textarea v-model="day.content" class="note-textarea" :placeholder="'Note per ' + day.name"
                      rows="4"></textarea>
                  </v-card>
                </v-col>
              </v-row>  

              <v-btn color="primary" block @click="openReportDialog" :loading="loadingReport">
                <v-icon left>mdi-file-chart</v-icon>
                Ottieni Resoconto
              </v-btn>
            </v-card>
          </v-col>
          <!-- Colonna Livello -->
          <v-col cols="12" md="4">
            <v-card elevation="6" class="level-card" color="#e8f5e9">
              <div class="plant-container">
                <img :src="currentPlantImage" alt="Plant growth" class="plant-gif">
                <div class="level-badge">
                  Livello {{ userLevel }}
                </div>
              </div>

              <v-card-text class="text-center">
                <v-progress-linear :value="levelProgress" height="25" color="light-green darken-2" striped>
                  <strong>{{ levelProgress }}%</strong>
                </v-progress-linear>

                <div class="experience-info mt-2">
                  <v-chip small color="brown lighten-4">
                    {{ currentExperience }}/{{ experienceToNextLevel }} XP
                  </v-chip>
                </div>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </v-main>

    <!-- Citazione Motivazionale -->
    <v-card class="motivational-quote" elevation="8" rounded="xl" v-if="showQuote && quote !== ''" @click="hideQuote">
      <v-icon left color="yellow darken-2">mdi-lightbulb</v-icon>
      <span>{{ quote }}</span>
    </v-card>
   <v-dialog v-model="reportDialog" max-width="800" scrollable>
    <v-card>
      <v-card-title class="headline primary white--text">
        <v-icon color="white" left>mdi-chart-box</v-icon>
        Resoconto Settimanale - Settimana {{ currentWeek }}
      </v-card-title>
      
      <v-card-text class="pa-4">
        <div v-if="reportLoading" class="text-center py-6">
          <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
          <div class="mt-4">Generazione resoconto in corso...</div>
        </div>
        
        <div v-else class="report-content">
          <v-icon color="green" left>mdi-text-box-check</v-icon>
          <div class="report-text">{{ aiReport }}</div>
        </div>
      </v-card-text>

      <v-card-actions>
        <v-spacer></v-spacer>
        <v-btn color="primary" @click="reportDialog = false">
          Chiudi
        </v-btn>
      </v-card-actions>
    </v-card>
  </v-dialog>
  </v-app>
</template>


<script>
export default {
  middleware: 'auth',
  data() {
    return {
      note: '',
      quote: "",
      userLevel: 1,
      currentExperience: 0,
      showQuote: true,
      notes: [
        { name: 'Lunedì', content: '' },
        { name: 'Martedì', content: '' },
        { name: 'Mercoledì', content: '' },
        { name: 'Giovedì', content: '' },
        { name: 'Venerdì', content: '' },
        { name: 'Sabato', content: '' },
        { name: 'Domenica', content: '' }
      ],
      reportDialog: false,
      aiReport: '',
      reportLoading: false, // Aggiunto loadingReport
      loadingReport: false   // Aggiunto per il pulsante
    };
  },

  computed: {
    currentPlantImage() {
      const stage = Math.min(Math.ceil(this.userLevel / 5), 6);
      return `/plants/${stage}.gif`;
    },
    levelProgress() {
      return ((this.currentExperience / this.experienceToNextLevel) * 100).toFixed(1);
    },
    experienceToNextLevel() {
      return Math.floor(Math.pow(this.userLevel * 30, 1.5));
    },
    currentWeek() {
      const now = new Date();
      const startOfYear = new Date(now.getFullYear(), 0, 1);
      const pastDays = (now - startOfYear) / 86400000;
      return Math.ceil((pastDays + startOfYear.getDay() + 1) / 7);
    }
  },

  methods: {
    hideQuote() {
      const quote = document.querySelector('.motivational-quote');
      quote.style.transition = 'opacity 0.5s';
      quote.style.opacity = 0;
      setTimeout(() => {
        this.showQuote = false;
      }, 500);
    },

     async openReportDialog() {
      this.reportDialog = true; 
      await this.generateReport();
    },

    async generateMotivationalQuote() {
      try {
        const res = await fetch('https://openrouter.ai/api/v1/chat/completions', {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${this.$config.openrouterKey}`,
            'HTTP-Referer': 'https://www.sitename.com',
            'X-Title': 'SiteName',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            model: 'meta-llama/llama-3.3-70b-instruct:free',
            messages: [{ role: 'user', content: "Scrivi una breve frase motivazionale che non hai mai scritto prima. stampa solo la frase stessa" }],
          }),
        });

        const data = await res.json();
        this.quote = data.choices?.[0]?.message?.content || "Il successo è la somma di piccoli sforzi ripetuti giorno dopo giorno.";
        sessionStorage.setItem("motivationalQuote", this.quote);
      } catch (error) {
        console.error("Errore nel generare la citazione:", error);
        this.quote = "Credi in te stesso e tutto sarà possibile.";
      }
    },
    loadNotes() {
      if (typeof window !== 'undefined') {
        const currentWeekStr = this.currentWeek.toString();
        const savedWeek = localStorage.getItem('lastSavedWeek');

        console.log('Current Week:', currentWeekStr);
        console.log('Saved Week:', savedWeek);

        if (savedWeek === currentWeekStr) {
          const savedNotes = localStorage.getItem('weeklyNotes');
          console.log(savedNotes)
          if (savedNotes) {
            try {
              const parsedNotes = JSON.parse(savedNotes);
              this.notes = this.notes.map((day, index) => ({
                ...day,
                content: parsedNotes[index]?.content || ''
              }));
            } catch (e) {
              console.error('Error parsing notes:', e);
            }
          }
        } else {
          this.resetNotes();
        }
      }
    },
    resetNotes() {
      this.notes = this.notes.map(day => ({ ...day, content: '' }));
      if (typeof window !== 'undefined') {
        localStorage.setItem('weeklyNotes', JSON.stringify(this.notes));
        localStorage.setItem('lastSavedWeek', this.currentWeek.toString());
      }
    },
    async generateReport() {
      this.reportLoading = true;
      try {
        const prompt = `Analizza queste note settimanali e fornisci un resoconto sulla produttività. 
          Rispondi in italiano in massimo 200 caratteri. Formato risposta:
          [Punteggio 1-5]/5 - [Breve analisi in 2-3 frasi]. 
          Ecco le note giornaliere:\n\n${this.notes.map(d => `${d.name}: ${d.content || 'Nessuna nota'}`).join('\n')}`;

        const response = await fetch('https://openrouter.ai/api/v1/chat/completions', {
          method: 'POST',
          headers: {
            'Authorization': `Bearer ${this.$config.openrouterKey}`,
            'Content-Type': 'application/json'
          },
          body: JSON.stringify({
            model: 'meta-llama/llama-3.3-70b-instruct:free',
            messages: [{ role: 'user', content: prompt }]
          })
        });

        const data = await response.json();
        this.aiReport = data.choices[0].message.content;
        console.log('Resoconto generato:', this.aiReport);
      } catch (error) {
        console.error('Errore generazione resoconto:', error);
        this.aiReport = "Impossibile generare il resoconto. Riprova più tardi.";
      }
      this.reportLoading = false;
    },
    createReportPrompt() {
      return `Analizza queste note settimanali e fornisci un resoconto sulla produttività. 
        Rispondi in italiano in massimo 250 caratteri. Formato risposta il piu sincero possibile e valuta in modo oggettivo anche se non hai fatto nulla oppure se hai fatto qualcosa di ben complicato:
        [Punteggio 1-5]/5 - [Analisi in 4-5 frasi dicendo dove posso migliorare con dei suggerimenti].
        Ecco le note giornaliere:\n\n${this.notes.map(d => `${d.name}: ${d.content || 'Nessuna nota'}`).join('\n')}`;
    },
  },



  async mounted() {
    await this.$auth.fetchUser();
    this.loadNotes();

    this.userLevel = this.$auth.user.level;
    this.currentExperience = this.$auth.user.experience;

    if (sessionStorage.getItem("isPostBack")) {
      this.quote = sessionStorage.getItem("motivationalQuote") || "Il successo è la somma di piccoli sforzi ripetuti giorno dopo giorno.";
    } else {
      sessionStorage.setItem("isPostBack", "true");
      this.generateMotivationalQuote();
    }

  },

    watch: {
      '$auth.user': {
        immediate: true,
        deep: true,
          handler(user) {
            this.userLevel = user?.level || 1;
            this.currentExperience = user?.experience || 0;
          }
    },
    currentWeek: {
      immediate: true,
      handler(newWeek, oldWeek) {
        if (oldWeek !== undefined && newWeek !== oldWeek) {
          this.resetNotes();
        }
      }
    },
    notes: {
      handler(newNotes) {
        if (typeof window !== 'undefined') {
          // Aggiungi un controllo per evitare salvataggi inutili
          if (newNotes.some(note => note.content !== '')) {
            localStorage.setItem('weeklyNotes', JSON.stringify(newNotes));
            localStorage.setItem('lastSavedWeek', this.currentWeek.toString());
          }
        }
      },
      deep: true,
      immediate: true
    }
  }
};
</script>


<style scoped>
* {
  font-family: "Uto-Bold";
}

.v-main {
  overflow-y: auto !important;
  height: 100vh;
}

.level-card {
  border-radius: 16px;
  overflow: hidden;
  position: relative;
}

.plant-container {
  position: relative;
  min-height: 250px;
  background: linear-gradient(180deg, #c8e6c9 0%, #a5d6a7 100%);
}

.plant-gif {
  width: 100%;
  height: 250px;
  object-fit: contain;
}

.level-badge {
  position: absolute;
  top: 16px;
  right: 16px;
  background: rgba(255, 255, 255, 0.9);
  padding: 8px 16px;
  border-radius: 24px;
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.experience-info {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-top: 12px;
}

.report-content {
  background: #fff9e6;
  border-radius: 8px;
  padding: 20px;
  margin: 10px 0;
  border-left: 4px solid #ffc107;
}

.report-text {
  white-space: pre-wrap;
  line-height: 1.6;
  font-size: 1.1em;
  color: #4a4a4a;
}

.v-card-title.headline.primary {
  background-color: #2196F3 !important;
}
/* Animazioni */
.plant-gif {
  transition: transform 0.3s ease;
}

.plant-gif:hover {
  transform: scale(1.05);
}

.day-header {
  font-weight: bold;
  color: #6d4c41;
  margin-bottom: 8px;
  font-size: 1.1em;
}

.report-content {
  white-space: pre-wrap;
  line-height: 1.6;
  font-size: 1.1em;
}

.note-textarea {
  width: 100%;
  border: none;
  resize: none;
  padding: 8px;
  background: transparent;
  font-family: 'Fira Mono', monospace;
  font-size: 0.9em;
}

.note-paper {
  background: repeating-linear-gradient(to bottom,
      #fffde8,
      #fffde8 29px,
      #ffe9b7 30px);
  border: 2px solid #ffc107;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(255, 193, 7, 0.14);
  padding: 25px 20px 10px 40px;
  position: relative;
  min-height: 280px;
  margin-bottom: 10px;
  overflow: hidden;
}

.notebook-title {
  font-size: 2rem;
  color: #6d4c41;
  letter-spacing: 1px;
}

.note-paper::before {
  content: "";
  position: absolute;
  left: 24px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #ffd966;
  opacity: 0.7;
}

.note-textarea {
  width: 100%;
  height: 100%;
  min-height: 220px;
  background: transparent;
  border: none;
  resize: none;
  font-size: 1.1rem;
  color: #6d4c00;
  font-family: 'Fira Mono', 'Consolas', monospace;
  outline: none;
  padding-left: 0;
  line-height: 30px;
  letter-spacing: 0.5px;
}

.note-textarea::placeholder {
  color: #bfa84a;
  opacity: 1;
}

/* Motivational quote e altro stile come prima */
.motivational-quote {
  position: fixed;
  bottom: 20px;
  left: 25%;
  width: 300px;
  padding: 15px;
  background: linear-gradient(145deg, #fff5d6 0%, #ffd8b1 100%);
  color: #333;
  font-size: 1rem;
  font-weight: bold;
  display: flex;
  align-items: center;
  gap: 10px;
  border-radius: 10px;
  box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.2);
  z-index: 10;
  transition: background-color 2s ease;
}

.motivational-quote:hover {
  background-color: #fee26f;
}

.motivational-quote span {
  position: relative;
  display: inline-block;
  padding: 0 30px;
}

.motivational-quote span::before,
.motivational-quote span::after {
  font-family: "Times New Roman", serif;
  font-size: 2.5em;
  color: #f1c40f;
  position: absolute;
  opacity: 0.7;
}

.motivational-quote span::before {
  content: '❝';
  left: -5px;
  top: -15px;
  color: #ffd966;
}

.motivational-quote span::after {
  content: '❞';
  color: #ffd966;
  right: -5px;
  bottom: -25px;
}

@media (max-width: 960px) {
  .motivational-quote {
    bottom: 80px;
  }
}
</style>

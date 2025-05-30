<template>
  <v-app>

    <NavBar />

    <v-main style="background-color: #fdf3e4;">
      <v-container class="py-8">
        <v-row>

          <v-col cols="12" md="8">
            <v-card elevation="6" rounded="lg" class="mx-auto pa-6 notebook-background" max-width="100%">
              <div class="text-center mb-6">
                <h2 class="notebook-title">Note Settimanali</h2>
                <v-chip class="mt-2" color="orange lighten-3">
                  Settimana {{ currentWeek }}
                </v-chip>
              </div>

              <!-- Aggiunto il contorno stile notebook -->
              <div class="note-paper-container">
                <v-row>
                  <v-col v-for="(day, index) in notes" :key="index" cols="12" sm="6" md="4">
                    <div class="day-card">
                      <div class="day-header">{{ day.name }}</div>

                      <!-- Pulsante registrazione -->
                      <div class="voice-controls">
                        <v-btn @click="toggleRecognition(index)" :color="day.isListening ? 'red' : 'primary'" x-small
                          icon :class="{ 'pulse': day.isListening }">
                          <v-icon>{{ day.isListening ? 'mdi-microphone-off' : 'mdi-microphone' }}</v-icon>
                        </v-btn>
                        <span v-if="day.isListening" class="recording-indicator">
                          <v-icon x-small color="red">mdi-record</v-icon>
                          Registrazione...
                        </span>
                      </div>

                      <!-- Textarea con anteprima trascrizione -->
                      <textarea v-model="day.content" class="note-textarea" :placeholder="'Note per ' + day.name"
                        rows="4"></textarea>
                      <div v-if="day.transcript" class="transcript-preview active">
                        Anteprima: {{ day.transcript }}
                      </div>
                    </div>
                  </v-col>
                </v-row>
              </div>

              <v-btn color="primary" block @click="openReportDialog" :loading="loadingReport" class="mt-4">
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
            <v-card elevation="6" class="mt-4" color="#f0e6ff">
              <v-card-text class="text-center">
                <v-icon x-large color="deep-purple darken-2">mdi-forum</v-icon>
                <h3 class="mt-2 mb-4 forum-title">Community di Supporto</h3>
                <v-btn color="deep-purple darken-2" dark large block href="/forum" class="forum-btn">
                  <v-icon left>mdi-account-group</v-icon>
                  ACCEDI AL FORUM
                </v-btn>
                <p class="mt-3 caption grey--text text--darken-1">
                  Condividi esperienze e consigli con altri utenti
                </p>
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
        { name: 'Lunedì', content: '', isListening: false, transcript: '' },
        { name: 'Martedì', content: '', isListening: false, transcript: '' },
        { name: 'Mercoledì', content: '', isListening: false, transcript: '' },
        { name: 'Giovedì', content: '', isListening: false, transcript: '' },
        { name: 'Venerdì', content: '', isListening: false, transcript: '' },
        { name: 'Sabato', content: '', isListening: false, transcript: '' },
        { name: 'Domenica', content: '', isListening: false, transcript: '' }
      ],
      reportDialog: false,
      recognition: null,
      currentDayIndex: null,
      aiReport: '',
      reportLoading: false,
      loadingReport: false
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
          const userId = this.$auth.user.id;
          const savedNotes = localStorage.getItem(`user-${userId}-weeklyNotes`);
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
        const userId = this.$auth.user.id;
        localStorage.setItem(`user-${userId}-weeklyNotes`, JSON.stringify(this.notes));
        localStorage.setItem('lastSavedWeek', this.currentWeek.toString());
      }
    },
    async generateReport() {
      this.reportLoading = true;
      try {
        const prompt = this.createReportPrompt();


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
      this.notes.forEach(day => {
        day.content = '';
      });
      let userId = this.$auth.user.id;
      localStorage.removeItem(`user-${userId}-weeklyNotes`);
    },
    createReportPrompt() {
      return `Analizza queste note settimanali e fornisci un resoconto brutale sulla produttività. 
      Valuta in modo spietato: se ho fatto poco o sempre la stessa cosa (es. solo matematica per giorni), il voto deve essere basso. Non premiare l'abitudine, valuta varietà, intensità e impatto reale del lavoro.

      Rispondi in italiano, massimo 250 caratteri. Formato: 
      [Punteggio da 0 a 10]/10 - [Analisi in qualche frase. Sii tagliente, diretto, senza giustificazioni. Sottolinea carenze, inutili ripetizioni e dove sto sprecando il tempo. Dai consigli pratici su come alzare il livello.]

      Ecco le note giornaliere:\n\n${this.notes.map(d => `${d.name}: ${d.content || 'Nessuna nota'}`).join('\n')}`;

    },

    initSpeechRecognition() {
      if (process.client) {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (SpeechRecognition) {
          this.recognition = new SpeechRecognition();
          this.recognition.continuous = true;
          this.recognition.interimResults = true;
          this.recognition.lang = 'it-IT';

          this.recognition.onresult = (event) => {
            if (this.currentDayIndex === null) return;

            let interimTranscript = '';
            let finalTranscript = '';

            for (let i = event.resultIndex; i < event.results.length; i++) {
              const transcript = event.results[i][0].transcript;
              event.results[i].isFinal ?
                finalTranscript += transcript + ' ' :
                interimTranscript += transcript;
            }

            const day = this.notes[this.currentDayIndex];
            day.transcript = finalTranscript + interimTranscript;
          };

          this.recognition.onend = () => {

            if (this.currentDayIndex !== null) {
              const day = this.notes[this.currentDayIndex];
              day.isListening = false;
              this.currentDayIndex = null;
            }
          };
        }
      }
    },

    toggleRecognition(index) {
      const day = this.notes[index];

      // Verifica che SpeechRecognition sia disponibile
      if (!this.recognition) {
        this.$toast.error("Riconoscimento vocale non supportato in questo browser");
        return;
      }

      if (day.isListening) {
        this.recognition.stop(); // Ferma la registrazione
        day.content = day.transcript; // Sovrascrivi il contenuto della nota con l'anteprima
        day.transcript = ''; // Rimuovi l'anteprima
        day.isListening = false;
        this.currentDayIndex = null;

        // Salva immediatamente in localStorage
        this.saveNotesToLocalStorage();
      } else {
        if (this.currentDayIndex !== null) {
          this.recognition.stop();
        }
        this.currentDayIndex = index;
        day.transcript = '';
        day.isListening = true;
        this.recognition.start();
      }
    },
    saveNotesToLocalStorage() {
      if (typeof window !== 'undefined') {
        const userId = this.$auth.user.id;
        localStorage.setItem(`user-${userId}-weeklyNotes`, JSON.stringify(this.notes));
        localStorage.setItem('lastSavedWeek', this.currentWeek.toString());
      }
    }
  },



  async mounted() {
    await this.$auth.fetchUser();
    this.loadNotes();
    this.initSpeechRecognition();


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
            const userId = this.$auth.user.id;
            localStorage.setItem(`user-${userId}-weeklyNotes`, JSON.stringify(newNotes));
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
.v-main {
  overflow-y: auto !important;
  height: 100vh;
}

.notebook-background {
  background: repeating-linear-gradient(to bottom,
      #fffdf8,
      #fffdf8 29px,
      #ffe9c7 30px) !important;
  border: 2px solid #ffc107;
  box-shadow: 0 2px 8px rgba(255, 193, 7, 0.14);
  position: relative;
}

.note-paper-container {
  padding: 25px 20px 10px 40px;
  position: relative;
}

.note-paper-container::before {
  content: "";
  position: absolute;
  left: 24px;
  top: 0;
  bottom: 0;
  width: 2px;
  background: #ffd966;
  opacity: 0.7;
}

.notebook-title {
  font-size: 2rem;
  color: #6d4c41;
  letter-spacing: 1px;
  text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

.day-card {
  background: rgba(255, 255, 255, 0.7);
  border-radius: 8px;
  border: solid 0.5px #ffd966;
  padding: 15px;
  margin-bottom: 15px;
  transition: all 0.3s ease;
  position: relative;
  padding-top: 40px;
  /* Spazio per i controlli vocali */
}

.day-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}


.voice-controls {
  position: absolute;
  top: 10px;
  right: 10px;
  display: flex;
  align-items: center;
  gap: 8px;
  z-index: 2;
}

.recording-indicator {
  color: #ff4444;
  font-size: 0.8rem;
  display: flex;
  align-items: center;
  gap: 4px;
}

.transcript-preview {
  font-size: 0.8rem;
  color: #6d4c41;
  padding: 4px 8px;
  background: rgba(255, 209, 102, 0.2);
  border-radius: 4px;
  margin-top: 8px;
}

.note-textarea {
  width: 100%;
  background: transparent;
  border: none;
  resize: none;
  font-family: 'Fira Mono', 'Consolas', monospace;
  font-size: 0.95rem;
  color: #6d4c00;
  line-height: 1.5;
  padding: 8px;
  position: relative;
  z-index: 1;
}

.note-textarea:focus {
  outline: none;
  box-shadow: inset 0 0 0 1px #ffd966;
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

.transcript-preview.active {
  background: rgba(255, 0, 0, 0.1);
  border-left: 3px solid #ff4444;
  font-weight: bold;
  animation: flash 1.5s infinite;
}

@keyframes flash {

  0%,
  100% {
    opacity: 1;
  }

  50% {
    opacity: 0.7;
  }
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

/* Animazione pulsazione per il microfono attivo */
@keyframes pulse {
  0% {
    transform: scale(1);
  }

  50% {
    transform: scale(1.1);
  }

  100% {
    transform: scale(1);
  }
}

.pulse {
  animation: pulse 0.8s infinite;
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

.forum-title {
  color: #4527A0;
  font-weight: 700;
  letter-spacing: 0.05em;
  text-transform: uppercase;
}

.forum-btn {
  transition: all 0.3s ease;
  font-weight: 700;
  letter-spacing: 0.05em;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  background: linear-gradient(145deg, #673AB7, #4527A0);
}

.forum-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
}

.v-card.forum-card {
  border-left: 4px solid #673AB7;
  border-radius: 12px;
}

@media (max-width: 960px) {
  .forum-btn {
    font-size: 0.875rem;
    padding: 12px 20px;
  }
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
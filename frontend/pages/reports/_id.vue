<template>
  <v-app>
    <NavBar />
    
    <v-main style="background-color: #fdf3e4;">
      <v-container class="py-8">
        <v-card elevation="6" rounded="xl" class="pa-6 report-card" :class="scoreClass">
          <!-- Stato di caricamento -->
          <div v-if="loading" class="text-center py-12">
            <v-progress-circular indeterminate color="primary" size="80"></v-progress-circular>
            <div class="mt-6 text-h5 font-weight-medium">Caricamento resoconto in corso...</div>
            <p class="mt-2 caption grey--text">Stiamo recuperando tutti i dettagli della tua settimana</p>
          </div>

          <!-- Stato di errore -->
          <div v-else-if="error" class="text-center py-12">
            <v-icon size="80" color="red lighten-1">mdi-alert-circle</v-icon>
            <h3 class="mt-4 red--text text-h4">Errore nel caricamento</h3>
            <p class="mt-3 body-1">{{ error }}</p>
            <v-btn color="primary" @click="$router.push('/reports')" large class="mt-4">
              <v-icon left>mdi-arrow-left</v-icon>
              Torna ai resoconti
            </v-btn>
          </div>

          <!-- Contenuto principale -->
          <div v-else>
            <!-- Header con punteggio -->
            <div class="d-flex align-center justify-space-between flex-wrap mb-8">
              <div>
                <v-chip :color="scoreColor" dark label class="px-4 py-2 score-chip">
                  <span class="text-h4 font-weight-bold mr-1">{{ score }}</span>
                  <span class="text-h6">/10</span>
                </v-chip>
                <div class="mt-2 text-caption grey--text">Punteggio settimanale</div>
              </div>
              
              <div class="text-right">
                <h1 class="text-h3 font-weight-bold primary--text mb-1">Settimana {{ report.week }}</h1>
                <div class="d-flex align-center justify-end">
                  <v-icon small color="grey" class="mr-1">mdi-calendar</v-icon>
                  <div class="text-subtitle-1 grey--text">{{ formatDate(report.created_at) }}</div>
                </div>
              </div>
            </div>

            <v-divider class="my-4"></v-divider>

            <!-- Contenuto del report -->
            <div class="report-content-section">
              <div class="d-flex align-center mb-4">
                <v-icon size="32" color="primary" class="mr-3">mdi-chart-box</v-icon>
                <h2 class="text-h5 font-weight-bold">Analisi della tua settimana</h2>
              </div>
              
              <v-card color="reportSurface" class="pa-6 report-content-card elevation-2">
                <div class="report-content">
                  {{ report.content }}
                </div>
              </v-card>
            </div>

            <!-- Note originali -->
            <div class="mt-8">
              <div class="d-flex align-center mb-4">
                <v-icon size="32" color="orange" class="mr-3">mdi-note-text</v-icon>
                <h2 class="text-h5 font-weight-bold">Note Originali</h2>
              </div>
              
              <v-row>
                <v-col 
                  v-for="(note, index) in report.notes" 
                  :key="index"
                  cols="12" md="6"
                >
                  <v-card class="note-card elevation-2" :class="`note-${index % 3}`">
                    <div class="d-flex align-center pa-3 note-header">
                      <v-icon class="mr-2">mdi-calendar-check</v-icon>
                      <div class="text-subtitle-1 font-weight-bold">{{ note.name }}</div>
                    </div>
                    
                    <v-divider></v-divider>
                    
                    <v-card-text class="note-content">
                      <div class="text-body-1">
                        {{ note.content || 'Nessuna nota per questo giorno' }}
                      </div>
                    </v-card-text>
                  </v-card>
                </v-col>
              </v-row>
            </div>

            <!-- Azioni -->
            <div class="mt-8 d-flex flex-wrap justify-space-between align-center">
              <div>
                <v-btn 
                  color="secondary" 
                  @click="downloadReport" 
                  large
                  class="mr-3"
                  depressed
                >
                  <v-icon left>mdi-download</v-icon>
                  Scarica Resoconto
                </v-btn>
                
                <v-btn 
                  color="primary" 
                  @click="$router.push('/reports')" 
                  large
                  outlined
                >
                  <v-icon left>mdi-arrow-left</v-icon>
                  Torna ai Resoconti
                </v-btn>
              </div>
              
              <v-chip color="orange" dark class="mt-3 mt-sm-0 year-chip">
                <v-icon left>mdi-calendar-star</v-icon>
                Anno {{ report.year }}
              </v-chip>
            </div>
          </div>
        </v-card>
      </v-container>
    </v-main>
    
    <!-- Snackbar per notifiche -->
    <v-snackbar v-model="snackbar.show" :color="snackbar.color" timeout="3000">
      {{ snackbar.message }}
      <template v-slot:action="{ attrs }">
        <v-btn text v-bind="attrs" @click="snackbar.show = false">
          <v-icon>mdi-close</v-icon>
        </v-btn>
      </template>
    </v-snackbar>
  </v-app>
</template>

<script>
export default {
  middleware: 'auth',
  asyncData({ params }) {
    return {
      reportId: params.id,
      report: null,
      loading: true,
      error: null,
      score: 0
    };
  },
  data() {
    return {
      snackbar: {
        show: false,
        message: '',
        color: 'success'
      }
    };
  },
  computed: {
    scoreColor() {
      if (this.score >= 8) return 'green';
      if (this.score >= 5) return 'orange';
      return 'red';
    },
    scoreClass() {
      if (this.score >= 8) return 'score-high';
      if (this.score >= 5) return 'score-medium';
      return 'score-low';
    }
  },
  async mounted() {
    await this.loadReport();
  },
  methods: {
    async loadReport() {
      try {
        this.loading = true;
        const response = await this.$axios.get(`/api/reports/${this.reportId}`);
        this.report = response.data;
        
        // Estrai il punteggio dal contenuto
        const scoreMatch = this.report.content.match(/(\d+)\/10/);
        if (scoreMatch && scoreMatch[1]) {
          this.score = parseInt(scoreMatch[1]);
        } else {
          // Valutazione generica se non c'è punteggio esplicito
          this.score = this.report.content.includes("basso") ? 3 : 
                      this.report.content.includes("medio") ? 5 : 7;
        }
        
        this.loading = false;
      } catch (err) {
        this.error = 'Impossibile caricare il resoconto. Potrebbe non esistere o non hai i permessi necessari.';
        this.loading = false;
      }
    },
    formatDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('it-IT', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric'
      });
    },
    downloadReport() {
      const blob = new Blob([this.report.content], { type: 'text/plain' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = `resoconto-settimana-${this.report.week}-${this.report.year}.txt`;
      link.click();
      this.showSnackbar('Resoconto scaricato!', 'success');
    },
    showSnackbar(message, color) {
      this.snackbar = {
        show: true,
        message,
        color
      };
    }
  }
};
</script>

<style scoped>
/* Stile principale della scheda */
.report-card {
  background: #fffdf8;
  border-radius: 16px;
  transition: all 0.3s ease;
  border-top: 5px solid;
}

.report-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
}

/* Classi di punteggio */
.score-high {
  border-top-color: #4caf50;
}

.score-medium {
  border-top-color: #ff9800;
}

.score-low {
  border-top-color: #f44336;
}

/* Stile del punteggio */
.score-chip {
  min-width: 100px;
  height: 60px !important;
  display: flex;
  justify-content: center;
  align-items: center;
  box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
}

/* Contenuto del report */
.report-content-section {
  margin-top: 24px;
}

.report-content-card {
  border-radius: 12px;
  background-color: #fff;
  border-left: 5px solid var(--v-primary-base);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05) !important;
}

.report-content {
  font-size: 1.1rem;
  line-height: 1.8;
  white-space: pre-wrap;
  min-height: fit-content;
  border-radius: 8px;
}

/* Stile per le note */
.note-card {
  border-radius: 12px;
  overflow: hidden;
  transition: transform 0.3s ease;
}

.note-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1) !important;
}

.note-header {
  background-color: #fff;
  padding: 16px;
}

.note-content {
  padding: 16px;
  min-height: 150px;
  background-color: #fffaf0;
  border-left: 3px solid;
}

.note-0 {
  border-top: 3px solid #4caf50;
}

.note-0 .note-content {
  border-left-color: #4caf50;
}

.note-1 {
  border-top: 3px solid #2196f3;
}

.note-1 .note-content {
  border-left-color: #2196f3;
}

.note-2 {
  border-top: 3px solid #ff9800;
}

.note-2 .note-content {
  border-left-color: #ff9800;
}

/* Stile per il badge dell'anno */
.year-chip {
  font-size: 1.1rem !important;
  height: 40px !important;
  padding: 0 20px !important;
  box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
}

/* Responsive design */
@media (max-width: 960px) {
  .score-chip {
    height: 50px !important;
    min-width: 90px;
    font-size: 1.8rem !important;
  }
  
  .report-content {
    font-size: 1rem;
  }
}

@media (max-width: 600px) {
  .report-card {
    padding: 16px;
  }
  
  .score-chip {
    height: 45px !important;
    min-width: 80px;
    font-size: 1.6rem !important;
  }
  
  h1.text-h3 {
    font-size: 1.8rem !important;
  }
}
</style>
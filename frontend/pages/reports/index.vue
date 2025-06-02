<template>
  <v-app>
    <NavBar />
    
    <v-main style="background-color: #fdf3e4;">
      <v-container class="py-8">
        <!-- Header con titolo e statistiche -->
        <div class="d-flex align-center justify-space-between mb-6">
          <div>
            <h1 class="text-h4 font-weight-bold primary--text">
              <v-icon large color="primary" class="mr-3">mdi-file-chart</v-icon>
              I tuoi Resoconti
            </h1>
            <p class="subtitle-1 grey--text text--darken-1">
              Storico delle analisi delle tue settimane di lavoro
            </p>
          </div>
          
          <v-card color="orange lighten-5" class="px-4 py-2" elevation="0" rounded="lg">
            <div class="text-center">
              <div class="text-h6 font-weight-medium">{{ reports.length }}</div>
              <div class="caption grey--text">Resoconti totali</div>
            </div>
          </v-card>
        </div>

        <v-divider class="my-6"></v-divider>

        <!-- Stato di caricamento -->
        <div v-if="loading" class="text-center py-12">
          <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
          <div class="mt-4 text-h6">Caricamento dei tuoi resoconti...</div>
          <p class="mt-2 caption grey--text">Stiamo recuperando la tua storia di produttività</p>
        </div>

        <!-- Stato senza resoconti -->
        <div v-else-if="reports.length === 0" class="text-center py-12">
          <v-icon size="80" color="grey lighten-1">mdi-file-remove</v-icon>
          <div class="text-h5 mt-4 grey--text text--darken-1">Nessun resoconto ancora</div>
          <p class="mt-2 mb-6 body-1">Inizia a compilare le note settimanali per generare il tuo primo resoconto!</p>
          <v-btn color="primary" large @click="$router.push('/')">
            <v-icon left>mdi-arrow-left</v-icon>
            Torna alla dashboard
          </v-btn>
        </div>

        <!-- Gruppi per anno -->
        <div v-else v-for="(yearGroup, year) in groupedReports" :key="year" class="mb-8">
          <div class="d-flex align-center mb-4">
            <div class="year-badge mr-3">{{ year }}</div>
            <v-divider></v-divider>
          </div>
          
          <!-- Griglia di schede -->
          <v-row>
            <v-col v-for="report in yearGroup" :key="report.id" cols="12" md="6" lg="4">
              <v-card elevation="6" rounded="xl" class="report-card" :class="reportScoreClass(report)">
                <div class="d-flex align-center justify-space-between pa-4">
                  <div>
                    <div class="text-subtitle-1 font-weight-bold">Settimana {{ report.week }}</div>
                    <div class="caption grey--text text--darken-1">{{ formatShortDate(report.created_at) }}</div>
                  </div>
                  
                  <div class="report-score">
                    <div class="score-value">{{ reportScore(report) }}</div>
                    <div class="score-label">/10</div>
                  </div>
                </div>
                
                <v-divider></v-divider>
                
                <v-card-text class="report-content">
                  <div class="text-body-1">{{ truncate(report.content, 120) }}</div>
                </v-card-text>
                
                <v-card-actions class="pa-4">
                  <v-btn 
                    color="primary" 
                    text 
                    @click="openReport(report)"
                    class="mr-2"
                  >
                    <v-icon left small>mdi-eye</v-icon>
                    Dettagli
                  </v-btn>
                  
                  <v-btn 
                    color="grey darken-1" 
                    text 
                    @click="downloadReport(report)"
                  >
                    <v-icon left small>mdi-download</v-icon>
                    Scarica
                  </v-btn>
                  
                  <v-spacer></v-spacer>
                  
                  <v-icon color="grey lighten-1">mdi-calendar</v-icon>
                </v-card-actions>
              </v-card>
            </v-col>
          </v-row>
        </div>
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
  data() {
    return {
      reports: [],
      loading: true,
      snackbar: {
        show: false,
        message: '',
        color: 'success'
      }
    };
  },
  
  computed: {
    // Raggruppa i report per anno
    groupedReports() {
      const groups = {};
      
      this.reports.forEach(report => {
        const year = new Date(report.created_at).getFullYear();
        if (!groups[year]) groups[year] = [];
        groups[year].push(report);
      });
      
      // Ordina gli anni in ordine discendente
      return Object.keys(groups)
        .sort((a, b) => b - a)
        .reduce((acc, year) => {
          // Ordina i report per settimana discendente
          acc[year] = groups[year].sort((a, b) => b.week - a.week);
          return acc;
        }, {});
    }
  },
  
  async mounted() {
    await this.loadReports();
  },
  
  methods: {
    // Carica i report dal server
    async loadReports() {
      try {
        this.loading = true;
        const response = await this.$axios.get('/api/reports');
        this.reports = response.data;
        this.loading = false;
      } catch (error) {
        this.showSnackbar('Errore nel caricamento dei resoconti', 'error');
        this.loading = false;
      }
    },
    
    // Estrae il punteggio dal contenuto del report
    reportScore(report) {
      // Cerca un pattern del tipo "8/10" nel testo
      const match = report.content.match(/(\d+)\/10/);
      return match ? match[1] : '?';
    },
    
    // Restituisce la classe CSS in base al punteggio
    reportScoreClass(report) {
      const score = this.reportScore(report);
      
      if (score === '?') return 'score-unknown';
      if (score >= 8) return 'score-high';
      if (score >= 6) return 'score-medium';
      return 'score-low';
    },
    
    // Formatta la data in versione breve
    formatShortDate(dateString) {
      const date = new Date(dateString);
      return date.toLocaleDateString('it-IT', { 
        day: '2-digit',
        month: 'short',
      });
    },
    
    // Tronca il testo
    truncate(text, length) {
      if (!text) return '';
      return text.length > length 
        ? text.substring(0, length) + '...' 
        : text;
    },
    
    // Apri dettaglio report
    openReport(report) {
      this.$router.push(`/reports/${report.id}`);
    },
    
    // Scarica report
    downloadReport(report) {
      const blob = new Blob([report.content], { type: 'text/plain' });
      const link = document.createElement('a');
      link.href = URL.createObjectURL(blob);
      link.download = `resoconto-settimana-${report.week}-${report.year}.txt`;
      link.click();
      this.showSnackbar('Resoconto scaricato!', 'success');
    },
    
    // Mostra snackbar
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
/* Stili personalizzati */
.year-badge {
  background-color: #e3f2fd;
  color: #1976d2;
  font-weight: 700;
  padding: 4px 12px;
  border-radius: 20px;
  min-width: 60px;
  text-align: center;
}

.report-card {
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  border-top: 4px solid transparent;
  overflow: hidden;
}

.report-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 20px -10px rgba(0, 0, 0, 0.15) !important;
}

.report-card.score-high {
  border-top-color: #4caf50;
}

.report-card.score-medium {
  border-top-color: #ff9800;
}

.report-card.score-low {
  border-top-color: #f44336;
}

.report-card.score-unknown {
  border-top-color: #9e9e9e;
}

.report-score {
  display: flex;
  align-items: flex-end;
  line-height: 1;
}

.score-value {
  font-size: 1.8rem;
  font-weight: 700;
}

.report-card.score-high .score-value {
  color: #4caf50;
}

.report-card.score-medium .score-value {
  color: #ff9800;
}

.report-card.score-low .score-value {
  color: #f44336;
}

.report-card.score-unknown .score-value {
  color: #9e9e9e;
}

.score-label {
  font-size: 0.9rem;
  color: #9e9e9e;
  margin-left: 2px;
  margin-bottom: 5px;
}

.report-content {
  min-height: 120px;
  padding: 16px;
  background-color: rgba(255, 255, 255, 0.7);
  border-radius: 0 0 12px 12px;
}

/* Effetti di transizione */
.v-enter-active, .v-leave-active {
  transition: opacity 0.5s, transform 0.5s;
}
.v-enter, .v-leave-to {
  opacity: 0;
  transform: translateY(20px);
}

/* Responsive */
@media (max-width: 960px) {
  .report-card {
    margin-bottom: 24px;
  }
  
  .year-badge {
    margin-bottom: 16px;
  }
}
</style>
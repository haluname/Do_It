<template>
    <v-app>
      <NavBar v-if="!sessionActive" />
      <v-main style="background-color: #fdf3e4;">
        <v-container class="fill-height">
          <v-row align="center" justify="center">
            <v-col cols="12" md="8" lg="6">
              <v-card class="text-center pa-8" elevation="6">
                <div v-if="!sessionActive">
                  <h1 class="mb-6">Sessione di Studio Pomodoro</h1>
                  <v-btn 
                    x-large 
                    color="primary" 
                    @click="startSession"
                    :disabled="starting"
                  >
                    <v-icon left>mdi-play</v-icon>
                    Inizia Sessione da 20 minuti
                  </v-btn>
                </div>
  
                <div v-else>
                  <h1 class="display-3 mb-4">{{ formattedTime }}</h1>
                  <v-chip color="primary" class="mb-4">
                    <v-icon left>mdi-timer</v-icon>
                    Tempo rimanente
                  </v-chip>
                  
                  <div v-if="sessionFailed" class="error-message">
                    <v-icon color="red" large>mdi-alert</v-icon>
                    <h2 class="mt-2">Sessione interrotta!</h2>
                  </div>
  
                  <div v-else-if="sessionCompleted" class="success-message">
                    <v-icon color="green" large>mdi-check-circle</v-icon>
                    <h2 class="mt-2">Sessione completata con successo!</h2>
                  </div>
  
                  <v-btn 
                    v-else 
                    color="error" 
                    @click="endSession"
                    class="mt-4"
                  >
                    Interrompi Sessione
                  </v-btn>
                </div>
              </v-card>
            </v-col>
          </v-row>
        </v-container>
      </v-main>
    </v-app>
  </template>
  
  <script>
  export default {
    middleware: 'auth',
    data() {
      return {
        sessionActive: false,
        starting: false,
        sessionFailed: false,
        sessionCompleted: false,
        timeLeft: 20 * 60, // 20 minuti in secondi
        timer: null,
        visibilityListener: null,
        fullscreenEnabled: false
      }
    },
    computed: {
      formattedTime() {
        const minutes = Math.floor(this.timeLeft / 60)
        const seconds = this.timeLeft % 60
        return `${minutes}:${seconds.toString().padStart(2, '0')}`
      }
    },
    methods: {
      async startSession() {
        this.starting = true
        try {
          await this.enterFullscreen()
          this.sessionActive = true
          this.startTimer()
          this.addVisibilityListeners()
        } catch (error) {
          console.error('Errore fullscreen:', error)
          this.sessionFailed = true
        }
        this.starting = false
      },
  
      enterFullscreen() {
        return new Promise((resolve, reject) => {
          const element = document.documentElement
          if (element.requestFullscreen) {
            element.requestFullscreen().then(resolve).catch(reject)
          } else if (element.mozRequestFullScreen) { /* Firefox */
            element.mozRequestFullScreen().then(resolve).catch(reject)
          } else if (element.webkitRequestFullscreen) { /* Chrome, Safari and Opera */
            element.webkitRequestFullscreen().then(resolve).catch(reject)
          } else if (element.msRequestFullscreen) { /* IE/Edge */
            element.msRequestFullscreen().then(resolve).catch(reject)
          } else {
            reject(new Error('Fullscreen non supportato'))
          }
        })
      },
  
      startTimer() {
        this.timer = setInterval(() => {
          if (this.timeLeft > 0) {
            this.timeLeft--
          } else {
            this.sessionCompleted = true
            this.endSession()
          }
        }, 1000)
      },
  
      addVisibilityListeners() {
        // Controlla se l'utente esce dalla pagina
        this.visibilityListener = () => {
          if (document.hidden && !this.sessionCompleted) {
            this.sessionFailed = true
            this.endSession()
          }
        }
        
        document.addEventListener('visibilitychange', this.visibilityListener)
        window.addEventListener('beforeunload', this.beforeUnloadHandler)
      },
  
      beforeUnloadHandler(e) {
        if (!this.sessionCompleted) {
          e.preventDefault()
          e.returnValue = 'La sessione è ancora in corso! Sei sicuro di voler uscire?'
        }
      },
  
      endSession() {
        clearInterval(this.timer)
        this.sessionActive = false
        
        if (document.fullscreenElement) {
          document.exitFullscreen()
        }
  
        // Pulizia event listeners
        document.removeEventListener('visibilitychange', this.visibilityListener)
        window.removeEventListener('beforeunload', this.beforeUnloadHandler)
      }
    },
    beforeDestroy() {
      this.endSession()
    }
  }
  </script>
  
  <style scoped>
  .fill-height {
    min-height: calc(100vh - 128px);
  }
  
  .error-message {
    color: #d32f2f;
    padding: 20px;
  }
  
  .success-message {
    color: #2e7d32;
    padding: 20px;
  }
  
  .display-3 {
    font-family: 'Uto-Bold', sans-serif !important;
    font-size: 4rem !important;
  }
  </style>
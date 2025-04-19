<template>
  <v-app>
    <NavBar v-if="!sessionActive" />
    <v-main style="background-color: #fdf3e4;">
      <v-container class="fill-height">
        <v-row align="center" justify="center">
          <v-col cols="12" md="8" lg="6">
            <v-card class="text-center pa-8 session-box" elevation="6">
              
              <div v-if="!sessionActive">
                <h1 class="mb-6">Sessione di Studio</h1>
                <div class="mb-6">
                  <h3 class="mb-2">Sessioni completate oggi: {{ sessionsToday }}</h3>
                  <v-dialog v-model="goalDialog" max-width="400">
                    <template v-slot:activator="{ on }">
                      <v-chip 
                        :color="goalAchieved ? 'success darken-2' : 'orange'" 
                        text-color="white" 
                        v-on="on" 
                        style="cursor: pointer"
                      >
                        <v-icon left>mdi-target</v-icon>
                        Obiettivo: {{ dailyGoal }} ({{ sessionsToday }}/{{ dailyGoal }})
                      </v-chip>
                    </template>
                    <v-card>
                      <v-card-title>Imposta Obiettivo Giornaliero</v-card-title>
                      <v-card-text>
                        <v-select
                          v-model="dailyGoal"
                          :items="[1, 2, 3, 4, 5, 6, 7, 8]"
                          label="Sessioni giornaliere"
                          @change="updateDailyGoal"
                        ></v-select>
                      </v-card-text>
                    </v-card>
                  </v-dialog>
                </div>
                
                <v-row class="mb-4">
                  <v-col
                    v-for="duration in durations"
                    :key="duration"
                    cols="6"
                    sm="3"
                  >
                    <v-btn
                      block
                      :color="selectedDuration === duration ? 'primary' : 'grey lighten-2'"
                      @click="selectedDuration = duration"
                    >
                      {{ duration }} min
                    </v-btn>
                  </v-col>
                </v-row>

                <v-btn 
                  x-large 
                  color="primary" 
                  @click="startSession"
                  :disabled="starting"
                >
                  <v-icon left>mdi-play</v-icon>
                  Inizia Sessione ({{ selectedDuration }} min)
                </v-btn>
              </div>

              <div v-else>
                <div class="timer-wrapper">
                  <v-progress-circular
                    :value="progress"
                    :size="300"
                    :width="15"
                    :color="progressColor"
                    rotate="0"
                  >
                    <h1 class="display-3 mb-4">{{ formattedTime }}</h1>
                  </v-progress-circular>
                </div>
           
                
                <div v-if="sessionFailed" class="error-message">
                  <v-icon color="red" large>mdi-alert-octagon</v-icon>
                  <h2 class="mt-2">Sessione interrotta!</h2>
                  <p>La sessione non verrà conteggiata</p>
                </div>

                <div v-else-if="sessionCompleted" class="success-message">
                  <v-icon color="green" large>mdi-check-circle</v-icon>
                  <h2 class="mt-2">Sessione completata con successo!</h2>
                  <p>Sessioni completate oggi: {{ sessionsToday }}</p>
                </div>

                <v-btn 
                  v-else 
                  color="error" 
                  @click="interruptSession"
                  class="mt-4"
                >
                  Interrompi Sessione
                </v-btn>
              </div>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
        <!-- Controlli Audio in basso a destra -->
        <v-card class="audio-player" elevation="8" rounded="xl" v-if="sessionActive">
      <div class="player-controls">
        <v-select
          v-model="selectedSound"
          :items="sounds"
          item-text="name"
          item-value="path"
          label="Seleziona musica"
          dense
          outlined
          @change="changeSound"
          class="music-selector"
          hide-details
        ></v-select>
        
        <v-btn icon @click="togglePlay">
          <v-icon>{{ isPlaying ? 'mdi-pause' : 'mdi-play' }}</v-icon>
        </v-btn>
        <v-btn icon @click="stopAudio">
          <v-icon>mdi-stop</v-icon>
        </v-btn>
        <v-slider v-model="volume" min="0" max="1" step="0.1" dense hide-details @input="setVolume" />
      </div>
    </v-card>

    <!-- Audio con loop automatico -->
    <audio ref="audioPlayer" @ended="handleAudioEnd" loop>
      <source :src="selectedSound" type="audio/mpeg">
    </audio>
    </v-main>

    <!-- Alert Personalizzato -->
    <v-dialog
      v-model="showInterruptionAlert"
      persistent
      max-width="400"
      hide-overlay
    >
      <v-card>
        <v-card-title class="headline red lighten-2 white--text">
          <v-icon large left color="white">mdi-alert-octagon</v-icon>
          ATTENZIONE
        </v-card-title>
        <v-card-text class="text-center pa-8">
          <div class="text-h5 red--text text--darken-2 font-weight-bold">
            SESSIONE INTERROTTA!
          </div>
          <p class="mt-4">La sessione non verrà conteggiata</p>
        </v-card-text>
        <v-card-actions class="justify-center pb-6">
          <v-btn
            color="red darken-2"
            large
            @click="closeAlert"
          >
            OK
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>

    <!-- Theme Color per Mobile -->
    <meta name="theme-color" :content="themeColor">
  </v-app>
</template>

<script>
export default {
  middleware: 'auth',
  data() {
    return {
      durations: [15, 30, 45, 60],
      selectedDuration: 15,
      dailyGoal: 4,
      sessionsToday: 0,
      sessionActive: false,
      starting: false,
      sessionFailed: false,
      sessionCompleted: false,
      timeLeft: 0,
      timer: null,
      visibilityListener: null,
      keyListener: null,
      showInterruptionAlert: false,
      originalFavicon: '/img/logoSMALL.svg',
      alertFavicon: '/alert-favicon.ico',
      originalTitle: 'Pomodoro Timer',
      themeColor: '#fdf3e4',
      titleInterval: null,
      goalDialog: false,
      sounds: [
        { name: 'Sunset Vibes', path: '/audio/Sunset Vibes.mp3' },
        { name: 'Rain', path: '/audio/rain.mp3' },
      ],
      selectedSound: '/audio/Sunset Vibes.mp3',
      isPlaying: false,
      volume: 0.5,
      tickAudio: null,

    }
  },
  computed: {
    formattedTime() {
      const minutes = Math.floor(this.timeLeft / 60)
      const seconds = this.timeLeft % 60
      return `${minutes}:${seconds.toString().padStart(2, '0')}`
    },
    progress() {
      const totalTime = this.selectedDuration * 60
      return ((totalTime - this.timeLeft) / totalTime) * 100
    },
    progressColor() {
      if (this.sessionFailed) return 'red'
      if (this.sessionCompleted) return 'green'
      return 'primary'
    },
    goalAchieved() {
      return this.sessionsToday >= this.dailyGoal
    }
  },
  mounted() {
    this.loadSessions()
    this.originalTitle = document.title
    if (process.client) {
      this.tickAudio = new Audio('/audio/tick.mp3')
      this.tickAudio.preload = 'auto'
    }
  },
  methods: {
    setCookie(name, value) {
      const date = new Date()
      date.setHours(24, 0, 0, 0) 
      document.cookie = `${name}=${value}; expires=${date.toUTCString()}; path=/; SameSite=Lax`
    },

    getCookie(name) {
      return document.cookie
        .split('; ')
        .find(row => row.startsWith(name + '='))
        ?.split('=')[1]
    },
    loadSessions() {
      if (!process.client) return
      this.sessionsToday = parseInt(this.getCookie('pomodoroSessions')) || 0
      this.dailyGoal = parseInt(this.getCookie('dailyGoal')) || 4
    },

    updateSessions() {
      if (!process.client) return
      this.setCookie('pomodoroSessions', this.sessionsToday)
    },

    updateDailyGoal() {
      if (!process.client) return
      this.setCookie('dailyGoal', this.dailyGoal)
      this.goalDialog = false
    },

    async startSession() {
      this.starting = true
      this.timeLeft = this.selectedDuration * 60
      try {
        await this.enterFullscreen()
        this.sessionActive = true
        this.sessionFailed = false
        this.sessionCompleted = false
        this.startTimer()
        this.addEventListeners()
        this.setupFullscreenListener()
      } catch (error) {
        console.error('Errore fullscreen:', error)
        this.sessionFailed = true
        this.endSession()
      }
      this.starting = false
    },

    enterFullscreen() {
      return new Promise((resolve, reject) => {
        const element = document.documentElement
        if (element.requestFullscreen) {
          element.requestFullscreen().then(resolve).catch(reject)
        } else if (element.mozRequestFullScreen) {
          element.mozRequestFullScreen().then(resolve).catch(reject)
        } else if (element.webkitRequestFullscreen) {
          element.webkitRequestFullscreen().then(resolve).catch(reject)
        } else if (element.msRequestFullscreen) {
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
          this.sessionsToday++
          this.updateSessions()
          this.endSession()
        }
      }, 1000)
    },

    addEventListeners() {
      this.visibilityListener = () => {
        if (document.hidden && !this.sessionCompleted) {
          this.handleSessionInterruption()
        }
      }

      this.keyListener = (e) => {
        if (e.key === 'Escape' && this.sessionActive) {
          if (document.fullscreenElement) {
            this.handleSessionInterruption()
          }
          e.stopPropagation()
        }
      }

      document.addEventListener('keydown', this.keyListener, { capture: true })
      document.addEventListener('visibilitychange', this.visibilityListener)
      window.addEventListener('beforeunload', this.beforeUnloadHandler)
    },

    beforeUnloadHandler(e) {
      if (!this.sessionCompleted) {
        e.preventDefault()
        e.returnValue = 'La sessione è ancora in corso! Sei sicuro di voler uscire?'
      }
    },

    startBrowserAlert() {
      this.changeFavicon(this.alertFavicon)
      let count = 0
      this.titleInterval = setInterval(() => {
        document.title = count++ % 2 === 0 ? '⚠️ SESSIONE INTERROTTA! ⚠️' : this.originalTitle
      }, 500)
      this.themeColor = '#ffeb3b'
    },

    resetBrowserAlert() {
      this.changeFavicon(this.originalFavicon)
      clearInterval(this.titleInterval)
      document.title = this.originalTitle
      this.themeColor = '#fdf3e4'
    },

    changeFavicon(url) {
      const link = document.querySelector("link[rel*='icon']") || document.createElement('link')
      link.type = 'image/x-icon'
      link.rel = 'shortcut icon'
      link.href = url + '?t=' + Date.now()
      document.head.appendChild(link)
    },

    endSession() {
      this.stopAudio();
      clearInterval(this.timer)
      this.sessionActive = false
      
      if (document.fullscreenElement) {
        document.exitFullscreen()
      }

      if (this.sessionCompleted) {
        // Riproduci il suono solo se la sessione è completata con successo
        if (this.tickAudio) {
          this.tickAudio.currentTime = 0
          this.tickAudio.play().catch(() => {
            console.log("Errore nella riproduzione dell'audio di conferma")
          })
        }
        this.resetBrowserAlert()
      }

      if (this.sessionFailed) {
        this.showInterruptionAlert = true
      }

      document.removeEventListener('visibilitychange', this.visibilityListener)
      document.removeEventListener('keydown', this.keyListener)
      window.removeEventListener('beforeunload', this.beforeUnloadHandler)
    },

    closeAlert() {
      this.showInterruptionAlert = false
      this.sessionFailed = false
      this.resetBrowserAlert()
    },

    interruptSession() {
      this.sessionFailed = true
      this.startBrowserAlert()
      this.endSession()
    },
    handleSessionInterruption() {
      this.sessionFailed = true
      this.startBrowserAlert()
      this.endSession()
    },
    setupFullscreenListener() {
      document.addEventListener('fullscreenchange', () => {
        if (!document.fullscreenElement && this.sessionActive) {
          this.handleSessionInterruption()
        }
      })
    },

    togglePlay() {
      const audio = this.$refs.audioPlayer;
      if (this.isPlaying) {
        audio.pause();
      } else {
        audio.volume = this.volume;
        audio.play().catch(() => {
          this.isPlaying = false;
        });
      }
      this.isPlaying = !this.isPlaying;
    },

    handleAudioEnd() {
      const audio = this.$refs.audioPlayer;
      audio.currentTime = 0;
      audio.play().catch(() => {
        this.isPlaying = false;
      });
    },

    changeSound() {
      const audio = this.$refs.audioPlayer;
      const wasPlaying = this.isPlaying;
      
      audio.pause();
      audio.src = this.selectedSound;
      audio.load();
      
      if (wasPlaying) {
        audio.play().catch(() => {
          this.isPlaying = false;
        });
      }
    },

    stopAudio() {
      const audio = this.$refs.audioPlayer;
      audio.pause();
      audio.currentTime = 0;
      this.isPlaying = false;
    },

    setVolume() {
      this.$refs.audioPlayer.volume = this.volume;
    },

    fadeIn(audio) {
      let vol = 0;
      const interval = setInterval(() => {
        if (vol < this.volume) {
          vol += 0.05;
          audio.volume = vol;
        } else {
          clearInterval(interval);
        }
      }, 1000);
    }
  },
  beforeDestroy() {
    this.endSession()
  }
}
</script>

<style scoped>
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

*{
  font-family: "Uto-Bold", sans-serif !important;
}

.session-box {
  border-radius: 24px;
  background-color: #FDFAF6;
  box-shadow: 
    12px 12px 24px #e0c76a,
    -12px -12px 24px #ffffe0;
  transition: all 0.3s ease-in-out;
  padding: 40px !important;
  backdrop-filter: blur(4px);
  position: relative;
  overflow: hidden;
  transition: transform 0.4s ease, box-shadow 0.4s ease;

}

.session-box h1{
  color: #4B5563;
}
.session-box h3{
  color: #6B7280;
}
.session-box::before {
  content: '';
  position: absolute;
  top: -50px;
  right: -50px;
  width: 150px;
  height: 150px;
  background: rgba(255, 255, 255, 0.2);
  border-radius: 50%;
  z-index: 0;
}

.session-box h1,
.session-box h2,
.session-box h3 {
  z-index: 1;
  position: relative;
}

.session-box .v-btn {
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: bold;
}

.session-box:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 12px 48px rgba(0, 0, 0, 0.2);
}

.audio-player {
  position: fixed;
  display: flex;
  justify-content: space-between;
  align-items: center;
  bottom: 20px;
  right: 20px;
  width: 400px; /* Larghezza aumentata */
  padding: 15px;
  background: #34495e;
  color: white;
  z-index: 1000;
}

.player-controls {
  display: grid;
  grid-template-columns: 1fr auto auto auto;
  align-items: center;
  gap: 10px;
}


.music-selector {
  flex-grow: 1;
}

.music-selector >>> .v-select__selection {
  color: white !important;
}

.music-selector >>> .v-input__control {
  min-height: 40px !important;
}

.music-selector >>> .v-input__slot {
  background: rgba(255, 255, 255, 0.1) !important;
}
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

.timer-wrapper {
  position: relative;
  margin: 20px auto;
  width: 300px;
  height: 300px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.v-progress-circular {
  position: absolute;
  top: 0;
  left: 0;
}

@keyframes tab-alert {
  0% { background-color: #ffeb3b; }
  50% { background-color: #ff5722; }
  100% { background-color: #ffeb3b; }
}

@keyframes pulse {
  0% { transform: scale(1); }
  50% { transform: scale(1.05); }
  100% { transform: scale(1); }
}

.v-dialog {
  z-index: 1000 !important;
}

body:has(.v-dialog--active) {
  animation: tab-alert 1s infinite;
}

.v-chip.success--text {
  animation: pulse 1.5s infinite;
}


.v-btn {

  transition: transform 0.2s ease, box-shadow 0.2s ease;
 
}

.v-btn:hover:not(.v-btn--disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

.v-btn:active:not(.v-btn--disabled) {
  transform: translateY(0);
  box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
}

.v-btn--disabled,
.v-btn:disabled {
  opacity: 0.6 !important;
  pointer-events: none;
}

.v-btn:focus {
  outline: none;
}
.v-btn:focus::after {
  content: "";
  position: absolute;
  top: 0; left: 0; right: 0; bottom: 0;
  border: 2px solid rgba(255, 255, 255, 0.5);
  border-radius: 12px;
  pointer-events: none;
}


</style>
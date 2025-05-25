<template>
  <div>
    <v-btn 
      @click="toggleRecognition"
      :color="isListening ? 'red' : 'primary'"
      class="mb-4"
    >
      <v-icon left>{{ isListening ? 'mdi-microphone-off' : 'mdi-microphone' }}</v-icon>
      {{ isListening ? 'Ferma' : 'Inizia a parlare' }}
    </v-btn>

    <div class="transcription-box pa-4">
      <div v-if="isListening" class="recording-indicator">
        <v-icon color="red">mdi-record</v-icon>
        Registrazione in corso...
      </div>
      <div class="transcript">{{ transcript }}</div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      isListening: false,
      transcript: '',
      recognition: null
    }
  },

  mounted() {
    if (process.client) {
      const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition
      
      if (SpeechRecognition) {
        this.recognition = new SpeechRecognition()
        this.recognition.continuous = true
        this.recognition.interimResults = true
        this.recognition.lang = 'it-IT'

        this.recognition.onresult = (event) => {
          let interimTranscript = ''
          let finalTranscript = ''

          for (let i = event.resultIndex; i < event.results.length; i++) {
            const transcript = event.results[i][0].transcript
            if (event.results[i].isFinal) {
              finalTranscript += transcript + ' '
            } else {
              interimTranscript += transcript
            }
          }

          this.transcript = finalTranscript + interimTranscript
        }

        this.recognition.onerror = (event) => {
          console.error('Errore:', event.error)
          this.isListening = false
        }

        this.recognition.onend = () => {
          this.isListening = false
        }
      }
    }
  },

  methods: {
    toggleRecognition() {
      if (this.isListening) {
        this.recognition.stop()
        console.log('Registrazione fermata')
        console.log('Trascrizione finale:', this.transcript)
      } else {
        this.transcript = ''
        this.recognition.start()
      }
      this.isListening = !this.isListening
    }
  }
}
</script>

<style scoped>
.transcription-box {
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  min-height: 150px;
  background-color: #f8f9fa;
}

.recording-indicator {
  color: #dc3545;
  font-weight: 500;
  margin-bottom: 8px;
}

.transcript {
  font-size: 1.1rem;
  line-height: 1.6;
  white-space: pre-wrap;
}
</style>
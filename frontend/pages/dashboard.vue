<template>
  <v-app>
    <NavBar />

    <v-main style="background-color: #fdf3e4;">
      <v-container class="py-8">
        <v-card elevation="6" rounded="lg" class="mx-auto pa-4" color="#fff2b8">
          <div class="text-center mb-6">
            <h2>Bot</h2>
            <v-text-field v-model="userInput" label="Enter your question" solo></v-text-field>
            <v-btn color="success" @click="sendMessage">Ask!</v-btn>
            <div id="response" v-html="parsedResponse"></div>
          </div>
        </v-card>
      </v-container>
    </v-main>

    <!-- Citazione Motivazionale -->
    <v-card class="motivational-quote" elevation="8" rounded="xl" v-if="showQuote" @click="hideQuote">
      <v-icon left color="yellow darken-2">mdi-lightbulb</v-icon>
      <span>{{ quote }}</span>
    </v-card>




    <!-- Controlli Audio in basso a destra -->
    <v-card class="audio-player" elevation="8" rounded="xl">
      <div class="player-controls">
        <v-btn icon @click="togglePlay">
          <v-icon>{{ isPlaying ? 'mdi-pause' : 'mdi-play' }}</v-icon>
        </v-btn>
        <v-btn icon @click="stopAudio">
          <v-icon>mdi-stop</v-icon>
        </v-btn>
        <v-slider v-model="volume" min="0" max="1" step="0.1" dense hide-details @input="setVolume" />
      </div>
    </v-card>

    <!-- Audio nascosto -->
    <audio ref="audioPlayer" @ended="isPlaying = false">
      <source src="/audio/Sunset Vibes.mp3" type="audio/mpeg">
      Il tuo browser non supporta l'audio.
    </audio>

  </v-app>
</template>

<script>
import { marked } from 'marked';

export default {
  middleware: 'auth',
  data() {
    return {
      userInput: '',
      response: '',
      isPlaying: false,
      volume: 0.5, // Volume iniziale
      quote: "Lorem, ipsum dolor sit amet consectetur adipisicing elit",
      showQuote: true
    };
  },
  computed: {
    parsedResponse() {
      return marked.parse(this.response || '');
    },
  },
  methods: {
    async sendMessage() {
      if (!this.userInput) {
        this.response = 'Please enter a message.';
        return;
      }
      this.response = 'Loading...';
      try {
        const res = await fetch('https://openrouter.ai/api/v1/chat/completions', {
          method: 'POST',
          headers: {
            Authorization: 'Bearer ',
            'HTTP-Referer': 'https://www.sitename.com',
            'X-Title': 'SiteName',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            model: 'deepseek/deepseek-r1:free',
            messages: [{ role: 'user', content: this.userInput }],
          }),
        });
        const data = await res.json();
        this.response = data.choices?.[0]?.message?.content || 'No response received.';
      } catch (error) {
        this.response = 'Error: ' + error.message;
      }
    },

    togglePlay() {
      const audio = this.$refs.audioPlayer;
      if (this.isPlaying) {
        audio.pause();
      } else {
        audio.volume = 0; // Inizia silenzioso per il fade-in
        audio.play();
        this.fadeIn(audio); // Effetto fade-in
      }
      this.isPlaying = !this.isPlaying;
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
      }, 200);
    },
    hideQuote() {
      const quote = document.querySelector('.motivational-quote');
      quote.style.transition = 'opacity 0.5s';
      quote.style.opacity = 0;
      setTimeout(() => {
        this.showQuote = false;
      }, 500);
    },

    async generateMotivationalQuote() {
      try {
        const res = await fetch('https://openrouter.ai/api/v1/chat/completions', {
          method: 'POST',
          headers: {
            Authorization: 'Bearer ',
            'HTTP-Referer': 'https://www.sitename.com',
            'X-Title': 'SiteName',
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            model: 'deepseek/deepseek-r1:free',
            messages: [{ role: 'user', content: "Dammi una breve citazione motivazionale." }],
          }),
        });

        const data = await res.json();
        this.quote = data.choices?.[0]?.message?.content || "Il successo è la somma di piccoli sforzi ripetuti giorno dopo giorno.";
      } catch (error) {
        console.error("Errore nel generare la citazione:", error);
        this.quote = "Credi in te stesso e tutto sarà possibile.";
      }
    }

  },
  mounted() {
    if (sessionStorage.getItem("isPostBack")) {
      console.log("La pagina è stata ricaricata o rivisitata");
      
    } else {
      console.log("Primo caricamento della pagina");
      sessionStorage.setItem("isPostBack", "true");
      //this.generateMotivationalQuote();
    }
  }
};
</script>

<style scoped>
/* Posizionamento della citazione in basso a sinistra */
* {
  font-family: "Uto-Bold";
}

.motivational-quote {
  position: fixed;
  bottom: 20px;
  left: 100px;
  right: auto;
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

/* Aggiunta virgolette decorative alla citazione */
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

/* Aggiunto margine extra per distanziarla dalla navbar */
@media (max-width: 960px) {
  .motivational-quote {
    bottom: 80px;
    /* Sposta la citazione più in alto nei dispositivi più piccoli */
  }
}

/* Posizionamento del Player Audio in basso a destra */
.audio-player {
  position: fixed;
  bottom: 20px;
  right: 20px;
  width: 220px;
  padding: 10px;
  background: #34495e;
  color: white;
}

.player-controls {
  display: flex;
  align-items: center;
  gap: 10px;
}

#response {
  margin-top: 20px;
  padding: 10px;
  min-height: 50px;
  background: #f8f9fa;
  border-radius: 5px;
}

.v-card:hover {
  transform: translateY(-4px);
}
</style>

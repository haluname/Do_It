<template>
  <v-app>
    <NavBar />

    <v-main style="background-color: #fdf3e4;">
      <v-container class="py-8">
            <v-row>

         <v-col cols="12" md="8">
        <v-card elevation="6" rounded="lg" class="mx-auto pa-6" color="#fff2b8" max-width="600">
          <div class="text-center mb-6">
            <h2 class="notebook-title">Il mio foglio di note</h2>
          </div>
          <div class="note-paper">
            <textarea
              v-model="note"
              class="note-textarea"
              placeholder="Scrivi qui i tuoi appunti..."
              rows="10"
            ></textarea>
          </div>
        </v-card>
        </v-col>
         <!-- Colonna Livello -->
      <v-col cols="12" md="4">
        <v-card elevation="6" class="level-card" color="#e8f5e9">
          <div class="plant-container">
            <img 
              :src="currentPlantImage" 
              alt="Plant growth" 
              class="plant-gif"
            >
            <div class="level-badge">
              Livello {{ userLevel }}
            </div>
          </div>

          <v-card-text class="text-center">
            <v-progress-linear
              :value="levelProgress"
              height="25"
              color="light-green darken-2"
              striped
            >
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
      showQuote: true
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
    }
  },
  async mounted() {
      await this.$auth.fetchUser();
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
    }
  }
};
</script>


<style scoped>
* {
  font-family: "Uto-Bold";
}

.v-container {
    height: 100%;

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
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.experience-info {
  display: flex;
  justify-content: center;
  gap: 8px;
  margin-top: 12px;
}

/* Animazioni */
.plant-gif {
  transition: transform 0.3s ease;
}

.plant-gif:hover {
  transform: scale(1.05);
}

.note-paper {
  background: repeating-linear-gradient(
    to bottom,
    #fffde8,
    #fffde8 29px,
    #ffe9b7 30px
  );
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

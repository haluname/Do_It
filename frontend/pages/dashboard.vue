<template>
  <v-app>
    <NavBar />

    <v-main style="background-color: #fdf3e4;">
      <v-container class="py-8">
        <v-card elevation="6" rounded="lg" class="mx-auto pa-4" color="#fff2b8">
          <div class="text-center mb-6">
            <h2>Free ChatBot</h2>
            <v-text-field v-model="userInput" label="Enter your question" solo></v-text-field>
            <v-btn color="success" @click="sendMessage">Ask!</v-btn>
            <div id="response" v-html="parsedResponse"></div>
          </div>
        </v-card>
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
import { marked } from 'marked';

export default {
  data() {
    return {
      userInput: '',
      response: '',
      navItems: [
        { title: 'Home', icon: 'mdi-view-dashboard', route: '/dashboard' },
        { title: 'Goals', icon: 'mdi-folder-multiple', route: '/projects' },
        { title: 'Calendar', icon: 'mdi-calendar', route: '/calendar' },
        { title: 'TODAY', icon: 'mdi-star', route: '/today' },
      ],
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
            Authorization: 'Bearer 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',
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
  },
};
</script>

<style scoped>
#response {
  margin-top: 20px;
  padding: 10px;
  min-height: 50px;
  background: #f8f9fa;
  border-radius: 5px;
}
::v-deep(#response h3) {
  color: #333;
  font-size: 1.2em;
}
::v-deep(#response strong) {
  color: #d9534f;
}
::v-deep(#response ul) {
  padding-left: 20px; 
}
::v-deep(#response li) {
  margin-bottom: 5px;
}
*{
  font-family: 'Uto-Bold', sans-serif !important; 
}

.item-list{
  font-size: 14px !important;
}

@keyframes rainbow {
  0% { color: #ff0000; }
  20% { color: #ff9900; }
  40% { color: #ffff00; }
  60% { color: #33ff00; }
  80% { color: #0099ff; }
  100% { color: #cc00ff; }
}

.rainbow-text {
  animation: rainbow 10.5s linear infinite; 
  background-clip: text;
  -webkit-background-clip: text;
}

.today-item {
  font-size: 1.1em !important;
  font-weight: 900 !important;
  letter-spacing: 1px !important;
  text-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.today-item-mobile {
  font-size: 0.9rem !important;
  font-weight: 700 !important;
  margin-top: 2px !important;
}



/* Effetto hover per TODAY */


/* Mobile specific adjustments */
.mobile-nav .rainbow-text {
  animation: rainbow 2.5s linear infinite;
}

.mobile-nav .v-btn[to="/today"] .v-icon {
  color: #ffd166 !important;
  transform: scale(1.2);
}


.active-nav-item {
  background-color: #2c3e56 !important;
  color: #ffd166 !important;
  border-left: 4px solid #ffd166;
}


.primary--text {
  color: #34495e !important;
}

.secondary--text {
  color: #444444 !important;
}

.v-btn {
  letter-spacing: 1px;
  font-weight: bold;
  text-transform: uppercase;
}

.v-btn--active:before {
  background-color: transparent;
}

.v-card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.v-card:hover {
  transform: translateY(-4px);
}

.v-avatar {
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.mobile-nav {
  border-top: 3px solid #ffd166 !important;
  z-index: 1000;
}

.mobile-nav .v-btn {
  flex-direction: column;
  height: auto !important;
  padding: 8px 0 !important;
}

.mobile-nav .nav-label {
  font-size: 0.7rem;
  margin-top: 4px;
}

.mobile-card {
  border-radius: 16px !important;
  margin: 8px;
  padding: 16px !important;
}

/* Responsive adjustments */
@media (max-width: 960px) {
  .nav-drawer {
    display: none !important;
  }

  .v-main {
    padding-bottom: 56px !important; /* Altezza bottom navigation */
  }
}
</style>
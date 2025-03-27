<template>
  <v-app>
    <NavBar />

    <v-main style="background-color: #fdf3e4;">
      <v-container class="py-8">
        <v-card elevation="6" rounded="lg" class="mx-auto pa-4" color="#fff2b8">
          <div class="text-center mb-6">
            <h2>ChatBot</h2>
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
  middleware: 'auth',
  data() {
    return {
      userInput: '',
      response: '',
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
*{ font-family: 'Uto-Bold', sans-serif !important; }

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

.v-card {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.v-card:hover {
  transform: translateY(-4px);
}

@media (max-width: 960px) {
  .v-main {
    padding-bottom: 56px !important;
  }
}
</style>
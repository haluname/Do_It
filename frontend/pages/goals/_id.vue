<template>
  <v-app>
    <v-main style="background-color: #fdf3e4;">
      <v-container class="py-8">
        <!-- Loader -->
        <v-row v-if="loading" class="d-flex justify-center">
          <v-progress-circular indeterminate color="primary" />
        </v-row>

        <v-card v-else class="mx-auto" max-width="800" elevation="6">
          <v-card-title class="headline">
            {{ goal.title }}
            <v-chip :color="priorityColor(goal.priority)" class="ml-3" dark>
              {{ priorityLabel(goal.priority) }}
            </v-chip>
          </v-card-title>

          <v-card-subtitle>
            <v-icon left>mdi-calendar</v-icon>
            Scadenza: {{ formatDate(goal.exp) }}
          </v-card-subtitle>

          <v-card-text>
            <v-divider class="my-4"></v-divider>
            <div class="description-text">
              {{ goal.description }}
            </div>

            <v-divider class="my-4"></v-divider>

            
          </v-card-text>

          <v-card-actions>
            <v-btn color="primary" @click="$router.go(-1)">
              <v-icon left>mdi-arrow-left</v-icon>
              Torna indietro
            </v-btn>
          </v-card-actions>
        </v-card>
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
export default {
  middleware: 'auth',
  data() {
    return {
      goal: null,
      loading: true,
    }
  },
  async fetch() {
    try {
      const response = await this.$axios.get(
        `http://localhost:8000/api/goals/${this.$route.params.id}`
      )
      this.goal = response.data
    } catch (error) {
      console.error('Error fetching goal:', error)
      this.$nuxt.error({ statusCode: 404, message: 'Goal non trovato' })
    } finally {
      this.loading = false
    }
  },
  methods: {
    formatDate(date) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' }
      return new Date(date).toLocaleDateString('it-IT', options)
    },
    formatDateTime(date) {
      const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }
      return new Date(date).toLocaleDateString('it-IT', options)
    },
    priorityLabel(priority) {
      const labels = { 1: 'Alta', 2: 'Media', 3: 'Bassa' }
      return labels[priority] || 'Sconosciuta'
    },
    priorityColor(priority) {
      return { 1: 'red', 2: 'orange', 3: 'green' }[priority] || 'gray'
    }
  }
}
</script>

<style scoped>
.headline {
  font-family: "Uto-Bold", sans-serif !important;
  font-size: 1.8rem !important;
}

.description-text {
  font-size: 1.1rem;
  line-height: 1.6;
  white-space: pre-wrap;
}

.meta-info {
  font-size: 0.9rem;
  color: #666;
}

.v-chip {
  font-family: "Uto-Bold", sans-serif !important;
}
</style>
<template>
  <v-app>
    <NavBar />
    <v-main style="background-color: #fdf3e4;">
      <v-container class="py-8">

        <!-- Loader -->
        <v-row v-if="loading" class="d-flex justify-center">
          <v-progress-circular indeterminate color="primary" />
        </v-row>

        <!-- Quando ha finito di caricare -->
        <v-row v-else>
          <!-- Nessun goal -->
          <template v-if="goals.length === 0">
            <v-col cols="12" class="text-center">
              <v-alert type="info" color="amber lighten-4" border="left" elevation="2">
                Non ci sono goals.
              </v-alert>
            </v-col>
          </template>

          <!-- Lista goals -->
          <template v-else>
            <v-col v-for="goal in goals" :key="goal.id" cols="12" md="3">
              <NuxtLink :to="`/goals/${goal.id}`" style="text-decoration: none;">
                <v-card class="goal-card" :class="priorityClass(goal.priority)" elevation="6" hover>
                  <v-card-title class="goal-title">
                    {{ goal.title }}
                  </v-card-title>
                  <v-card-subtitle class="goal-priority">
                    <v-chip :color="priorityColor(goal.priority)" dark>
                      Priorità: {{ priorityLabel(goal.priority) }}
                    </v-chip>
                  </v-card-subtitle>
                  <v-card-text>
                    <p>{{ goal.description }}</p>
                    <p class="goal-expiry">
                      <v-icon color="red">mdi-calendar</v-icon>
                      Scadenza: {{ formatDate(goal.exp) }}
                    </p>
                  </v-card-text>
                </v-card>
              </NuxtLink>
            </v-col>

          </template>
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
      goals: [],
      loading: true, // Aggiungiamo lo stato di caricamento
    };
  },
  async mounted() {
    try {
      const res = await this.$axios.get('http://localhost:8000/api/goals');
      this.goals = res.data;
    } catch (error) {
      console.error('Error fetching goals:', error);
    } finally {
      this.loading = false; // Impostiamo lo stato di caricamento a false quando i dati sono stati recuperati
    }
  },
  methods: {
    formatDate(date) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' };
      return new Date(date).toLocaleDateString('it-IT', options);
    },
    priorityLabel(priority) {
      const labels = { 1: 'Alta', 2: 'Media', 3: 'Bassa' };
      return labels[priority] || 'Sconosciuta';
    },
    priorityColor(priority) {
      return { 1: 'red', 2: 'orange', 3: 'green' }[priority] || 'gray';
    },
    priorityClass(priority) {
      return { 1: 'high-priority', 2: 'medium-priority', 3: 'low-priority' }[priority] || '';
    },
  },
};
</script>

<style scoped>
* {
  font-family: "Uto-Bold", sans-serif !important;
}

.goal-card {
  border-radius: 12px;
  padding: 16px;
  transition: transform 0.2s ease-in-out;
}

.goal-card:hover {
  transform: scale(1.05);
}

.goal-title {
  font-size: 1.2rem;
  font-weight: bold;
}

.goal-priority {
  margin-bottom: 8px;
}

.goal-expiry {
  font-size: 0.9rem;
  font-weight: bold;
  color: #d32f2f;
}

.high-priority {
  border-left: 5px solid red;
}

.medium-priority {
  border-left: 5px solid orange;
}

.low-priority {
  border-left: 5px solid green;
}
</style>
<template>
  <v-card elevation="3" class="solution-card">
    <v-card-title class="d-flex justify-space-between align-start">
      <div>
        {{ solution.title }}
        <!-- Contatore Mi Piace più grande e prominente -->
        <v-chip 
          color="orange lighten-4" 
          class="like-count mr-2 px-3 py-1"
          medium
        >
          <v-icon left color="orange darken-2" size="24">mdi-thumb-up</v-icon>
          <span class="count-number">{{ solution.likes_count }}</span>
        </v-chip>
      </div>
   <v-btn 
          icon 
          :color="solution.liked_by_user ? 'orange darken-2' : 'grey'" 
          @click="toggleLike"
          class="like-btn"
          large
        >
          <v-icon size="32">mdi-thumb-up</v-icon>
        </v-btn>
    </v-card-title>

    <v-card-subtitle class="d-flex align-center">
      <v-avatar size="30" class="mr-2">
        <span class="text-uppercase">{{ solution.user.name.charAt(0) }}</span>
      </v-avatar>
      {{ solution.user.name }} - {{ formatDate(solution.created_at) }}
    </v-card-subtitle>

    <v-card-text>
      <div class="mb-4">
        <div class="font-weight-bold orange--text text--darken-2">Problema:</div>
        <div class="problem-content">{{ solution.problem }}</div>
      </div>
      <div>
        <div class="font-weight-bold green--text text--darken-2">Soluzione:</div>
        <div class="solution-content">{{ solution.solution }}</div>
      </div>
    </v-card-text>
  </v-card>
</template>

<script>
export default {
  props: {
    solution: {
      type: Object,
      required: true
    }
  },
  methods: {
    formatDate(dateString) {
      const options = { year: 'numeric', month: 'long', day: 'numeric' }
      return new Date(dateString).toLocaleDateString('it-IT', options)
    },
    toggleLike() {
      this.$emit('like-toggled', this.solution.id)
    }
  }
}
</script>

<style scoped>
.solution-card {
  border-left: 4px solid #ff9800;
  height: 100%;
  display: flex;
  flex-direction: column;
}

.problem-content, .solution-content {
  white-space: pre-line;
  padding: 12px;
  background-color: rgba(255, 243, 224, 0.5);
  border-radius: 8px;
  margin-top: 8px;
}

.solution-content {
  background-color: rgba(232, 245, 233, 0.5);
}

.v-card__text {
  flex-grow: 1;
  display: flex;
  flex-direction: column;
}

.like-btn {
  transition: all 0.3s ease;
  transform: scale(1);
}

.like-btn:hover {
  transform: scale(1.1);
}

.v-chip {
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>
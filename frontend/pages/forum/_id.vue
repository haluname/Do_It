<template>
  <v-app>
    <NavBarForum />
    
    <v-main style="background-color: #fdf3e4;">
      <v-container class="py-8">
        <!-- Breadcrumb -->
        <v-breadcrumbs :items="breadcrumbs" class="mb-4">
          <template v-slot:divider>
            <v-icon>mdi-chevron-right</v-icon>
          </template>
        </v-breadcrumbs>

        <!-- Thread principale -->
        <v-card elevation="6" color="#fff5e6" class="mb-6">
          <v-card-title class="text-h5 font-weight-bold orange--text text--darken-3">
            {{ thread.title }}
            <v-chip v-if="thread.pinned" x-small color="amber lighten-4" class="ml-2">
              <v-icon x-small left>mdi-pin</v-icon>
              In evidenza
            </v-chip>
          </v-card-title>
          
          <v-card-subtitle class="mt-2">
            <div class="d-flex align-center">
              <v-chip small :color="thread.category.color" dark class="mr-2">
                {{ thread.category.text }}
              </v-chip>
              <span class="caption grey--text mr-2">•</span>
              <span class="caption">Creato da {{ thread.author }} {{ formatDate(thread.date) }}</span>
              <v-spacer></v-spacer>
              <v-chip small class="mr-2" color="grey lighten-4">
                <v-icon x-small left>mdi-comment-outline</v-icon>
                {{ thread.replies }}
              </v-chip>
              <v-chip small color="grey lighten-4">
                <v-icon x-small left>mdi-eye</v-icon>
                {{ thread.views }}
              </v-chip>
            </div>
          </v-card-subtitle>

          <v-card-text class="pt-4">
            <div class="thread-content" v-html="thread.content"></div>
            
            <!-- Actions -->
            <div class="mt-6 d-flex">
              <v-btn v-if="canModerate" color="orange darken-3" dark x-small @click="togglePin">
                <v-icon left small>{{ thread.pinned ? 'mdi-pin-off' : 'mdi-pin' }}</v-icon>
                {{ thread.pinned ? 'Rimuovi evidenziazione' : 'Evidenzia' }}
              </v-btn>
              <v-btn v-if="canModerate" color="red darken-3" dark x-small class="ml-2" @click="toggleClose">
                <v-icon left small>{{ thread.closed ? 'mdi-lock-open' : 'mdi-lock' }}</v-icon>
                {{ thread.closed ? 'Riapri discussione' : 'Chiudi discussione' }}
              </v-btn>
            </div>
          </v-card-text>
        </v-card>

        <!-- Nuova risposta -->
        <v-card v-if="$auth.loggedIn && !thread.closed" elevation="3" class="mb-6">
          <v-card-title class="subtitle-1 font-weight-bold">
            Nuova Risposta
          </v-card-title>
          <v-card-text>
            <v-textarea v-model="newPostContent" label="Scrivi la tua risposta..." outlined rows="4" auto-grow></v-textarea>
            <v-btn color="orange darken-3" dark @click="submitPost">Invia Risposta</v-btn>
          </v-card-text>
        </v-card>

        <!-- Lista risposte -->
        <div v-for="post in posts" :key="post.id" class="mb-4">
          <v-card elevation="3">
            <v-card-text>
              <div class="d-flex">
                <v-avatar size="40" color="orange" class="mr-4">
                  <span class="white-text">{{ post.authorInitials }}</span>
                </v-avatar>
                <div class="flex-grow-1">
                  <div class="d-flex align-center">
                    <strong>{{ post.author }}</strong>
                    <span class="caption grey--text ml-2">{{ formatDate(post.date) }}</span>
                  </div>
                  <div class="mt-2" v-html="post.content"></div>
                  
                  <div class="mt-3 d-flex align-center">
                    <v-btn icon x-small @click="likePost(post)">
                      <v-icon :color="post.likedByUser ? 'red' : 'grey'">mdi-heart</v-icon>
                    </v-btn>
                    <span class="caption mr-4">{{ post.likes }}</span>
                    
                    <v-btn icon x-small @click="toggleReply(post)">
                      <v-icon>mdi-reply</v-icon>
                    </v-btn>
                    <span class="caption">Rispondi</span>
                  </div>

                  <!-- Form risposta annidata -->
                  <div v-if="post.showReplyForm" class="mt-3">
                    <v-textarea v-model="post.replyContent" label="Scrivi una risposta..." outlined rows="2" auto-grow></v-textarea>
                    <v-btn color="orange darken-3" dark small @click="submitReply(post)">Invia Risposta</v-btn>
                  </div>

                  <!-- Risposte annidate -->
                  <div v-if="post.replies && post.replies.length > 0" class="mt-4 ml-8">
                    <div v-for="reply in post.replies" :key="reply.id" class="mb-3">
                      <v-card elevation="2" color="grey lighten-4">
                        <v-card-text class="pa-3">
                          <div class="d-flex">
                            <v-avatar size="30" color="orange" class="mr-3">
                              <span class="white-text">{{ reply.authorInitials }}</span>
                            </v-avatar>
                            <div>
                              <strong>{{ reply.author }}</strong>
                              <span class="caption grey--text ml-2">{{ formatDate(reply.date) }}</span>
                              <div class="mt-1" v-html="reply.content"></div>
                            </div>
                          </div>
                        </v-card-text>
                      </v-card>
                    </div>
                  </div>
                </div>
              </div>
            </v-card-text>
          </v-card>
        </div>

        <!-- Paginazione -->
        <v-pagination v-model="page" :length="totalPages" circle color="orange darken-2" class="mt-6"></v-pagination>
      </v-container>
    </v-main>
  </v-app>
</template>

<script>
export default {
  data() {
    return {
      thread: {
        id: null,
        title: '',
        content: '',
        author: '',
        date: '',
        category: { text: '', color: '' },
        pinned: false,
        closed: false,
        replies: 0,
        views: 0,
        userId: null
      },
      posts: [],
      newPostContent: '',
      page: 1,
      totalPages: 1,
      loading: false
    }
  },
  computed: {
    breadcrumbs() {
      return [
        {
          text: 'Community',
          disabled: false,
          href: '/forum'
        },
        {
          text: this.thread.title || 'Discussione',
          disabled: true
        }
      ]
    },
    canModerate() {
      return this.$auth.loggedIn && (this.$auth.user.isAdmin || this.thread.userId === this.$auth.user.id)
    }
  },
  methods: {
    async loadThread() {
      this.loading = true;
      try {
        const response = await this.$axios.get(`/api/threads/${this.$route.params.id}`);
        const data = response.data;
        
        this.thread = {
          id: data.id,
          title: data.title,
          content: data.content,
          author: data.user.name,
          date: data.created_at,
          category: {
            text: data.category.name,
            color: data.category.color
          },
          pinned: data.pinned,
          closed: data.closed,
          replies: data.replies_count,
          views: data.views_count,
          userId: data.user_id
        };
        
        // Aggiorna titolo pagina
        document.title = `${this.thread.title} - Community`;
      } catch (error) {
        this.$toast.error('Errore nel caricamento della discussione');
        this.$router.push('/forum');
      } finally {
        this.loading = false;
      }
    },
    async loadPosts() {
      this.loading = true;
      try {
        const response = await this.$axios.get(`/api/threads/${this.thread.id}/posts`, {
          params: { page: this.page }
        });
        
        this.posts = response.data.data.map(post => ({
          id: post.id,
          content: post.content,
          author: post.user.name,
          authorInitials: post.user.name.charAt(0),
          date: post.created_at,
          likes: post.likes_count,
          likedByUser: post.liked_by_user,
          userId: post.user_id,
          replies: []
        }));
        
        this.totalPages = Math.ceil(response.data.total / response.data.per_page);
      } catch (error) {
        this.$toast.error('Errore nel caricamento delle risposte');
      } finally {
        this.loading = false;
      }
    },
    async submitPost() {
      if (!this.newPostContent.trim()) return;
      
      try {
        const response = await this.$axios.post('/api/posts', {
          thread_id: this.thread.id,
          content: this.newPostContent
        }, {
          headers: {
            Authorization: `Bearer ${this.$auth.strategy.token.get()}`
          }
        });
        
        // Aggiungi la nuova risposta alla lista
        this.posts.push({
          id: response.data.id,
          content: response.data.content,
          author: this.$auth.user.name,
          authorInitials: this.$auth.user.name.charAt(0),
          date: new Date().toISOString(),
          likes: 0,
          likedByUser: false,
          replies: []
        });
        
        // Resetta il form
        this.newPostContent = '';
        
        // Incrementa contatore risposte
        this.thread.replies++;
        
        this.$toast.success('Risposta inviata con successo!');
      } catch (error) {
        this.$toast.error('Errore nell\'invio della risposta');
      }
    },
    async togglePin() {
      try {
        const response = await this.$axios.put(`/api/threads/${this.thread.id}/pin`, {}, {
          headers: {
            Authorization: `Bearer ${this.$auth.strategy.token.get()}`
          }
        });
        
        this.thread.pinned = response.data.pinned;
        this.$toast.success(this.thread.pinned ? 'Discussione evidenziata' : 'Evidenziazione rimossa');
      } catch (error) {
        this.$toast.error('Errore nell\'aggiornamento della discussione');
      }
    },
    async toggleClose() {
      try {
        const response = await this.$axios.put(`/api/threads/${this.thread.id}/close`, {}, {
          headers: {
            Authorization: `Bearer ${this.$auth.strategy.token.get()}`
          }
        });
        
        this.thread.closed = response.data.closed;
        this.$toast.success(this.thread.closed ? 'Discussione chiusa' : 'Discussione riaperta');
      } catch (error) {
        this.$toast.error('Errore nell\'aggiornamento della discussione');
      }
    },
    async likePost(post) {
      if (post.likedByUser) {
        // Dislike
        await this.$axios.delete(`/api/likes`, {
          data: {
            likeable_type: 'posts',
            likeable_id: post.id
          },
          headers: {
            Authorization: `Bearer ${this.$auth.strategy.token.get()}`
          }
        });
        post.likes--;
        post.likedByUser = false;
      } else {
        // Like
        await this.$axios.post('/api/likes', {
          likeable_type: 'posts',
          likeable_id: post.id
        }, {
          headers: {
            Authorization: `Bearer ${this.$auth.strategy.token.get()}`
          }
        });
        post.likes++;
        post.likedByUser = true;
      }
    },
    toggleReply(post) {
      this.$set(post, 'showReplyForm', !post.showReplyForm);
      this.$set(post, 'replyContent', '');
    },
    async submitReply(post) {
      if (!post.replyContent.trim()) return;
      
      try {
        const response = await this.$axios.post('/api/posts', {
          thread_id: this.thread.id,
          parent_id: post.id,
          content: post.replyContent
        }, {
          headers: {
            Authorization: `Bearer ${this.$auth.strategy.token.get()}`
          }
        });
        
        // Resetta il form
        post.replyContent = '';
        
        // Aggiungi la risposta al post padre
        if (!post.replies) post.replies = [];
        post.replies.push({
          id: response.data.id,
          content: response.data.content,
          author: this.$auth.user.name,
          authorInitials: this.$auth.user.name.charAt(0),
          date: new Date().toISOString()
        });
        
        this.$toast.success('Risposta inviata con successo!');
      } catch (error) {
        this.$toast.error('Errore nell\'invio della risposta');
      }
    },
    formatDate(dateString) {
      const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }
      return new Date(dateString).toLocaleDateString('it-IT', options)
    }
  },
  watch: {
    page() {
      this.loadPosts();
    }
  },
  async mounted() {
    await this.loadThread();
    await this.loadPosts();
  }
}
</script>

<style scoped>
.white-text {
  color: white !important;
}
.thread-content {
  line-height: 1.8;
  white-space: pre-line;
}
</style>
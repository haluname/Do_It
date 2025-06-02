<template>
  <v-app>
    <NavBarForum />
    <v-main style="background-color: #fdf3e4; max-height: 100vh; overflow-y: auto;">
      <v-container class="py-8">
        <!-- Breadcrumbs -->
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
            <!-- Badge CHIUSO aggiunto qui -->
            <v-chip v-if="thread.closed" x-small color="red lighten-5" class="ml-2">
              <v-icon x-small left>mdi-lock</v-icon>
              Chiuso
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
              <v-btn v-if="isThreadOwner" color="orange darken-3" dark x-small class="ml-2" @click="toggleClose">
                <v-icon left small>{{ thread.closed ? 'mdi-lock-open' : 'mdi-lock' }}</v-icon>
                {{ thread.closed ? 'Riapri discussione' : 'Chiudi discussione' }}
              </v-btn>

              <!-- Pulsante pin visibile solo a mod/admin -->

            </div>
          </v-card-text>
        </v-card>

        <!-- Messaggio thread chiuso -->
        <v-alert v-if="thread.closed" type="info" class="mb-6">
          Questa discussione è stata chiusa dal proprietario. Non è più possibile rispondere.
        </v-alert>

        <!-- Nuova risposta - nascosta se thread chiuso -->
        <v-card v-if="$auth.loggedIn && !thread.closed" elevation="3" class="mb-6">
          <v-card-title class="subtitle-1 font-weight-bold">
            Nuova Risposta
          </v-card-title>
          <v-card-text>
            <v-textarea v-model="newPostContent" label="Scrivi la tua risposta..." outlined rows="4"
              auto-grow></v-textarea>
            <v-btn color="orange darken-3" dark @click="submitPost">Invia Risposta</v-btn>
          </v-card-text>
        </v-card>

        <!-- Lista risposte -->
        <div v-for="post in posts" :key="post.id" class="mb-4">
          <PostItem :post="post" :thread="thread" :depth="0" @reply-submitted="handleReplySubmitted" />
        </div>

        <v-pagination v-model="page" :length="totalPages" circle color="orange darken-2" class="mt-6"></v-pagination>
      </v-container>
    </v-main>
  </v-app>
</template>
<script>
export default {
  middleware: 'auth',
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
    },
    isThreadOwner() {
      return this.$auth.loggedIn && this.$auth.user.id === this.thread.userId;
    },

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
          userId: data.user.id // SALVA L'ID DEL PROPRIETARIO
        };
        console.log(this.thread);

        document.title = `${this.thread.title} - Community`;
      } catch (error) {
        this.$toast.error('Errore nel caricamento della discussione');
        this.$router.push('/forum');
      } finally {
        this.loading = false;
      }
    },

    async toggleClose() {
      try {
        const response = await this.$axios.put(
          `http://localhost:8000/api/threads/${this.thread.id}/close`,
          {},
          {
            headers: {
              Authorization: `Bearer ${this.$auth.strategy.token.get()}`
            }
          }
        );

        this.thread.closed = response.data.closed;
        this.$toast.success(this.thread.closed ?
          'Discussione chiusa con successo' :
          'Discussione riaperta con successo'
        );
      } catch (error) {
        this.$toast.error('Errore nell\'aggiornamento della discussione');
      }
    },
    async loadPosts() {
      this.loading = true;
      try {
        const response = await this.$axios.get(`http://localhost:8000/api/threads/${this.thread.id}/posts`);

        // Mappatura corretta delle risposte annidate
        this.posts = response.data.data.map(post => {
          return this.formatPostWithReplies(post);
        });

        this.totalPages = response.data.last_page;
      } catch (error) {
        console.error('Dettagli errore:', error.response?.data || error.message);
        this.$toast.error('Errore nel caricamento delle risposte');
      } finally {
        this.loading = false;
      }
    },
    async submitPost() {
      if (!this.newPostContent.trim()) return;

      try {
        const response = await this.$axios.post('http://localhost:8000/api/posts', {
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


        // Dopo aver inviato la risposta
        if (this.thread.userId !== this.$auth.user.id) {
          try {
            await this.$axios.post('/api/notifications', {
              user_id: this.thread.userId,
              type: 'thread_reply',
              notifiable_id: this.thread.id,
              notifiable_type: 'App\\Models\\Thread',
              message: `${this.$auth.user.name} ha risposto alla tua discussione "${this.thread.title}"`,
            }, {
              headers: { Authorization: `Bearer ${this.$auth.strategy.token.get()}` }
            });
          } catch (notificationError) {
            console.error('Errore invio notifica:', notificationError);

          }
        }

        this.$toast.success('Risposta inviata con successo!');
      } catch (error) {
        this.$toast.error('Errore nell\'invio della risposta');
      }
    },
    async togglePin() {
      try {
        const response = await this.$axios.put(`http://localhost:8000/api/threads/${this.thread.id}/pin`, {}, {
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
        const response = await this.$axios.put(`http://localhost:8000/api/threads/${this.thread.id}/close`, {}, {
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
        await this.$axios.delete(`http://localhost:8000/api/likes`, {
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
        await this.$axios.post('http://localhost:8000/api/likes', {
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
        const response = await this.$axios.post('http://localhost:8000/api/posts', {
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
    },

    formatPostWithReplies(post) {
      const replies = post.replies ? post.replies.map(reply => {
        return this.formatPostWithReplies(reply);
      }) : [];

      return {
        id: post.id,
        userId: post.user_id || (post.user ? post.user.id : null),
        content: post.content,
        author: post.user?.name || 'Utente sconosciuto',
        authorInitials: post.user?.name?.charAt(0) || '?',
        date: post.created_at,
        likes: post.likes_count || 0,
        likedByUser: post.liked_by_user || false,
        replies: replies,
        showReplyForm: false,
        replyContent: ''
      };
    },

    async handleReplySubmitted({ parentId, content }) {
      try {
        const response = await this.$axios.post('http://localhost:8000/api/posts', {
          thread_id: this.thread.id,
          parent_id: parentId,
          content: content
        }, {
          headers: {
            Authorization: `Bearer ${this.$auth.strategy.token.get()}`
          }
        });

        // Aggiungi la risposta ricorsivamente
        this.addReplyToPost(parentId, {
          id: response.data.id,
          content: response.data.content,
          author: this.$auth.user.name,
          authorInitials: this.$auth.user.name.charAt(0),
          date: new Date().toISOString(),
          likes: 0,
          likedByUser: false,
          replies: [],
          userId: this.$auth.user.id
        });

        const parentPost = this.findPostById(parentId, this.posts);

        if (parentPost && parentPost.userId !== this.$auth.user.id) {
          await this.$axios.post('/api/notifications', {
            user_id: parentPost.userId,
            type: 'post_reply',
            notifiable_id: parentId,
            notifiable_type: 'App\\Models\\Post',
            message: `${this.$auth.user.name} ha risposto al tuo messaggio`,
            data: { thread_id: this.thread.id } // Aggiungi questa linea
          }, {
            headers: { Authorization: `Bearer ${this.$auth.strategy.token.get()}` }
          });
        }

        this.$toast.success('Risposta inviata con successo!');
      } catch (error) {
        console.error('Errore nella risposta:', error);
        this.$toast.error('Errore nell\'invio della risposta');
      }
    },

    addReplyToPost(parentId, newReply) {
      const findAndAdd = (postList) => {
        for (const post of postList) {
          if (post.id === parentId) {
            if (!post.replies) this.$set(post, 'replies', []);
            post.replies.push(newReply);
            return true;
          }
          if (post.replies && post.replies.length > 0) {
            if (findAndAdd(post.replies)) return true;
          }
        }
        return false;
      };

      findAndAdd(this.posts);
    },
    findPostById(id, postList) {
      for (const post of postList) {
        if (post.id === id) return post;
        if (post.replies?.length) {
          const found = this.findPostById(id, post.replies);
          if (found) return found;
        }
      }
      return null;
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
* {
  font-family: "Uto-Bold", sans-serif;
}

.white-text {
  color: white !important;
}

.thread-content {
  line-height: 1.8;
  white-space: pre-line;
}
</style>
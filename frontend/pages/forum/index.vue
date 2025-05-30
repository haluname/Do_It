<template>
    <v-app>
        <NavBarForum />

        <v-main style="background-color: #fdf3e4;">
            <v-container class="py-8">
                <v-row>
                    <!-- Colonna principale -->
                    <v-col cols="12" md="8">
                        <v-card elevation="6" class="mb-6" color="#fff5e6">
                            <v-toolbar flat color="orange lighten-5">
                                <v-toolbar-title class="font-weight-bold orange--text text--darken-3">
                                    <v-icon left color="orange darken-2">mdi-forum</v-icon>
                                    Community di Supporto
                                </v-toolbar-title>
                                <v-spacer></v-spacer>
                                <v-btn color="orange darken-3" dark @click="openNewThreadDialog">
                                    <v-icon left>mdi-plus-circle</v-icon>
                                    Nuova Discussione
                                </v-btn>
                            </v-toolbar>

                            <v-card-text>
                                <v-text-field v-model="searchQuery" prepend-inner-icon="mdi-magnify"
                                    label="Cerca discussioni..." outlined dense hide-details class="mb-4"
                                    @keyup.enter="searchThreads"></v-text-field>

                                <v-btn color="orange darken-3" dark @click="searchThreads">
                                    <v-icon left>mdi-magnify</v-icon>
                                    Cerca
                                </v-btn>

                                <v-chip-group v-model="filter" column multiple>
                                    <v-chip v-for="category in categories" :key="category.value" filter
                                        :color="category.color" outlined>
                                        {{ category.text }}
                                    </v-chip>
                                </v-chip-group>
                            </v-card-text>
                        </v-card>


                        <v-card v-for="thread in threads" :key="thread.id" elevation="3" class="mb-4 thread-card">
                            <v-card-text class="py-3">
                                <v-row align="center">
                                    <!-- Colonna sinistra: Titolo e info -->
                                    <v-col cols="12" md="8">
                                        <div class="d-flex align-center">
                                            <v-icon small left :color="thread.category.color">{{ thread.category.icon
                                                }}</v-icon>
                                            <router-link :to="`/forum/${thread.id}`"
                                                class="thread-title font-weight-medium">
                                                {{ thread.title }}
                                            </router-link>
                                            <v-chip v-if="thread.isPinned" x-small color="amber lighten-4" class="ml-2">
                                                <v-icon x-small left>mdi-pin</v-icon>
                                                In evidenza
                                            </v-chip>
                                        </div>
                                        <div class="thread-meta mt-1">
                                            <span class="caption orange--text text--darken-2">{{ thread.author }}</span>
                                            <span class="caption grey--text mx-2">•</span>
                                            <span class="caption grey--text">{{ formatDate(thread.date) }}</span>
                                        </div>
                                    </v-col>

                                    <!-- Colonna destra: Azioni (like, commenti e cancella) -->
                                    <v-col cols="12" md="4">

                                        <div class="d-flex justify-end">
                                            <v-btn v-if="$auth.user && $auth.user.forum_role === 'mod'" icon small
                                                color="amber" @click="togglePin(thread)" class="ml-3">
                                                <v-icon>{{ thread.isPinned ? 'mdi-pin' : 'mdi-pin-outline' }}</v-icon>
                                            </v-btn>
                                            <!-- Conteggio commenti e like -->
                                            <v-chip small class="mr-2" color="grey lighten-4">
                                                <v-icon x-small left>mdi-comment-outline</v-icon>
                                                {{ thread.replies }}
                                            </v-chip>
                                            <v-chip small color="grey lighten-4">
                                                <v-icon x-small left>mdi-heart-outline</v-icon>
                                                {{ thread.likes }}
                                            </v-chip>

                                            <!-- Pulsante di cancella (visibile solo ai moderatori) -->
                                            <v-btn v-if="$auth.user && $auth.user.forum_role === 'mod'" icon small
                                                color="error" @click="deleteThread(thread.id)" class="ml-3">
                                                <v-icon>mdi-delete</v-icon>
                                            </v-btn>

                                        </div>
                                    </v-col>
                                </v-row>
                            </v-card-text>
                        </v-card>

                        <v-pagination v-model="page" :length="totalPages" circle color="orange darken-2" class="mt-6"
                            :disabled="loading"></v-pagination>
                    </v-col>

                    <!-- Sidebar -->
                    <v-col cols="12" md="4">
                        <v-card elevation="6" class="mb-4" color="#e8f5e9">
                            <v-card-title class="subtitle-1 font-weight-bold">
                                <v-icon left color="green darken-2">mdi-chart-line</v-icon>
                                Statistiche Community
                            </v-card-title>
                            <v-card-text>
                                <div class="d-flex justify-space-between mb-2">
                                    <span class="caption">Discussioni totali</span>
                                    <span class="font-weight-medium">1,234</span>
                                </div>
                                <div class="d-flex justify-space-between mb-2">
                                    <span class="caption">Utenti registrati</span>
                                    <span class="font-weight-medium">589</span>
                                </div>
                                <div class="d-flex justify-space-between">
                                    <span class="caption">Online ora</span>
                                    <span class="font-weight-medium">126</span>
                                </div>
                            </v-card-text>
                        </v-card>

                        <v-card elevation="6" class="mb-4" color="#fff3e0">
                            <v-card-title class="subtitle-1 font-weight-bold">
                                <v-icon left color="orange darken-2">mdi-account-group</v-icon>
                                Utenti più attivi
                            </v-card-title>
                            <v-list dense>
                                <v-list-item v-for="user in topUsers" :key="user.id">

                                    <v-list-item-content>
                                        <v-list-item-title class="caption font-weight-medium">
                                            {{ user.name }}
                                        </v-list-item-title>
                                        <v-list-item-subtitle class="caption">
                                            {{ user.points }} XP
                                        </v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                            </v-list>
                        </v-card>

                        <v-card elevation="6" color="#f0f4ff">
                            <v-card-title class="subtitle-1 font-weight-bold">
                                <v-icon left color="blue darken-2">mdi-tag</v-icon>
                                Categorie Popolari
                            </v-card-title>
                            <v-card-text>
                                <v-chip v-for="category in popularCategories" :key="category.name"
                                    :color="category.color" outlined class="ma-1" label>
                                    {{ category.name }}
                                    <v-avatar right class="ml-2" size="24" color="white">
                                        {{ category.count }}
                                    </v-avatar>
                                </v-chip>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
        </v-main>

        <!-- Dialog nuova discussione -->
        <v-dialog v-model="newThreadDialog" max-width="600" persistent>
            <v-card>
                <v-toolbar color="orange lighten-5" flat>
                    <v-toolbar-title class="orange--text text--darken-3">
                        Nuova Discussione
                    </v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn icon @click="closeDialog" :disabled="loading">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-toolbar>

                <v-card-text class="pt-4">
                    <v-select v-model="newThread.category_id" :items="categories" label="Categoria" outlined
                        item-text="text" item-value="id" :loading="loadingCategories" :error-messages="categoryErrors"
                        @change="resetCategoryError"></v-select>

                    <v-text-field v-model="newThread.title" label="Titolo discussione" outlined counter="120"
                        :error-messages="titleErrors" @input="resetTitleError"></v-text-field>

                    <v-textarea v-model="newThread.content" label="Contenuto" outlined rows="6" counter="1000" auto-grow
                        :error-messages="contentErrors" @input="resetContentError"></v-textarea>
                </v-card-text>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="orange darken-3" dark @click="createThread" :loading="loading" :disabled="loading">
                        <template v-slot:loader>
                            <span class="custom-loader">
                                <v-icon light>mdi-cached</v-icon>
                            </span>
                        </template>
                        Pubblica Discussione
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>
    </v-app>
</template>

<script>
export default {
    data() {
        return {
            perPage: 5,
            totalItems: 0,
            search: '',
            loading: false,
            loadingCategories: false,
            newThreadDialog: false,
            categories: [],
            threads: [],
            searchQuery: '',
            errors: {},
            newThread: {
                title: '',
                content: '',
                category_id: null
            },

            filter: [],
            page: 1,
            newThreadDialog: false,


            topUsers: [
                {
                    id: 1,
                    name: 'Proattivo87',
                    avatar: '/avatars/1.png',
                    points: 2450
                },
                // Altri utenti...
            ],
            popularCategories: [
                { name: 'Tecniche di Studio', count: 189, color: 'orange' },
                // Altre categorie...
            ],
            newThread: {
                category: '',
                title: '',
                content: ''
            }
        }
    },
    computed: {

        categoryErrors() {
            return this.errors.category_id || []
        },
        titleErrors() {
            return this.errors.title || []
        },
        contentErrors() {
            return this.errors.content || []
        },
        totalPages() {
            return Math.ceil(this.totalItems / this.perPage);
        }
    },
    methods: {
        formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric' }
            return new Date(dateString).toLocaleDateString('it-IT', options)
        },

        async loadCategories() {
            try {
                this.loadingCategories = true;
                const response = await this.$axios.get('http://localhost:8000/api/categories');

                console.log('Risposta API:', response.data);

                this.categories = response.data.data.map(c => ({
                    id: c.id,
                    text: c.name,
                    value: c.id,
                    color: c.color
                }));

            } catch (error) {
                console.error('Errore API:', error.response?.data);
                this.$toast.error('Errore nel caricamento delle categorie');
            } finally {
                this.loadingCategories = false;
            }
        },

        openNewThreadDialog() {
            if (this.$auth.loggedIn) {
                this.newThreadDialog = true
            } else {
                this.$router.push('/login')
            }
        },

        closeDialog() {
            this.newThreadDialog = false
            this.resetForm()
        },

        resetForm() {
            this.newThread = {
                title: '',
                content: '',
                category_id: null
            }
            this.errors = {}
        },

        resetCategoryError() {
            delete this.errors.category_id
        },

        resetTitleError() {
            delete this.errors.title
        },

        resetContentError() {
            delete this.errors.content
        },

        async createThread() {
            this.loading = true;
            try {
                const response = await this.$axios.post('http://localhost:8000/api/threads', this.newThread, {
                    headers: {
                        Authorization: `Bearer ${this.$auth.strategy.token.get()}`
                    }
                });

                this.$toast.success('Discussione creata con successo!', {
                    icon: 'mdi-check-circle',
                    duration: 3000
                });

                const threadId = response.data.data.id;
                this.$router.push(`/forum/${threadId}`);

                this.closeDialog();
            } catch (error) {
                console.error('Dettaglio errore:', error.response?.data || error.message);
                if (error.response?.status === 422) {
                    this.errors = error.response.data.errors;
                } else if (error.response?.status === 401) {
                    this.$auth.logout();
                    this.$router.push('/login');
                }
                this.$toast.error(error.response?.data?.message || 'Errore durante la creazione della discussione');
            } finally {
                this.loading = false;
            }
        }
        ,
        async searchThreads() {
            this.loading = true;
            try {
                const response = await this.$axios.get('/api/threads/search', {
                    params: {
                        query: this.searchQuery,
                        page: this.page
                    }
                });

                this.threads = response.data.data.map(thread => ({
                    id: thread.id,
                    title: thread.title,
                    content: thread.content,
                    views_count: thread.views_count,
                    replies: thread.replies_count,
                    likes: thread.likes_count,
                    isPinned: thread.pinned,
                    date: thread.created_at,
                    category: {
                        text: thread.category.name,
                        color: thread.category.color
                    },
                    author: thread.user.name
                }));

                this.totalItems = response.data.meta.total;
                this.page = response.data.meta.current_page;
            } catch (error) {
                console.error('Errore nella ricerca:', error);
                this.$toast.error('Errore nella ricerca delle discussioni');
            } finally {
                this.loading = false;
            }
        },

        async deleteThread(threadId) {
            if (!confirm("Sei sicuro di voler eliminare questo thread?")) return;

            try {
                await this.$axios.delete(`http://localhost:8000/api/threads/${threadId}`);
                this.$toast.success("Thread eliminato con successo!");
                // Rimuovi il thread dalla lista
                this.threads = this.threads.filter(t => t.id !== threadId);
            } catch (error) {
                console.error("Errore durante l'eliminazione:", error);
                this.$toast.error("Impossibile eliminare il thread.");
            }
        },

        async loadThreads() {
            this.loading = true;
            try {
                const isSearch = this.searchQuery.trim() !== '';
                const response = isSearch
                    ? await this.$axios.get('/api/threads/search', {
                        params: {
                            query: this.searchQuery,
                            page: this.page
                        }
                    })
                    : await this.$axios.get('/api/threads', {
                        params: { page: this.page }
                    });

                this.threads = response.data.data.map(thread => ({
                    id: thread.id,
                    title: thread.title,
                    content: thread.content,
                    views_count: thread.views_count,
                    replies: thread.replies_count,
                    likes: thread.likes_count,
                    isPinned: thread.pinned,
                    date: thread.created_at,
                    category: {
                        text: thread.category.name,
                        color: thread.category.color
                    },
                    author: thread.user.name
                }));

                this.totalItems = response.data.meta.total;
                this.page = response.data.meta.current_page;
            } catch (error) {
                console.error('Errore caricamento thread:', error);
                this.$toast.error('Errore nel caricamento delle discussioni');
            } finally {
                this.loading = false;
            }
        },
        async togglePin(thread) {
            try {
                const response = await this.$axios.put(
                    `http://localhost:8000/api/threads/${thread.id}/pin`
                );

                // Aggiorna il thread nella lista
                const updatedThread = response.data.data; 
                this.threads = this.threads.map(t =>
                    t.id === thread.id ? { ...t, isPinned: updatedThread.pinned } : t
                );
            } catch (error) {
                console.error("Errore durante il pin:", error);
                this.$toast.error("Impossibile modificare lo stato del pin.");
            }
        }

    },
    async mounted() {
        await this.loadCategories();
        await this.loadThreads();
    },

    watch: {
        page() {
            this.loadThreads();
        }
    },
}
</script>

<style scoped>
* {
    font-family: "Uto-Bold", sans-serif !important;
}

.thread-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border-left: 4px solid transparent;
}

.thread-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
    border-left-color: #ffa726;
}

.highlighted-thread {
    background: #fff3e0 !important;
    border-left-color: #ff9100;
}

.thread-title {
    color: #6d4c41;
    text-decoration: none;
}

.thread-title:hover {
    color: #ff9100;
    text-decoration: underline;
}

.thread-meta {
    font-size: 0.8rem;
}

.v-chip--filter.v-chip--active {
    background: #fff3e0 !important;
    border-color: #ffa726 !important;
}

.v-pagination__item--active {
    background-color: #ffa726 !important;
    color: white !important;
}

.custom-loader {
    animation: loader 1s infinite;
    display: flex;
}

@keyframes loader {
    from {
        transform: rotate(0);
    }

    to {
        transform: rotate(360deg);
    }
}
</style>
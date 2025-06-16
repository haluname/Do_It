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

                                <v-chip-group column>
                                    <v-chip v-for="category in categories" :key="category.id" :color="category.color"
                                        outlined :input-value="selectedCategory === category.id"
                                        @click="selectCategory(category.id)">
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
                                            <v-chip v-if="thread.closed" x-small color="red lighten-5" class="ml-2">
                                                <!-- Nuovo chip -->
                                                <v-icon x-small left>mdi-lock</v-icon>
                                                Chiuso
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
                                    <span class="font-weight-medium">{{ totalThreads }}</span>
                                </div>
                                <div class="d-flex justify-space-between mb-2">
                                    <span class="caption">Utenti registrati</span>
                                    <span class="font-weight-medium">{{ totalUsers }}</span>
                                </div>
                            </v-card-text>
                        </v-card>

                        <v-card elevation="6" class="mb-4" color="#fff3e0">
                            <v-card-title class="subtitle-1 font-weight-bold">
                                <v-icon left color="orange darken-2">mdi-account-group</v-icon>
                                Utenti più attivi su Do!t
                            </v-card-title>
                            <v-list dense>
                                <v-list-item v-for="user in topUsers" :key="user.name">
                                    <v-list-item-content>
                                        <v-list-item-title class="caption font-weight-medium">
                                            {{ user.name }} (Lv. {{ user.level }})
                                        </v-list-item-title>
                                        <v-list-item-subtitle class="caption">
                                            {{ user.experience }} XP
                                        </v-list-item-subtitle>
                                    </v-list-item-content>
                                </v-list-item>
                            </v-list>
                        </v-card>
                        <v-card elevation="6" color="#e3f2fd" class="mb-4">
                            <v-card-title class="subtitle-1 font-weight-bold">
                                <v-icon left color="blue darken-2">mdi-account-box</v-icon>
                                Hai dei dubbi o hai bisogno di aiuto?
                            </v-card-title>
                            <v-card-text>
                                <v-btn block color="blue darken-2" dark @click="$router.push('/forum/contact')"
                                    class="text-capitalize">
                                    <v-icon left>mdi-email</v-icon>
                                    Contatta il Team
                                </v-btn>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
            <span class="text-caption grey--text text--darken-1 mx-auto copyright"
                style="position: fixed; bottom: 10px; left: 50%; transform: translateX(-50%); font-size: 0.8rem; color: #6d4c41;">
                © {{ new Date().getFullYear() }} Do!t. Tutti i diritti riservati.
            </span>
            <v-chip-group column>
                <!-- Categorie esistenti -->

                <!-- Pulsante nuova categoria (solo per mod) -->
                <v-chip v-if="$auth.user && $auth.user.forum_role === 'mod'" color="green" text-color="white"
                    @click="openNewCategoryDialog">
                    <v-icon left>mdi-plus</v-icon>
                    Nuova
                </v-chip>
            </v-chip-group>
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

        <v-dialog v-model="newCategoryDialog" max-width="500">
            <v-card>
                <v-toolbar color="green" dark>
                    <v-toolbar-title>Nuova Categoria</v-toolbar-title>
                    <v-spacer></v-spacer>
                    <v-btn icon @click="newCategoryDialog = false">
                        <v-icon>mdi-close</v-icon>
                    </v-btn>
                </v-toolbar>

                <v-card-text class="pt-4">
                    <v-text-field v-model="newCategory.name" label="Nome categoria" outlined
                        :error-messages="newCategoryErrors.name"></v-text-field>

                    <v-color-picker v-model="newCategory.color" mode="hexa" swatches-max-height="100"></v-color-picker>
                </v-card-text>

                <v-card-actions>
                    <v-spacer></v-spacer>
                    <v-btn color="green" dark @click="createCategory" :loading="creatingCategory">
                        Crea
                    </v-btn>
                </v-card-actions>
            </v-card>
        </v-dialog>

    </v-app>
</template>

<script>
export default {
    middleware: 'auth',
    data() {
        return {
            perPage: 5,
            selectedCategory: null,
            totalItems: 0,
            totalThreads: 0,
            totalUsers: 0,
            topUsers: [],
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
            },
            newCategoryDialog: false,
            newCategory: {
                name: '',
                color: '#FF9800'
            },
            newCategoryErrors: {},
            creatingCategory: false,
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
        searchThreads() {
            this.page = 1; // Resetta alla prima pagina
            this.loadThreads(); // Usa lo stesso metodo di caricamento
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
                const params = {
                    page: this.page,
                };

                if (this.selectedCategory) {
                    params.category_id = this.selectedCategory;
                }

                if (this.searchQuery.trim() !== '') {
                    params.query = this.searchQuery;
                }

                // Usa SEMPRE lo stesso endpoint
                const response = await this.$axios.get('/api/threads', { params });

                this.threads = response.data.data.map(thread => ({
                    id: thread.id,
                    title: thread.title,
                    content: thread.content,
                    views_count: thread.views_count,
                    replies: thread.replies_count,
                    likes: thread.likes_count,
                    closed: thread.closed,
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

        // Metodo per gestire la selezione della categoria
        selectCategory(categoryId) {
            if (this.selectedCategory === categoryId) {
                // Se la categoria è già selezionata, deseleziona
                this.selectedCategory = null;
            } else {
                // Altrimenti seleziona la nuova categoria
                this.selectedCategory = categoryId;
            }
            this.page = 1; // Resetta alla prima pagina
            this.loadThreads();
        },
        async togglePin(thread) {
            try {
                const response = await this.$axios.put(
                    `http://localhost:8000/api/threads/${thread.id}/pin`
                );

                const pinnedStatus = response.data.pinned;

                this.threads = this.threads.map(t =>
                    t.id === thread.id ? { ...t, isPinned: pinnedStatus } : t
                );
            } catch (error) {
                console.error("Errore durante il pin:", error);
                this.$toast.error("Impossibile modificare lo stato del pin.");
            }
        },
        openNewThreadDialog() {
            if (this.$auth.loggedIn) {
                this.newThreadDialog = true
            } else {
                this.$router.push('/login')
            }
        },

        async loadStats() {
            try {
                const response = await this.$axios.get('/api/stats');
                this.totalThreads = response.data.total_threads;
                this.totalUsers = response.data.total_users;
                this.topUsers = response.data.top_users;
            } catch (error) {
                console.error('Errore caricamento statistiche:', error);
            }
        },
        openNewCategoryDialog() {
            this.newCategory = { name: '', color: '#FF9800' };
            this.newCategoryErrors = {};
            this.newCategoryDialog = true;
        },

        async createCategory() {
            this.creatingCategory = true;
            try {
                const response = await this.$axios.post('/api/categories', this.newCategory, {
                    headers: { Authorization: `Bearer ${this.$auth.strategy.token.get()}` }
                });

                this.$toast.success('Categoria creata con successo!');
                await this.loadCategories();
                this.newCategoryDialog = false;
            } catch (error) {
                if (error.response.status === 422) {
                    this.newCategoryErrors = error.response.data.errors;
                } else {
                    this.$toast.error('Errore nella creazione della categoria');
                }
            } finally {
                this.creatingCategory = false;
            }
        }

    },
    async mounted() {
        await this.loadCategories();
        await this.loadThreads();
        await this.loadStats();

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

.v-chip.v-chip--active {
    border: 2px solid #ff9800 !important;
    font-weight: bold;
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


.v-main {
    overflow-y: auto !important;
    height: 100vh;
}
</style>
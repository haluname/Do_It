<template>
  <v-app>
    <NavBarForum />
    <v-main style="background-color: #fdf3e4;">
      <!-- Hero Section -->
      <v-parallax src="/img/logoSMALL.svg" height="250" dark class="d-flex align-center justify-center">
        <v-container class="text-center">
          <h1 class="display-2 font-weight-bold mb-4">Wiki del Forum</h1>
          <p class="title">
            Scopri risorse utili per approfondire i tuoi argomenti preferiti.
          </p>
        </v-container>
      </v-parallax>

      <!-- Contenuto principale -->
      <v-container class="py-12">
        <!-- Sezione Risorse -->
        <v-row justify="center">
          <v-col cols="12" md="10" lg="8">
            <v-card class="pa-6 elevation-8 rounded-xl" color="#fdf3e4">
              <h2 class="text-h5 font-weight-bold mb-6 primary--text">
                📚 Risorse disponibili
              </h2>

              <!-- Lista categorie -->
              <v-expansion-panels accordion focusable>
                <!-- Categoria: Libri -->
                <v-expansion-panel>
                <v-expansion-panel-header class="font-weight-bold d-flex align-center">
  <v-icon class="mr-2">mdi-book</v-icon>
  Libri Consigliati
</v-expansion-panel-header>


                  <v-expansion-panel-content>
                    <v-list dense>
                      <v-card v-for="(book, index) in books" :key="index" class="mb-4 d-flex align-center book-card"
                        outlined>
                        <v-avatar size="56" class="ma-4" tile>
                          <v-icon large color="primary">mdi-book-open-page-variant</v-icon>
                        </v-avatar>
                        <v-card-text class="pa-0">
                          <div class="font-weight-bold">{{ book.title }}</div>
                          <div class="text--secondary">{{ book.author }} - {{ book.year }}</div>
                        </v-card-text>
                        <v-spacer />
                        <v-card-actions>
                          <v-btn icon :href="fileUrl(book.file_path)" target="_blank" color="primary">
                            <v-icon>mdi-file-pdf-box</v-icon>
                          </v-btn>
                          <v-btn icon color="secondary" @click="openPreview(book)">
                            <v-icon>mdi-eye</v-icon>
                          </v-btn>
                        </v-card-actions>
                      </v-card>

                    </v-list>
                  </v-expansion-panel-content>
                </v-expansion-panel>


                <v-expansion-panel>
                  <v-expansion-panel-header class="font-weight-bold">
                    <v-icon left>mdi-help-circle</v-icon>
                    FAQ
                  </v-expansion-panel-header>
                  <v-expansion-panel-content>
                    <v-expansion-panels accordion focusable flat>
                      <v-expansion-panel v-for="(faq, index) in faqs" :key="index">
                        <v-expansion-panel-header class="font-weight-medium">
                          {{ faq.question }}
                        </v-expansion-panel-header>
                        <v-expansion-panel-content v-html="faq.answer" style="padding: 25px;"></v-expansion-panel-content>
                      </v-expansion-panel>
                    </v-expansion-panels>
                  </v-expansion-panel-content>
                </v-expansion-panel>
              </v-expansion-panels>
            </v-card>
          </v-col>
        </v-row>

        <!-- Sezione PDF Viewer -->
        <v-row v-if="selectedBook" class="mt-10" justify="center">
          <v-col cols="12" md="10" lg="8">
            <v-card class="pa-6 elevation-8 rounded-xl" color="#fdf3e4">
              <div class="d-flex justify-space-between align-center mb-4">
                <h2 class="text-h5 font-weight-bold">📄 {{ selectedBook.title }}</h2>
                <v-btn icon @click="closePreview">
                  <v-icon>mdi-close</v-icon>
                </v-btn>
              </div>

              <v-responsive aspect-ratio="16/9" class="pdf-container">
                <iframe :key="'pdf-' + selectedBook.slug" :src="pdfUrl(selectedBook.slug)" width="100%" height="600px"
                  style="border: none" @error="handlePdfError"></iframe>
              </v-responsive>

            </v-card>
          </v-col>
        </v-row>

        <!-- Nuova sezione aggiunta manualmente -->
        <v-row v-if="isAdmin" justify="center" class="mt-8">
          <v-col cols="12" md="10" lg="8">
            <v-card class="pa-6 elevation-8 rounded-xl" color="#f0f7ff">
              <h2 class="text-h5 font-weight-bold mb-6 primary--text">
                📖 Aggiungi un nuovo libro
              </h2>
              <v-form ref="form" @submit.prevent="submitBook">
                <v-row dense>
                  <v-col cols="12" md="6">
                    <v-text-field v-model="newBook.title" label="Titolo" outlined dense
                      :rules="[v => !!v || 'Il titolo è obbligatorio']" />
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-text-field v-model="newBook.author" label="Autore" outlined dense
                      :rules="[v => !!v || 'L\'autore è obbligatorio']" />
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-text-field v-model="newBook.year" label="Anno" type="number" outlined dense
                      :rules="[v => !!v || 'L\'anno è obbligatorio']" />
                  </v-col>

                  <v-col cols="12" md="6">
                    <v-file-input v-model="newBook.file" label="Carica PDF" accept="application/pdf" outlined dense
                      :rules="[v => !!v || 'Il file è obbligatorio']" />
                  </v-col>

                  <v-col cols="12" class="text-right">
                    <v-btn type="submit" color="primary" :loading="isSubmitting" large>
                      Aggiungi
                    </v-btn>
                  </v-col>
                </v-row>
              </v-form>

            </v-card>
          </v-col>
        </v-row>
      </v-container>
        <span class="text-caption grey--text text--darken-1 mx-auto copyright" style="position: fixed; bottom: 10px; left: 50%; transform: translateX(-50%); font-size: 0.8rem; color: #6d4c41;">
        © {{ new Date().getFullYear() }} Do!t. Tutti i diritti riservati.
      </span>
    </v-main>
  </v-app>
</template>

<script>
export default {
  data() {
    return {
      books: [],
      newBook: {
        title: '',
        author: '',
        year: null,
        file: null
      },
      isSubmitting: false,
      faqs: [
        {
          question: "Come posso contribuire alla Wiki?",
          answer: "Puoi inviare una richiesta tramite la pagina &nbsp;<a href='/forum/contact' class='font-weight-bold'>Contatto</a>."
        },
        {
          question: "I PDF sono gratuiti?",
          answer: "Sì, tutti i contenuti presenti in questa sezione sono gratuiti e open source."
        }
      ],
      selectedBook: null,
      pdfError: false,
      // Aggiungi questa variabile per l'URL base del backend
      baseUrl: 'http://localhost:8000'
    }
  },
  computed: {
    isAdmin() {
      return this.$auth.user.forum_role === 'mod';
    }
  },
  watch: {
    selectedBook(newVal, oldVal) {
      if (newVal && oldVal && newVal.slug === oldVal.slug) return;

      this.$nextTick(() => {
        setTimeout(() => {
          const element = document.querySelector('.pdf-container');
          if (element) {
            element.scrollIntoView({ behavior: 'smooth' });
          }
        }, 300);
      });
    }
  },
  async mounted() {
    await this.loadBooks()
  },
  methods: {
    // Metodo per generare l'URL completo del file
    fileUrl(filePath) {
      return `${this.baseUrl}/storage/${filePath}`
    },
    async loadBooks() {
      try {
        const response = await this.$axios.get('/api/resources')
        this.books = response.data.books
        console.log('Libri caricati:', this.books)
      } catch (error) {
        console.error('Errore nel caricamento dei libri:', error)
        this.$toast.error('Errore nel caricamento delle risorse')
      }
    },
    pdfUrl(slug) {
      return `${this.baseUrl}/api/resources/file/${slug}`;
    },

    // MODIFICA openPreview per usare slug invece di file_path
    openPreview(book) {
      if (this.selectedBook && this.selectedBook.slug === book.slug) {
        this.selectedBook = null;
        return;
      }

      this.selectedBook = book;
      this.pdfError = false;

      // Scrolla solo se necessario
      this.$nextTick(() => {
        const element = document.querySelector('.pdf-container');
        if (element) {
          const rect = element.getBoundingClientRect();
          const isVisible = (
            rect.top >= 0 &&
            rect.left >= 0 &&
            rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
            rect.right <= (window.innerWidth || document.documentElement.clientWidth)
          );

          if (!isVisible) {
            element.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }
        }
      })
    },
    closePreview() {
      this.selectedBook = null
      this.pdfError = false
    },
    async submitBook() {
      if (this.$refs.form.validate()) {
        this.isSubmitting = true

        const formData = new FormData()
        formData.append('title', this.newBook.title)
        formData.append('author', this.newBook.author)
        formData.append('year', this.newBook.year)
        formData.append('file', this.newBook.file)

        try {
          const response = await this.$axios.post('/api/resources/books', formData, {
            headers: {
              'Content-Type': 'multipart/form-data'
            }
          })

          this.newBook = {
            title: '',
            author: '',
            year: null,
            file: null
          }
          this.$refs.form.resetValidation()

          await this.loadBooks()
          this.$toast.success('Libro aggiunto con successo!')
        } catch (error) {
          console.error('Errore durante l\'aggiunta del libro:', error)

          let errorMessage = 'Si è verificato un errore durante l\'aggiunta del libro.'
          if (error.response) {
            if (error.response.data && error.response.data.errors) {
              const errors = Object.values(error.response.data.errors).flat()
              errorMessage = errors.join('\n')
            } else if (error.response.data && error.response.data.message) {
              errorMessage = error.response.data.message
            }
          }

          this.$toast.error(errorMessage)
        } finally {
          this.isSubmitting = false
        }
      }
    },
    handlePdfError() {
      this.pdfError = true
      this.$toast.error('Errore nel caricamento del PDF')
    }
  }
}
</script>

<style scoped>
* {
  font-family: 'Montserrat', 'Segoe UI', Arial, sans-serif !important;
  color: #23263a;
}


html,
body,
#app {
  height: 100%;
  width: 100%;
  overflow-x: hidden;
  overflow-y: auto;
}

#app {
  display: flex;
  flex-direction: column;
}

.v-main {
  overflow-y: auto !important;
  height: 100vh;
  background: #f7f7fa;
}

.v-parallax {
  background-position: bottom;
  background-size: cover;
  position: relative;
  background: #fdf3e4;
  box-shadow: 0 8px 36px rgba(30, 34, 50, 0.04);

}

.v-parallax::after {
  content: "";
  position: absolute;
  inset: 0;
  background: linear-gradient(120deg,
      rgba(244, 145, 51, 0.2) 0%,
      rgba(255, 155, 61, 0.1) 100%);
  z-index: 1;
}

.v-parallax .v-container {
  position: relative;
  z-index: 2;
}

.v-parallax h1 {
  font-size: 3rem;
  /* Dimensione più grande */
  color: #ffffff;
  /* Colore personalizzato */
  text-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
  /* Ombra più marcata */
  font-weight: 900;
  /* Grassetto estremo */
  letter-spacing: 1px;
  /* Spaziatura tra le lettere */
}



.v-parallax p {
  color: #7a7d8c;
  font-weight: 500;
}

/* Card */
.v-card {
  border-radius: 16px !important;
  box-shadow: 0 6px 24px rgba(30, 34, 50, 0.07);
  border: 1px solid #e5e6ea;
  background: #fff !important;
  transition: box-shadow 0.18s, transform 0.18s;
}

.v-card.pa-6.elevation-8.rounded-xl {
  border-left: 4px solid #1a237e;
}

/* Book Cards */
.book-card {
  border-radius: 12px;
  border: 1px solid #e5e6ea;
  background: #fbfbfd !important;
  box-shadow: 0 2px 10px rgba(30, 34, 50, 0.03);
  transition: box-shadow 0.18s, border-color 0.18s, transform 0.18s;
}

.book-card:hover {
  border-color: #1a237e;
  box-shadow: 0 12px 28px rgba(30, 34, 50, 0.07);
  transform: scale(1.012);
}

.v-avatar {
  background: #e5e6ea;
  border-radius: 10px;
}

.v-card-actions .v-btn {
  margin-left: 4px;
  background: #f7f7fa;
  border-radius: 10px;
  box-shadow: 0 2px 8px rgba(30, 34, 50, 0.03);
  color: #1a237e !important;
}

.v-card-actions .v-btn:hover {
  background: #e5e6ea;
  color: #1a237e !important;
}

/* Expansion Panels */
.v-expansion-panel-header {
  display: flex;
  align-items: center;
}

.v-expansion-panel-header:hover {
  background: #e5e6ea;
}

.v-expansion-panel-content__wrap {
  padding: 18px 30px !important;
  background: #f7f7fa;
  border-radius: 0 0 10px 10px;
}

.v-expansion-panel-header .v-icon {
  color: #1a237e;
  font-size: 28px !important;
  margin-right: 12px;
  transition: color 0.3s ease;
}


.v-expansion-panel-header:hover .v-icon {
  color: #3a466e;
  /* Cambio colore su hover */
}

/* FAQ */
.v-expansion-panels[flat] .v-expansion-panel-header {
  background: #f7f7fa !important;
  color: #3a466e;
  font-weight: 600;
}

.v-expansion-panels[flat] .v-expansion-panel-content__wrap {
  background: #fff !important;
  border-left: 3px solid #e5e6ea;
}

/* PDF Viewer */
.pdf-container {
  border: 1.5px solid #e5e6ea;
  border-radius: 12px;
  box-shadow: 0 6px 24px rgba(30, 34, 50, 0.08);
  overflow: hidden;
  background: #fff;
}

.pdf-container iframe {
  min-height: 380px;
  background: #fff;
}

@media (max-width: 600px) {
  .pdf-container iframe {
    height: 320px;
    min-height: 220px;
  }
}

/* Sezione aggiunta libro */
.v-card[color="#f0f7ff"] {
  border: 1.5px dashed #3a466e;
  background: #f7f7fa !important;
}

.v-card[color="#f0f7ff"] h2 {
  border-bottom: 1px solid #e5e6ea;
  padding-bottom: 10px;
  margin-bottom: 20px;
  color: #1a237e;
}

.v-text-field,
.v-file-input {
  background: #fbfbfd !important;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(30, 34, 50, 0.02);
}

.v-btn[type="submit"] {
  background-color: #1a237e !important;
  color: #fff !important;
  font-weight: 600;
  letter-spacing: 0.3px;
  border-radius: 12px;
  box-shadow: 0 2px 8px rgba(26, 35, 126, 0.07);
  transition: background 0.18s;
}

.v-btn[type="submit"]:hover {
  background: #3a466e !important;
}

.v-footer {
  background: #23263a !important;
  box-shadow: 0 -4px 12px rgba(30, 34, 50, 0.06);
  border-radius: 0;
}

.v-footer .v-card-text {
  color: #fff !important;
  font-weight: 500;
  letter-spacing: 0.6px;
}

@media (max-width: 960px) {

  .v-card,
  .v-card.pa-6.elevation-8.rounded-xl {
    padding: 14px !important;
  }

  .v-expansion-panel-content__wrap {
    padding: 10px !important;
  }
}

/* Microtransizioni */
.v-expansion-panel-content__wrap,
.v-card,
.book-card,
.v-btn {
  transition: box-shadow 0.18s, background 0.18s, transform 0.18s, border-color 0.18s;
}
</style>

<template>
  <v-app>
    <!-- Hero Section -->
    <v-parallax
      src="/img/wiki-hero.jpg"
      height="300"
      dark
      class="d-flex align-center justify-center"
    >
      <v-container class="text-center">
        <h1 class="display-2 font-weight-bold mb-4">Wiki del Forum</h1>
        <p class="title">Scopri guide, libri e risorse utili per approfondire i tuoi argomenti preferiti.</p>
      </v-container>
    </v-parallax>

    <!-- Contenuto principale -->
    <v-container class="py-12">
      <!-- Sezione Risorse -->
      <v-row justify="center">
        <v-col cols="12" md="10" lg="8">
          <v-card class="pa-6 elevation-8 rounded-xl" color="#fdf3e4">
            <h2 class="text-h5 font-weight-bold mb-6 primary--text">📚 Risorse disponibili</h2>

            <!-- Lista categorie -->
            <v-expansion-panels accordion focusable>
              <!-- Categoria: Libri -->
              <v-expansion-panel>
                <v-expansion-panel-header class="font-weight-bold">📘 Libri Consigliati</v-expansion-panel-header>
                <v-expansion-panel-content>
                  <v-list dense>
                    <v-list-item v-for="(book, index) in books" :key="index" two-line>
                      <v-list-item-content>
                        <v-list-item-title class="font-weight-medium">{{ book.title }}</v-list-item-title>
                        <v-list-item-sub-title>{{ book.author }} - {{ book.year }}</v-list-item-sub-title>
                      </v-list-item-content>
                      <v-list-item-action>
                        <v-btn :href="book.link" target="_blank" icon color="primary">
                          <v-icon>mdi-file-pdf-box</v-icon>
                        </v-btn>
                      </v-list-item-action>
                    </v-list-item>
                  </v-list>
                </v-expansion-panel-content>
              </v-expansion-panel>

              <!-- Categoria: Guide -->
              <v-expansion-panel>
                <v-expansion-panel-header class="font-weight-bold">📖 Guide Pratiche</v-expansion-panel-header>
                <v-expansion-panel-content>
                  <v-list dense>
                    <v-list-item v-for="(guide, index) in guides" :key="index" two-line>
                      <v-list-item-content>
                        <v-list-item-title>{{ guide.title }}</v-list-item-title>
                        <v-list-item-sub-title>{{ guide.description }}</v-list-item-sub-title>
                      </v-list-item-content>
                      <v-list-item-action>
                        <v-btn :href="guide.link" target="_blank" icon color="primary">
                          <v-icon>mdi-download</v-icon>
                        </v-btn>
                      </v-list-item-action>
                    </v-list-item>
                  </v-list>
                </v-expansion-panel-content>
              </v-expansion-panel>

              <!-- Categoria: FAQ -->
              <v-expansion-panel>
                <v-expansion-panel-header class="font-weight-bold">❓ FAQ</v-expansion-panel-header>
                <v-expansion-panel-content>
                  <v-expansion-panels accordion focusable flat>
                    <v-expansion-panel v-for="(faq, index) in faqs" :key="index">
                      <v-expansion-panel-header class="font-weight-medium">{{ faq.question }}</v-expansion-panel-header>
                      <v-expansion-panel-content v-html="faq.answer"></v-expansion-panel-content>
                    </v-expansion-panel>
                  </v-expansion-panels>
                </v-expansion-panel-content>
              </v-expansion-panel>
            </v-expansion-panels>
          </v-card>
        </v-col>
      </v-row>

      <!-- Sezione PDF Viewer (Opzionale) -->
      <v-row class="mt-10" justify="center" v-if="showPdf">
        <v-col cols="12" md="10" lg="8">
          <v-card class="pa-6 elevation-8 rounded-xl" color="#fdf3e4">
            <h2 class="text-h5 font-weight-bold mb-6">📄 Anteprima PDF</h2>
            <!-- Usa un componente PDF viewer -->
            <div class="pdf-container">
              <iframe
                :src="selectedPdf"
                style="width: 100%; height: 600px; border: none;"
                frameborder="0"
              ></iframe>
            </div>
          </v-card>
        </v-col>
      </v-row>
    </v-container>

    <!-- Footer -->
    <v-footer dark padless>
      <v-card class="flex" flat tile color="primary">
        <v-card-text class="white--text text-center">
          &copy; {{ new Date().getFullYear() }} - Do!t Forum
        </v-card-text>
      </v-card>
    </v-footer>
  </v-app>
</template>

<script>
export default {
  data() {
    return {
      // Esempio di dati (da sostituire con chiamata API)
      books: [
        {
          title: "Introduzione alla Programmazione",
          author: "Mario Rossi",
          year: 2022,
          link: "/docs/libri/programmazione.pdf"
        },
        {
          title: "Guida al Machine Learning",
          author: "Luca Bianchi",
          year: 2021,
          link: "/docs/libri/ml.pdf"
        }
      ],
      guides: [
        {
          title: "Come creare un sito web",
          description: "Guida passo passo per principianti",
          link: "/docs/guide/webdev.pdf"
        },
        {
          title: "Manuale di UX Design",
          description: "Linee guida per un'esperienza utente ottimale",
          link: "/docs/guide/ux.pdf"
        }
      ],
      faqs: [
        {
          question: "Come posso contribuire alla Wiki?",
          answer: "Puoi inviare una richiesta tramite la pagina <a href='/forum/contact' class='white--text font-weight-bold'>Contatto</a>."
        },
        {
          question: "I PDF sono gratuiti?",
          answer: "Sì, tutti i contenuti presenti in questa sezione sono gratuiti e open source."
        }
      ],
      showPdf: true, // Mostra/nasconde il viewer
      selectedPdf: "/docs/libri/programmazione.pdf" // PDF da mostrare
    };
  }
};
</script>

<style scoped>
* {
  font-family: "Uto-Bold", sans-serif !important;
}

.pdf-container {
  border: 1px solid #ccc;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.v-expansion-panel-header {
  font-size: 1.1rem;
  color: #444444;
}
</style>
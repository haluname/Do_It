<template>
    <v-app>
        <Loader :generating="generating" name = "flashcards" />
   
        <NavBar />

        <v-main style="background-color: #fdf3e4;">
            <!-- Modifica al container principale -->
            <v-container class="py-4" style="min-height: 90vh;"> <!-- Ridotto padding e aggiunto min-height -->
                <!-- Modifica allo slide group -->
                <v-slide-group show-arrows class="pa-2" style="overflow: visible; min-height: 500px;">
                    <v-slide-item v-for="file in files" :key="file.id" v-slot="{ toggle }" class="mb-4">
                        <!-- Aggiunto stile alla card -->
                        <div class="carousel-item mx-2" style="margin-top: 20px; margin-bottom: 20px;">
                            <!-- Margini verticali -->
                            <v-card class="file-card" elevation="6" width="300" style="min-height: 200px;"
                                :data-file-type="getFileType(file.mime_type)">


                                <v-card-title class="file-title">
                                    <v-icon class="mr-2">{{ fileIcon(file.mime_type) }}</v-icon>
                                    {{ truncateFileName(file.original_name) }}
                                </v-card-title>

                                <v-card-text>
                                    <div class="file-info">
                                        <v-chip small class="mb-2">
                                            {{ formatSize(file.size) }}
                                        </v-chip>
                                        <div>{{ formatDate(file.created_at) }}</div>
                                    </div>
                                </v-card-text>

                                <v-card-actions class="justify-end pa-2">
                                    <!-- Pulsante Anteprima -->
                                    <v-btn icon v-if="isPreviewable(file.mime_type)" @click="openFile(file)"
                                        color="teal">
                                        <v-icon>mdi-open-in-new</v-icon>
                                    </v-btn>

                                    <!-- Pulsanti esistenti -->
                                    <v-btn icon @click="downloadFile(file)" color="primary">
                                        <v-icon>mdi-download</v-icon>
                                    </v-btn>
                                    <v-btn icon @click="deleteFile(file)" color="error">
                                        <v-icon>mdi-delete</v-icon>
                                    </v-btn>
                                </v-card-actions>
                            </v-card>
                        </div>
                    </v-slide-item>

                </v-slide-group>

                <v-card elevation="6" class="mb-8">
                    <v-toolbar flat color="teal lighten-5">
                        <v-toolbar-title class="teal--text text--darken-3">
                            <v-icon left>mdi-cards</v-icon>
                            Generatore di Flashcard
                        </v-toolbar-title>
                    </v-toolbar>

                    <v-card-text>
                        <v-row>
                            <v-col cols="12" md="6">
                                <v-card outlined>
                                    <v-card-title>Carica Documento</v-card-title>
                                    <v-card-text>
                                        <v-file-input v-model="documentFile" accept=".pdf,.txt,.docx"
                                            label="Seleziona PDF, TXT o DOCX" outlined prepend-icon="mdi-file-document"
                                            @change="extractText"></v-file-input>

                                        <v-btn color="teal darken-2" dark @click="generateFlashcards"
                                            :disabled="!extractedText || generating" :loading="generating" class="mt-4">
                                            <v-icon left>mdi-auto-fix</v-icon>
                                            Genera Flashcard
                                        </v-btn>
                                    </v-card-text>
                                </v-card>
                            </v-col>

                            <v-col cols="12" md="6">
                                <v-card outlined>
                                    <v-card-title>Flashcard Generate</v-card-title>
                                    <v-card-text>
                                        <div v-if="flashcards.length === 0" class="text-center py-4 grey--text">
                                            <v-icon size="64" color="grey lighten-2">mdi-cards-outline</v-icon>
                                            <p class="mt-2">Nessuna flashcard generata</p>
                                        </div>

                                        <div v-else>
                                            <v-expansion-panels accordion>
                                                <v-expansion-panel v-for="(card, index) in flashcards" :key="index">
                                                    <v-expansion-panel-header>
                                                        <strong>{{ card.question }}</strong>
                                                    </v-expansion-panel-header>
                                                    <v-expansion-panel-content>
                                                        {{ card.answer }}
                                                    </v-expansion-panel-content>
                                                </v-expansion-panel>
                                            </v-expansion-panels>

                                            <v-btn color="teal" dark @click="saveFlashcards" class="mt-4">
                                                <v-icon left>mdi-content-save</v-icon>
                                                Salva Flashcard
                                            </v-btn>
                                        </div>
                                    </v-card-text>
                                </v-card>
                            </v-col>
                        </v-row>
                    </v-card-text>
                </v-card>

                <v-btn fab dark color="primary" fixed bottom right class="mb-10 mr-10 elevation-12"
                    @click="$refs.fileInput.click()">
                    <v-icon>mdi-plus</v-icon>
                </v-btn>

                <input type="file" ref="fileInput" hidden @change="uploadFile">
            </v-container>
        </v-main>

    </v-app>

</template>

<script>
export default {
    middleware: 'auth',

    data() {
        return {
            files: [],
            documentFile: null,
            extractedText: "",
            generating: false,
            flashcards: [],
            pdfjs: null,
            mammoth: null
        }
    },

    async mounted() {
        await this.fetchFiles();
        await this.loadLibraries();
    },

    methods: {
        async fetchFiles() {
            try {
                const { data } = await this.$axios.get('http://localhost:8000/api/files')
                this.files = data
            } catch (error) {
                this.showError('Errore nel caricamento dei file')
            }
        },

        async uploadFile(e) {
            const file = e.target.files[0]
            if (!file) return

            const formData = new FormData()
            formData.append('file', file)

            try {
                await this.$axios.post('http://localhost:8000/api/files', formData)
                await this.fetchFiles()
                this.showSuccess('File caricato con successo')
            } catch (error) {
                this.showError('Errore nel caricamento del file')
            }
        },

        async deleteFile(file) {
            try {
                await this.$axios.delete(`http://localhost:8000/api/files/${file.id}`)
                this.files = this.files.filter(f => f.id !== file.id)
                this.showSuccess('File eliminato')
            } catch (error) {
                this.showError('Errore nell\'eliminazione')
            }
        },

        downloadFile(file) {
            const url = `http://localhost:8000/api/files/${file.id}/download`;
            const token = this.$auth.strategy.token.get().split(' ')[1]; // Rimuove "Bearer"

            this.$axios({
                url: url,
                method: 'GET',
                responseType: 'blob',
                headers: {
                    Authorization: `Bearer ${token}`
                }
            }).then(response => {
                const blob = new Blob([response.data]);
                const link = document.createElement('a');
                link.href = URL.createObjectURL(blob);
                link.download = file.original_name;
                link.click();
                URL.revokeObjectURL(link.href);
            }).catch(error => {
                this.showError('Errore nel download del file');
            });
        },

        fileIcon(mime) {
            const types = {
                'image/': 'mdi-image',
                'audio/': 'mdi-music-note',
                'video/': 'mdi-video',
                'application/pdf': 'mdi-file-pdf-box',
                'text/': 'mdi-file-document'
            }
            return Object.entries(types).find(([key]) => mime.includes(key))?.[1] || 'mdi-file'
        },

        isPreviewable(mimeType) {
            const previewable = [
                'image/',
                'application/pdf',
                'text/plain',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ];
            return previewable.some(type => mimeType.includes(type));
        },

        openFile(file) {
            const token = this.$auth.strategy.token.get().split(' ')[1]; // Rimuove "Bearer"
            const url = `http://localhost:8000/api/files/${file.id}/preview`;

            // Crea un link temporaneo con autenticazione
            const tempLink = document.createElement('a');
            tempLink.href = url;
            tempLink.setAttribute('target', '_blank');
            tempLink.setAttribute('rel', 'noopener noreferrer');
            tempLink.setAttribute('download', '');

            fetch(url, {
                headers: {
                    'Authorization': `Bearer ${token}`,
                    'Content-Type': 'application/json',
                }
            })
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.blob();
                })
                .then(blob => {
                    const blobUrl = window.URL.createObjectURL(blob);
                    window.open(blobUrl, '_blank');
                    window.URL.revokeObjectURL(blobUrl);
                })
                .catch(error => {
                    this.showError('Errore durante l\'apertura del file');
                });
        },

        truncateFileName(name) {
            return name.length > 30 ? name.substr(0, 30) + '...' : name
        },

        formatSize(bytes) {
            const sizes = ['B', 'KB', 'MB', 'GB']
            if (bytes === 0) return '0 B'
            const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)))
            return Math.round(bytes / Math.pow(1024, i)) + ' ' + sizes[i]
        },

        formatDate(date) {
            return new Date(date).toLocaleDateString('it-IT')
        },

        showSuccess(text) {
            this.$store.commit('snackbar/show', { text, color: 'success' })
        },

        showError(text) {
            this.$store.commit('snackbar/show', { text, color: 'error' })
        },
        getFileType(mime) {
            if (mime.includes('pdf')) return 'pdf';
            if (mime.includes('image')) return 'image';
            if (mime.includes('video')) return 'video';
            if (mime.includes('msword') || mime.includes('document')) return 'document';
            return 'other';
        },
        async loadLibraries() {
            // Carica PDF.js
            if (!window.pdfjsLib) {
                await new Promise((resolve, reject) => {
                    const script = document.createElement("script");
                    script.src = "https://cdn.jsdelivr.net/npm/pdfjs-dist@3.4.120/build/pdf.min.js";
                    script.onload = () => {
                        window.pdfjsLib.GlobalWorkerOptions.workerSrc =
                            "https://cdn.jsdelivr.net/npm/pdfjs-dist@3.4.120/build/pdf.worker.min.js";
                        this.pdfjs = window.pdfjsLib;
                        resolve();
                    };
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
            } else {
                this.pdfjs = window.pdfjsLib;
            }

            // Carica Mammoth.js
            if (!window.mammoth) {
                await new Promise((resolve, reject) => {
                    const script = document.createElement("script");
                    script.src = "https://cdn.jsdelivr.net/npm/mammoth@1.4.21/mammoth.browser.min.js";
                    script.onload = () => {
                        this.mammoth = window.mammoth;
                        resolve();
                    };
                    script.onerror = reject;
                    document.head.appendChild(script);
                });
            } else {
                this.mammoth = window.mammoth;
            }
        },

        async extractText() {
            if (!this.documentFile) return;

            const extension = this.documentFile.name.split('.').pop().toLowerCase();

            try {
                if (extension === 'pdf') {
                    this.extractedText = await this.extractFromPDF(this.documentFile);
                } else if (extension === 'txt') {
                    this.extractedText = await this.extractFromText(this.documentFile);
                } else if (extension === 'docx') {
                    this.extractedText = await this.extractFromDocx(this.documentFile);
                } else {
                    this.showError('Formato file non supportato');
                }
            } catch (error) {
                console.error("Errore nell'estrazione:", error);
                this.showError('Errore nell\'estrazione del testo');
            }
        },

        // Estrazione da PDF
        async extractFromPDF(file) {
            const arrayBuffer = await file.arrayBuffer();
            const typedArray = new Uint8Array(arrayBuffer);

            const pdf = await this.pdfjs.getDocument(typedArray).promise;
            let fullText = "";

            for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                const page = await pdf.getPage(pageNum);
                const textContent = await page.getTextContent();
                fullText += textContent.items.map(item => item.str).join(" ") + "\n";
            }

            return fullText;
        },

        // Estrazione da file di testo
        extractFromText(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = reject;
                reader.readAsText(file);
            });
        },

        // Estrazione da DOCX
        async extractFromDocx(file) {
            const arrayBuffer = await file.arrayBuffer();
            const result = await this.mammoth.extractRawText({
                arrayBuffer: arrayBuffer
            });
            return result.value;
        },

        async generateFlashcards() {
            if (!this.extractedText) return;

            this.generating = true;
            this.flashcards = [];

            try {
                const prompt = `Dal seguente testo, genera quante flashcard credi siano utili per lo studio. 
                Ogni flashcard deve avere:
                1. Una domanda chiara
                2. Una risposta precisa basata sul testo
                
                DEVI RESTITUIRE SOLAMENTE UN JSON VALIDO SENZA ALTRO TESTO, COMMENTI O PREFAZIONI.

               Il JSON deve avere questa struttura:
{
  "flashcards": [
    {"question": "testo domanda", "answer": "testo risposta"},
    {"question": "testo domanda", "answer": "testo risposta"}
  ]
}
                
                Testo:
                ${this.extractedText}`; // Limita a 3000 caratteri

                const response = await fetch('https://openrouter.ai/api/v1/chat/completions', {
                    method: 'POST',
                    headers: {
                        Authorization: `Bearer ${this.$config.openrouterKey}`,
                        'HTTP-Referer': 'https://www.doit-app.it',
                        'X-Title': 'Do!t Flashcards',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        model: 'meta-llama/llama-3.3-70b-instruct:free',
                        messages: [{ role: 'user', content: prompt }],

                    }),
                });

                const data = await response.json();
                const aiResponse = data.choices?.[0]?.message?.content;
                const jsonStart = aiResponse.indexOf('{');
                const jsonEnd = aiResponse.lastIndexOf('}') + 1;
                const jsonString = aiResponse.substring(jsonStart, jsonEnd);

                const result = JSON.parse(jsonString);

                if (result.flashcards && Array.isArray(result.flashcards)) {
                    this.flashcards = result.flashcards;
                }
                console.log("Risposta AI:", aiResponse);

                try {
                    const result = JSON.parse(aiResponse);
                    if (result.flashcards && Array.isArray(result.flashcards)) {
                        this.flashcards = result.flashcards;
                    }
                } catch (e) {
                    console.error("Errore nel parsing:", e);
                    this.showError("Formato di risposta non valido");
                }
            } catch (error) {
                console.error("Errore nella generazione:", error);
                this.showError("Errore nella generazione delle flashcard");
                    this.tryFixJson(aiResponse);

            } finally {
                this.generating = false;
            }
        },

        saveFlashcards() {
            // Qui implementerai la logica per salvare le flashcard
            this.showSuccess("Flashcard salvate con successo!");
        },
          tryFixJson(jsonString) {
        try {
            // Tentativo 1: aggiunta di virgolette mancanti
            const fixed = jsonString
                .replace(/(\w+):/g, '"$1":')  // Aggiunge virgolette alle chiavi
                .replace(/: '(.+?)'/g, ': "$1"')  // Sostituisce singoli apici
                .replace(/: ([^{}\[\],]+)(?=[,\]}])/g, ': "$1"');  // Aggiunge virgolette a valori non quotati
            
            const result = JSON.parse(fixed);
            if (result.flashcards) {
                this.flashcards = result.flashcards;
                return;
            }
            
            // Tentativo 2: estrai array da risposta non strutturata
            const regex = /\{\s*"question":\s*"([^"]+)",\s*"answer":\s*"([^"]+)"\s*\}/g;
            let match;
            const flashcards = [];
            
            while ((match = regex.exec(jsonString)) !== null) {
                flashcards.push({
                    question: match[1],
                    answer: match[2]
                });
            }
            
            if (flashcards.length > 0) {
                this.flashcards = flashcards;
                return;
            }
            
            this.showError("Impossibile interpretare la risposta AI");
        } catch (fixError) {
            console.error("Correzione JSON fallita:", fixError);
            this.showError("Errore grave nell'interpretazione della risposta");
        }
    }
    }
}
</script>

<style scoped>
* {
    font-family: "Uto-Bold", sans-serif !important;
}


.v-main {
    overflow-y: auto !important;
    height: 100vh;
}

.flashcard-container {
    background-color: #e8f5e9;
    border-radius: 8px;
    padding: 16px;
    margin-top: 16px;
}

.v-expansion-panel {
    margin-bottom: 8px;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    overflow: hidden;
}

.v-expansion-panel-header {
    background-color: #f5f5f5;
    font-weight: 500;
}

.v-expansion-panel-content {
    background-color: white;
    padding: 16px;
}

/* Nuovo design per le card */
.file-card {
    border-radius: 16px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: linear-gradient(145deg, #ffffff, #fff9f0);
    border: 1px solid rgba(255, 209, 148, 0.3);
    overflow: hidden;
    position: relative;
}

.file-card:hover {
    transform: translateY(-5px) scale(1.02);
    box-shadow: 0 12px 24px rgba(255, 159, 28, 0.15) !important;
}

/* Header con gradiente */
.file-title {
    background: linear-gradient(45deg, #ff9f1c, #ffbf69);
    color: white !important;
    padding: 20px;
    font-size: 1.2rem;
    font-weight: 600;
    letter-spacing: 0.5px;
    border-radius: 16px 16px 0 0;
    display: flex;
    align-items: center;
    min-height: 70px;
}

.file-title .v-icon {
    color: white;
    font-size: 28px;
    margin-right: 12px;
}


/* Corpo della card */
.file-info {
    padding: 16px;
    position: relative;
    z-index: 1;
}

.v-chip {
    font-weight: 600;
    letter-spacing: 0.3px;
    background: rgba(255, 159, 28, 0.1) !important;
    color: #ff9f1c !important;
    border: 1px solid rgba(255, 159, 28, 0.2);
}

/* Footer con azioni */
.v-card__actions {
    background: rgba(255, 255, 255, 0.9);
    border-top: 1px solid rgba(255, 159, 28, 0.1);
    padding: 8px 16px;
    position: relative;
}

/* Effetti speciali */
.file-card::before {
    content: "";
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: radial-gradient(circle at 100% 0%, rgba(255, 191, 105, 0.1) 0%, transparent 30%);
    pointer-events: none;
}

.file-card[data-file-type="pdf"] .file-title {
    background: linear-gradient(45deg, #e74c3c, #c0392b);
}

.file-card[data-file-type="image"] .file-title {
    background: linear-gradient(45deg, #2ecc71, #27ae60);
}

.file-card[data-file-type="video"] .file-title {
    background: linear-gradient(45deg, #3498db, #2980b9);
}

.file-card[data-file-type="document"] .file-title {
    background: linear-gradient(45deg, #9b59b6, #8e44ad);
}

@keyframes float {
    0% {
        transform: translateY(0);
    }

    50% {
        transform: translateY(-5px);
    }

    100% {
        transform: translateY(0);
    }
}

.carousel-item {
    margin: 0 12px;
    width: 320px;
    transition: transform 0.3s ease;
}

.carousel-item:hover {
    animation: float 2s ease-in-out infinite;
}

.v-btn--icon {
    transition: all 0.3s ease;
    background: rgba(255, 255, 255, 0.9) !important;
    border: 1px solid rgba(0, 0, 0, 0.1);
}

.v-btn--icon:hover {
    transform: scale(1.15);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

.file-info div {
    color: #7f8c8d;
    font-size: 0.85rem;
    margin-top: 8px;
    font-style: italic;
}

.file-card:hover::before {
    transform: translate(10%, -10%);
}
</style>
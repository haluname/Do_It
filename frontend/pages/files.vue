<template>
    <v-app>
        <Loader :generating="generating" name="flashcards" />

        <NavBar />

        <v-main style="background-color: #fdf3e4;">
            <v-container class="py-4" style="min-height: 90vh;">

                         <!-- Sezione File Caricati -->
                <v-card elevation="6" class="mb-8">
                    <v-toolbar flat color="teal lighten-5">
                        <v-toolbar-title class="teal--text text--darken-3">
                            <v-icon left>mdi-file-multiple</v-icon>
                            I Tuoi File
                        </v-toolbar-title>
                    </v-toolbar>

                    <v-card-text>
                        <div v-if="files.length === 0" class="text-center py-4 grey--text">
                            <v-icon size="64" color="grey lighten-2">mdi-file-remove</v-icon>
                            <p class="mt-2">Non hai caricato nessun file</p>
                        </div>
                        <v-slide-group show-arrows class="pa-2 mb-8" style="overflow: visible; min-height: 500px;">
                            <v-slide-item v-for="file in files" :key="file.id" v-slot="{ toggle }" class="mb-4">
                                <div class="carousel-item mx-2" style="margin-top: 20px; margin-bottom: 20px;">
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
                                            <v-btn icon v-if="isPreviewable(file.mime_type)" @click="openFile(file)"
                                                color="teal">
                                                <v-icon>mdi-open-in-new</v-icon>
                                            </v-btn>
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
                    </v-card-text>
                </v-card>
                <!-- Sezione Flashcard Salvate -->
                <v-card elevation="6" class="mb-8">
                    <v-toolbar flat color="teal lighten-5">
                        <v-toolbar-title class="teal--text text--darken-3">
                            <v-icon left>mdi-cards</v-icon>
                            Le Tue Flashcard Salvate
                        </v-toolbar-title>
                    </v-toolbar>

                    <v-card-text>
                        <div v-if="savedFlashcards.length === 0" class="text-center py-4 grey--text">
                            <v-icon size="64" color="grey lighten-2">mdi-cards</v-icon>
                            <p class="mt-2">Nessuna flashcard salvata</p>
                        </div>

                        <div v-else>
                            <!-- Expansion panel per gruppo -->
                            <v-expansion-panels multiple class="mt-4">
                                <v-expansion-panel v-for="(group, groupName) in groupedFlashcards" :key="groupName">
                                    <v-expansion-panel-header class="group-header">
                                        <div class="d-flex align-center">
                                            <v-icon class="mr-2">mdi-folder</v-icon>
                                            <strong>{{ groupName }}</strong>
                                            <v-chip color="teal lighten-4" small class="ml-2">
                                                {{ group.length }} {{ group.length === 1 ? 'flashcard' : 'flashcards' }}
                                            </v-chip>
                                        </div>

                                        <template v-slot:actions>
                                            <v-btn icon @click.stop="deleteGroup(groupName)" color="error" class="ml-2">
                                                <v-icon>mdi-delete-forever</v-icon>
                                            </v-btn>
                                        </template>
                                    </v-expansion-panel-header>

                                    <v-expansion-panel-content>
                                        <div class="flashcards-grid">
                                            <!-- Mini card per ogni flashcard -->
                                            <v-card v-for="card in group" :key="card.id" class="mini-card ma-2"
                                                @click="showCardDetails(card)">
                                                <v-card-text class="question-text">
                                                    {{ card.question }}
                                                </v-card-text>

                                                <v-card-actions class="justify-end pa-1">
                                                    <v-btn icon small @click.stop="deleteFlashcard(card)" color="error">
                                                        <v-icon small>mdi-delete</v-icon>
                                                    </v-btn>
                                                </v-card-actions>
                                            </v-card>
                                        </div>
                                    </v-expansion-panel-content>
                                </v-expansion-panel>
                            </v-expansion-panels>
                        </div>
                    </v-card-text>
                </v-card>

                <!-- Dialog per salvare con nome gruppo -->
                <v-dialog v-model="saveDialog" max-width="500">
                    <v-card>
                        <v-card-title class="teal lighten-5">
                            <v-icon left>mdi-content-save</v-icon>
                            Salva Flashcard
                        </v-card-title>

                        <v-card-text class="pa-4">
                            <v-text-field v-model="groupName" label="Nome del gruppo" outlined clearable
                                hint="Es: Biologia Capitolo 3, Storia Romana" persistent-hint></v-text-field>

                            <div class="text-caption teal--text">
                                Assegna un nome significativo a questo gruppo di flashcard
                            </div>
                        </v-card-text>

                        <v-card-actions class="justify-end">
                            <v-btn color="grey" text @click="saveDialog = false">Annulla</v-btn>
                            <v-btn color="teal" dark @click="confirmSave">Salva</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>

                <!-- Dialog per visualizzare la flashcard completa -->
                <v-dialog v-model="cardDialog" max-width="600">
                    <v-card v-if="selectedCard">
                        <v-card-title class="teal lighten-5">
                            <v-icon left>mdi-card-text</v-icon>
                            Dettaglio Flashcard
                        </v-card-title>

                        <v-card-text class="pa-4">
                            <div class="question-dialog">
                                <strong>Domanda:</strong> {{ selectedCard.question }}
                            </div>
                            <v-divider class="my-3"></v-divider>
                            <div class="answer-dialog">
                                <strong>Risposta:</strong> {{ selectedCard.answer }}
                            </div>
                        </v-card-text>

                        <v-card-actions class="justify-end">
                            <v-btn color="teal" text @click="cardDialog = false">Chiudi</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>

       

                <!-- Sezione Generatore di Flashcard -->
                <v-card elevation="6" class="mb-8">
                    <v-toolbar flat color="teal lighten-5">
                        <v-toolbar-title class="teal--text text--darken-3">
                            <v-icon left>mdi-auto-fix</v-icon>
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
                                            <v-expansion-panels accordion class="mt-4">
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
            mammoth: null,
            savedFlashcards: [],
            deleting: false,
            saveDialog: false,      // Controlla il dialog di salvataggio
            groupName: '',          // Nome del gruppo per il salvataggio
            cardDialog: false,      // Controlla il dialog di dettaglio
            selectedCard: null
        }
    },

    computed: {
        // Raggruppa le flashcard per data di creazione
        groupedFlashcards() {
            const groups = {};
            this.savedFlashcards.forEach(card => {
                const group = card.group_name || 'Senza gruppo';

                if (!groups[group]) {
                    groups[group] = [];
                }
                groups[group].push(card);
            });

            // Ordina i gruppi alfabeticamente
            return Object.keys(groups).sort().reduce((acc, key) => {
                acc[key] = groups[key];
                return acc;
            }, {});
        }
    },

    async mounted() {
        await this.fetchFiles();
        await this.loadLibraries();
        await this.loadSavedFlashcards();
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

        formatGroupDate(dateString) {
            const date = new Date(dateString);
            return date.toLocaleDateString('it-IT', {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric'
            });
        },

        showCardDetails(card) {
            this.selectedCard = card;
            this.cardDialog = true;
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

        async loadSavedFlashcards() {
            try {
                const response = await this.$axios.get('http://localhost:8000/api/flashcards');
                this.savedFlashcards = response.data;
            } catch (error) {
                console.error("Errore nel caricamento delle flashcard:", error);
            }
        },

        saveFlashcards() {
            if (this.flashcards.length === 0) return;
            this.groupName = ''; // Resetta il nome gruppo
            this.saveDialog = true;
        },

        // Conferma il salvataggio con nome gruppo
        async confirmSave() {
            if (!this.groupName) {
                this.showError("Inserisci un nome per il gruppo");
                return;
            }

            try {
                await this.$axios.post('http://localhost:8000/api/flashcards', {
                    group_name: this.groupName,
                    flashcards: this.flashcards
                });

                this.showSuccess(`Flashcard salvate nel gruppo "${this.groupName}"!`);

                // Ricarica le flashcard salvate dopo il salvataggio
                await this.loadSavedFlashcards();

                // Resetta le flashcard generate
                this.flashcards = [];
                this.saveDialog = false;
            } catch (error) {
                console.error("Errore nel salvataggio:", error);
                this.showError("Errore nel salvataggio delle flashcard");
            }
        },

        async deleteFlashcard(flashcard) {
            if (!confirm('Sei sicuro di voler eliminare questa flashcard?')) {
                return;
            }

            this.deleting = true;

            try {
                await this.$axios.delete(`http://localhost:8000/api/flashcards/${flashcard.id}`);
                this.savedFlashcards = this.savedFlashcards.filter(card => card.id !== flashcard.id);
                this.showSuccess('Flashcard eliminata!');
            } catch (error) {
                console.error("Errore nell'eliminazione:", error);
                this.showError("Errore nell'eliminazione della flashcard");
            } finally {
                this.deleting = false;
            }
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
        },

        async deleteGroup(groupName) {
            if (!confirm(`Sei sicuro di voler eliminare TUTTO il gruppo "${groupName}"? Questa azione è irreversibile!`)) {
                return;
            }

            this.deleting = true;

            try {
                await this.$axios.delete(`http://localhost:8000/api/flashcards/group/${encodeURIComponent(groupName)}`);

                this.savedFlashcards = this.savedFlashcards.filter(card => card.group_name !== groupName);

                this.showSuccess(`Gruppo "${groupName}" eliminato completamente!`);
            } catch (error) {
                console.error("Errore nell'eliminazione del gruppo:", error);
                this.showError("Errore nell'eliminazione del gruppo");
            } finally {
                this.deleting = false;
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

.group-header {
    background-color: #e0f2f1;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
}

.flashcards-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 12px;
    padding: 10px;
}

.mini-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border-radius: 12px;
    background: linear-gradient(145deg, #ffffff, #f0f0f0);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    min-height: 100px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    overflow: hidden;
    border-left: 4px solid #ff9f1c;
}

.mini-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.15);
    background: linear-gradient(145deg, #ffffff, #e6f7ff);
}

.question-text {
    font-size: 0.95rem;
    padding: 12px;
    flex-grow: 1;
    display: flex;
    align-items: center;
}

.question-dialog,
.answer-dialog {
    font-size: 1.1rem;
    padding: 10px;
    line-height: 1.6;
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

.group-header .v-btn--icon {
    background: rgba(255, 255, 255, 0.7) !important;
    border: 1px solid rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.group-header .v-btn--icon:hover {
    transform: scale(1.1);
    background: rgba(255, 235, 238, 0.9) !important;
}
</style>
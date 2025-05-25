<template>
    <v-app>
        <NavBar />

       <v-main style="background-color: #fdf3e4;">
      <!-- Modifica al container principale -->
      <v-container class="py-4" style="min-height: 90vh;"> <!-- Ridotto padding e aggiunto min-height -->
        <!-- Modifica allo slide group -->
        <v-slide-group 
          show-arrows 
          class="pa-2" 
          style="overflow: visible; min-height: 500px;"
        >
          <v-slide-item 
            v-for="file in files" 
            :key="file.id" 
            v-slot="{ toggle }"
            class="mb-4" 
          >
            <!-- Aggiunto stile alla card -->
            <div class="carousel-item mx-2" style="margin-top: 20px; margin-bottom: 20px;"> <!-- Margini verticali -->
              <v-card 
                class="file-card" 
                elevation="6" 
                width="300"
                style="min-height: 200px;"
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
            files: []
        }
    },

    async mounted() {
        await this.fetchFiles()
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
        }
    }
}
</script>

<style scoped>
* {
    font-family: "Uto-Bold";
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
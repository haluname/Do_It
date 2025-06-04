<template>
  <div>
    <input 
      type="file" 
      accept=".pdf,.txt,.docx" 
      @change="handleFileUpload"
    />
    <div v-if="extractedText" class="text-container">
      {{ extractedText }}
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      extractedText: ""
    };
  },
  methods: {
    async handleFileUpload(event) {
      const file = event.target.files[0];
      if (!file) return;

      const extension = file.name.split('.').pop().toLowerCase();
      
      try {
        if (extension === 'pdf') {
          this.extractedText = await this.extractFromPDF(file);
        } else if (extension === 'txt') {
          this.extractedText = await this.extractFromText(file);
        } else if (extension === 'docx') {
          this.extractedText = await this.extractFromDocx(file);
        } else {
          alert('Formato file non supportato');
        }
      } catch (error) {
        console.error("Errore:", error);
        this.extractedText = "Errore nell'estrazione del testo";
      }
    },

    // Estrazione da PDF (usando PDF.js)
    async extractFromPDF(file) {
      const pdfjs = await this.loadPDFJS();
      const arrayBuffer = await file.arrayBuffer();
      const typedArray = new Uint8Array(arrayBuffer);

      const pdf = await pdfjs.getDocument(typedArray).promise;
      let fullText = "";

      for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
        const page = await pdf.getPage(pageNum);
        const textContent = await page.getTextContent();
        fullText += textContent.items.map(item => item.str).join(" ") + "\n";
      }

      return fullText;
    },

    // Caricamento dinamico PDF.js
    loadPDFJS() {
      return new Promise((resolve, reject) => {
        if (window.pdfjsLib) return resolve(window.pdfjsLib);

        const script = document.createElement("script");
        script.src = "https://cdn.jsdelivr.net/npm/pdfjs-dist@3.4.120/build/pdf.min.js";
        script.onload = () => {
          window.pdfjsLib.GlobalWorkerOptions.workerSrc = 
            "https://cdn.jsdelivr.net/npm/pdfjs-dist@3.4.120/build/pdf.worker.min.js";
          resolve(window.pdfjsLib);
        };
        script.onerror = reject;
        document.head.appendChild(script);
      });
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

    // ESTRAZIONE DOCX CORRETTA CON MAMMOTH.JS
    async extractFromDocx(file) {
      await this.loadMammoth();
      const arrayBuffer = await file.arrayBuffer();
      
      const result = await window.mammoth.extractRawText({ 
        arrayBuffer: arrayBuffer 
      });
      
      return result.value;
    },

    // Caricamento dinamico Mammoth.js (libreria specifica per DOCX)
    loadMammoth() {
      return new Promise((resolve, reject) => {
        if (window.mammoth) return resolve();

        const script = document.createElement("script");
        script.src = "https://cdn.jsdelivr.net/npm/mammoth@1.4.21/mammoth.browser.min.js";
        script.onload = resolve;
        script.onerror = reject;
        document.head.appendChild(script);
      });
    }
  }
};
</script>

<style>
.text-container {
  white-space: pre-wrap;
  margin-top: 20px;
  padding: 15px;
  border: 1px solid #eee;
  border-radius: 4px;
}
</style>
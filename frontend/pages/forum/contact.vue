<template>
    <v-app>
        <NavBarForum />

        <v-main>
            <!-- Hero Section -->
            <v-parallax
                src="https://images.unsplash.com/photo-1543269865-cbf427effbad?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&h=600"
                height="300" dark class="d-flex align-center justify-center">
                <v-container class="text-center">
                    <h1 class="display-2 font-weight-bold mb-4 text-shadow">Contattaci</h1>
                    <p class="title text-shadow">
                        Hai domande o suggerimenti? Siamo qui per aiutarti!
                    </p>
                </v-container>
            </v-parallax>

            <v-container class="py-8 py-md-12">
                <v-row justify="center">
                    <v-col cols="12" md="10" lg="8">
                        <v-card class="contact-card pa-4 pa-md-8 elevation-6 rounded-lg">
                            <v-row>
                                <!-- Contact Info -->
                                <v-col cols="12" md="5" class="contact-info">
                                    <div class="logo mb-6">
                                        <v-icon large color="primary" class="mr-2">mdi-forum</v-icon>
                                        <span class="text-h5 font-weight-bold primary--text">Do!t<span
                                                class="accent--text">Team</span></span>
                                    </div>

                                    <h2 class="text-h4 font-weight-bold mb-4 white--text">Contattaci</h2>

                                    <p class="contact-text mb-6">
                                        Hai domande, suggerimenti o hai bisogno di assistenza? Il nostro team è qui per
                                        aiutarti.
                                        Compila il modulo o contattaci direttamente usando le informazioni qui sotto.
                                    </p>

                                    <div class="contact-details mb-6">

                                        <div class="contact-item d-flex mb-4">
                                            <v-icon color="accent" class="mr-3">mdi-email</v-icon>
                                            <div>
                                                <h3 class="font-weight-bold white--text">Email</h3>
                                                <p class="mb-0">hello.doitapp@gmail.com<br></p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="social-links">
                                        <v-btn icon large color="white" class="social-icon">
                                            <v-icon>mdi-facebook</v-icon>
                                        </v-btn>
                                        <v-btn icon large color="white" class="social-icon">
                                            <v-icon>mdi-twitter</v-icon>
                                        </v-btn>
                                        <v-btn icon large color="white" class="social-icon">
                                            <v-icon>mdi-instagram</v-icon>
                                        </v-btn>
                                        <v-btn icon large color="white" class="social-icon">
                                            <v-icon>mdi-linkedin</v-icon>
                                        </v-btn>
                                    </div>
                                </v-col>

                                <!-- Contact Form -->
                                <v-col cols="12" md="7" class="contact-form">
                                    <h2 class="text-h4 font-weight-bold mb-6 primary--text">Invia un messaggio</h2>

                                    <v-form ref="form" @submit.prevent="submitForm">
                                        <v-row>
                                            <v-col cols="12" md="6">
                                                <v-text-field v-model="form.name" label="Nome completo" outlined
                                                    color="primary" :rules="[rules.required]"
                                                    placeholder="Il tuo nome"></v-text-field>
                                            </v-col>

                                            <v-col cols="12" md="6">
                                                <v-text-field v-model="form.email" label="Email" outlined
                                                    color="primary" :rules="[rules.required, rules.email]"
                                                    placeholder="tua@email.com"></v-text-field>
                                            </v-col>
                                        </v-row>

                                        <v-text-field v-model="form.subject" label="Oggetto" outlined color="primary"
                                            :rules="[rules.required]" placeholder="Di cosa vuoi parlare?"
                                            class="mb-4"></v-text-field>

                                        <v-textarea v-model="form.message" label="Messaggio" outlined color="primary"
                                            :rules="[rules.required]" placeholder="Scrivi il tuo messaggio qui..."
                                            rows="6" class="mb-6"></v-textarea>

                                        <v-btn type="submit" color="primary" large depressed class="submit-btn"
                                            :loading="isSubmitting">
                                            Invia messaggio
                                            <v-icon right>mdi-send</v-icon>
                                        </v-btn>
                                    </v-form>
                                </v-col>
                            </v-row>
                        </v-card>
                    </v-col>
                </v-row>
            </v-container>
            <span class="text-caption grey--text text--darken-1 mx-auto copyright"
                style="position: fixed; bottom: 10px; left: 50%; transform: translateX(-50%); font-size: 0.8rem; color: #6d4c41;">
                © {{ new Date().getFullYear() }} Do!t. Tutti i diritti riservati.
            </span>
        </v-main>

        <!-- Footer -->

    </v-app>
</template>

<script>
export default {
    middlewares: 'auth',

    data() {
        return {
            form: {
                name: '',
                email: '',
                subject: '',
                message: ''
            },
            rules: {
                required: value => !!value || 'Campo obbligatorio.',
                email: value => {
                    const pattern = /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
                    return pattern.test(value) || 'Email non valida'
                }
            },
            isSubmitting: false
        }
    },
    methods: {
        submitForm() {
            if (this.$refs.form.validate()) {
                this.isSubmitting = true

                setTimeout(() => {
                    this.$toast.success('Messaggio inviato con successo! Ti risponderemo al più presto.')
                    this.$refs.form.reset()
                    this.isSubmitting = false
                }, 1500)
            }
        }
    }
}
</script>

<style scoped>
* {
    font-family: "Uto-Bold", sans-serif !important;
}
.text-shadow {
    text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.3);
}

.v-main {
    overflow-y: auto !important;
    height: 100vh;
}

.contact-card {
    overflow: hidden;
}

.contact-info {
    background: linear-gradient(135deg, #c45f00 0%, #c45f00 100%);
    color: white;
    padding: 40px;
    border-radius: 12px 0 0 12px;
    position: relative;
    overflow: hidden;
}

.contact-info::before {
    content: "";
    position: absolute;
    top: -50px;
    right: -50px;
    width: 200px;
    height: 200px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
}

.contact-info::after {
    content: "";
    position: absolute;
    bottom: -80px;
    left: -30px;
    width: 250px;
    height: 250px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.05);
}

.contact-form {
    padding: 40px;
    background: white;
    border-radius: 0 12px 12px 0;
}

.contact-text {
    color: rgba(255, 255, 255, 0.9);
    font-size: 16px;
}

.contact-details p {
    color: rgba(255, 255, 255, 0.8);
    margin-bottom: 0;
}

.social-links {
    display: flex;
    gap: 10px;
}

.social-icon {
    transition: all 0.3s ease;
}

.social-icon:hover {
    transform: translateY(-5px);
    background: #ffd700 !important;
}

.social-icon:hover .v-icon {
    color: #1e3c72 !important;
}

.submit-btn {
    letter-spacing: 0.5px;
    font-weight: 600;
    text-transform: none;
    transition: all 0.3s ease;
}

.submit-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 15px rgba(30, 60, 114, 0.3) !important;
}

/* Animazioni */
@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.contact-info>* {
    animation: fadeIn 0.6s ease forwards;
}

.contact-form>* {
    animation: fadeIn 0.6s ease forwards;
    animation-delay: 0.2s;
}

.contact-item:nth-child(1) {
    animation-delay: 0.1s;
}

.contact-item:nth-child(2) {
    animation-delay: 0.2s;
}

.contact-item:nth-child(3) {
    animation-delay: 0.3s;
}

/* Responsive */
@media (max-width: 960px) {
    .contact-info {
        border-radius: 12px 12px 0 0;
    }

    .contact-form {
        border-radius: 0 0 12px 12px;
    }
}

@media (max-width: 600px) {

    .contact-info,
    .contact-form {
        padding: 30px;
    }
}
</style>

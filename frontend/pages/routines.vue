<template>
    <v-app>
        <NavBar />
        <v-main  style="background-color: #fdf3e4;">
            <!-- Header -->
            <v-container class="py-8">
                <v-row class="mb-8">
                    <v-col cols="12" class="text-center">
                        <h1 class="text-h3 font-weight-bold primary--text">
                            <v-icon large left color="primary">mdi-reload</v-icon>
                            Routine Giornaliere
                        </h1>
                        <p class="text-subtitle-1 mt-3 grey--text">
                            Traccia le tue abitudini
                        </p>
                    </v-col>
                </v-row>

                <!-- Sezione Calendario -->
                <v-row>
                    <v-col cols="12" md="8" offset-md="2">
                        <v-card elevation="4" class="calendar-card">
                            <v-card-title class="primary white--text">
                                <v-icon left>mdi-calendar-month</v-icon>
                                Calendario Mensile
                            </v-card-title>
                            <v-card-text class="pa-0">
                                <v-calendar ref="calendar" v-model="currentDate" type="month"
                                    :event-color="getEventColor" @click:day="showDayDetails" color="primary"
                                    locale="it-IT">
                                    <template #day="{ date }">
                                        <div class="v-calendar-day" :class="{ 'today': date === today }">
                                            <div class="day-number">{{ new Date(date).getDate() }}</div>
                                            <div class="event-chips">
                                                <v-chip v-for="event in eventsForDate(date)" :key="event.id" small label
                                                    :color="getEventColor(event)" dark class="mb-1 chip-title">
                                                    {{ event.title }}
                                                </v-chip>
                                            </div>
                                        </div>
                                    </template>
                                </v-calendar>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>

                <!-- Sezione Dettaglio Giorno -->
                <v-row v-if="selectedDayRoutines.length > 0" class="mt-6">
                    <v-col cols="12" md="6" offset-md="3">
                        <v-card elevation="4" class="day-details-card">
                            <v-card-title class="secondary white--text">
                                <v-icon left>mdi-calendar-check</v-icon>
                                Routine del {{ formatDisplayDate(selectedDay) }}
                            </v-card-title>
                            <v-card-text>
                                <v-list>
                                    <v-list-item v-for="routine in selectedDayRoutines" :key="routine.id"
                                        class="routine-item"
                                        :class="{ 'completed': isCompleted(routine, selectedDay) }">
                                        <v-list-item-content>
                                            <v-list-item-title>{{ routine.title }}</v-list-item-title>
                                            <v-list-item-subtitle>
                                                <v-chip small :color="frequencyColor(routine.frequency)" dark>
                                                    {{ frequencyLabel(routine.frequency) }}
                                                </v-chip>
                                            </v-list-item-subtitle>
                                        </v-list-item-content>
                                        <v-list-item-action>
                                            <v-checkbox :input-value="isCompleted(routine, selectedDay)"
                                                :disabled="!isToday(selectedDay) || isCompleted(routine, selectedDay)"
                                                color="success" @click="markAsDone(routine.id)"></v-checkbox>
                                        </v-list-item-action>
                                    </v-list-item>
                                </v-list>
                            </v-card-text>
                        </v-card>
                    </v-col>
                </v-row>

                <!-- Sezione Routine -->
                <v-row class="mt-8">
                    <v-col cols="12" md="6" offset-md="3" >
                        <v-slide-group show-arrows center-active>
                            <v-slide-item v-for="routine in activeRoutines" :key="routine.id">
                                <v-card class="routine-card mx-2" :class="routineClass(routine)" elevation="6"
                                    width="300" height="380">
                                    <v-card-title class="routine-title white--text">
                                        {{ routine.title }}
                                    </v-card-title>
                                    <v-card-subtitle class="routine-frequency py-1">
                                        <v-chip :color="frequencyColor(routine.frequency)" dark small
                                            class="font-weight-bold">
                                            {{ frequencyLabel(routine.frequency) }}
                                        </v-chip>
                                    </v-card-subtitle>
                                    <v-card-text class="pt-3">
                                        <p class="routine-description mb-4">{{ routine.description }}</p>
                                        <div class="routine-info">
                                            <p><strong>Prossima scadenza:</strong> {{ formatDate(routine.next_due) }}
                                            </p>

                                            <v-alert v-if="isOverdue(routine.next_due)" type="error" dense class="mt-3">
                                                Non eseguita!
                                            </v-alert>
                                        </div>
                                    </v-card-text>
                                    <v-card-actions class="justify-end pa-3">
                                        
                                        <v-btn icon @click="openDeleteDialog(routine.id)">
                                            <v-icon color="error">mdi-delete</v-icon>
                                        </v-btn>
                                    </v-card-actions>
                                </v-card>
                            </v-slide-item>
                        </v-slide-group>
                    </v-col>
                </v-row>

                <!-- Pulsante Aggiungi -->
                <v-btn fab dark color="primary" fixed bottom right class="mb-10 mr-10 elevation-12"
                    @click="addDialog = true">
                    <v-icon>mdi-plus</v-icon>
                </v-btn>

                <!-- Dialogo Aggiungi Routine -->
                <v-dialog v-model="addDialog" max-width="600">
                    <v-card class="rounded-lg">
                        <v-card-title class="headline success white--text">
                            <v-icon large left>mdi-plus-box</v-icon>
                            Nuova Routine
                        </v-card-title>
                        <v-card-text class="pa-4">
                            <v-form ref="form" v-model="valid">
                                <v-text-field v-model="newRoutine.title" label="Titolo della routine"
                                    :rules="[v => !!v || 'Il titolo è obbligatorio']" required outlined
                                    class="mb-4"></v-text-field>
                                <v-textarea v-model="newRoutine.description" label="Descrizione (opzionale)" outlined
                                    rows="2" class="mb-4"></v-textarea>
                                <v-select v-model="newRoutine.frequency" :items="frequencyOptions" label="Frequenza"
                                    item-text="text" item-value="value" outlined class="mb-4"></v-select>
                                <v-menu v-model="startDateMenu" :close-on-content-click="false" :nudge-right="40"
                                    transition="scale-transition" offset-y min-width="auto">
                                    <template v-slot:activator="{ on, attrs }">
                                        <v-text-field v-model="newRoutine.start_date" label="Data di inizio"
                                            prepend-icon="mdi-calendar" readonly v-bind="attrs" v-on="on"
                                            :rules="[v => !!v || 'La data di inizio è obbligatoria']"
                                            required></v-text-field>
                                    </template>
                                    <v-date-picker v-model="newRoutine.start_date" @input="startDateMenu = false"
                                        locale="it-IT"></v-date-picker>
                                </v-menu>
                            </v-form>
                        </v-card-text>
                        <v-card-actions class="pa-4 d-flex justify-end">
                            <v-btn text @click="addDialog = false">Annulla</v-btn>
                            <v-btn color="success" @click="saveRoutine" :disabled="!valid" depressed>
                                Salva
                            </v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>

                <v-dialog v-model="deleteDialog" max-width="500">
                    <v-card>
                        <v-card-title class="headline">Conferma eliminazione</v-card-title>
                        <v-card-text>
                            Sei sicuro di voler eliminare la routine "{{ selectedRoutineTitle }}"?
                            Tutti i dati associati andranno persi.
                        </v-card-text>
                        <v-card-actions>
                            <v-spacer></v-spacer>
                            <v-btn text @click="deleteDialog = false">Annulla</v-btn>
                            <v-btn color="error" @click="confirmDelete" depressed>Elimina</v-btn>
                        </v-card-actions>
                    </v-card>
                </v-dialog>

                <!-- Snackbar -->
                <v-snackbar v-model="snackbar" :color="snackbarColor" timeout="3000" top>
                    {{ snackbarText }}
                    <template v-slot:action="{ attrs }">
                        <v-btn text v-bind="attrs" @click="snackbar = false">Chiudi</v-btn>
                    </template>
                </v-snackbar>
            </v-container>
        </v-main>
    </v-app>
</template>

<script>
export default {
    middleware: 'auth',
    data() {
        return {
            routines: [],
            loading: true,
            deleteDialog: false,
            selectedRoutineId: null,
            snackbar: false,
            snackbarText: '',
            snackbarColor: 'success',
            addDialog: false,
            valid: false,
            saving: false,
            newRoutine: {
                title: '',
                description: '',
                frequency: 'daily',
                start_date: new Date().toISOString().substr(0, 10)
            },
            frequencyOptions: [
                { text: 'Giornaliero', value: 'daily' },
                { text: 'Ogni 2 giorni', value: 'every_2_days' },
                { text: 'Settimanale', value: 'weekly' },
                { text: 'Mensile', value: 'monthly' }
            ],
            currentDate: new Date().toISOString().substr(0, 10),
            today: new Date().toISOString().substr(0, 10),
            selectedDay: new Date().toISOString().substr(0, 10),
            selectedDayRoutines: [],
            selectedRoutineTitle: '',
            startDateMenu: false,
        };
    },
    computed: {
        activeRoutines() {
            return this.routines.map(routine => {
                const completions = JSON.parse(routine.completions || '[]');
                return {
                    ...routine,
                    completed: completions.includes(this.today),
                    completions
                };
            });
        },
        routineEvents() {
            const events = [];
            this.routines.forEach(r => {
                const schedule = this.calculateSchedule(r);
                schedule.forEach(date => {
                    events.push({
                        id: r.id,
                        title: r.title,
                        start: date,
                        end: date,
                        color: this.frequencyColor(r.frequency)
                    });
                });
            });
            return events;
        }
    },
    async mounted() {
        await this.fetchRoutines();
        this.updateDayRoutines(this.today);
    },
    methods: {
        async fetchRoutines() {
            try {
                const res = await this.$axios.get('http://localhost:8000/api/routines');
                this.routines = res.data;
            } catch (error) {
                console.error('Error fetching routines:', error);
                this.showSnackbar('Errore nel caricamento delle routine', 'error');
            } finally {
                this.loading = false;
            }
        },

        isCompleted(routine, date) {
            const completions = this.parseCompletions(routine.completions);
            return completions.includes(date);
        },
        isCompletedToday(routine) {
            return this.isCompleted(routine, this.today);
        },
        parseCompletions(completions) {
            try {
                if (Array.isArray(completions)) return completions;

                if (typeof completions === 'string') {
                    const parsed = JSON.parse(completions);
                    return Array.isArray(parsed) ? parsed : [];
                }

                return [];
            } catch (e) {
                console.error("Errore parsing completions:", e);
                return [];
            }
        },

        calculateSchedule(routine) {
            if (!routine.start_date) return [];

            const startDate = new Date(routine.start_date.length > 10
            ? routine.start_date
            : routine.start_date + 'T00:00:00');

            if (isNaN(startDate.getTime())) return [];

            const year = new Date(this.currentDate).getFullYear();
            const month = new Date(this.currentDate).getMonth();
            const daysInMonth = new Date(year, month + 1, 0).getDate();
            const schedule = [];

            const startDateStr = startDate.toISOString().substr(0, 10);

            for (let day = 1; day <= daysInMonth; day++) {
            const currentDate = new Date(year, month, day);
            const currentDateStr = currentDate.toISOString().substr(0, 10);

            if (currentDateStr < startDateStr) continue;

            const daysDiff = Math.floor(
                (currentDate - startDate) / (1000 * 60 * 60 * 24)
            );

            let addEvent = false;
            switch (routine.frequency) {
                case 'daily':
                addEvent = true;
                break;
                case 'every_2_days':
                if (daysDiff % 2 === 0) addEvent = true;
                break;
                case 'weekly':
                if (currentDate.getDay() === startDate.getDay()) addEvent = true;
                break;
                case 'monthly':
                if (currentDate.getDate() === startDate.getDate()) addEvent = true;
                break;
            }
            if (addEvent) {
                // Add 1 day to the event
                const eventDate = new Date(currentDate);
                eventDate.setDate(eventDate.getDate() + 1);
                const eventDateStr = eventDate.toISOString().substr(0, 10);
                schedule.push(eventDateStr);
            }
            }
            return schedule;
        },

        getEventColor(event) {
            return event.color;
        },

        isToday(date) {
            return date === this.today;
        },

        updateDayRoutines(date) {
            this.selectedDay = date;
            this.selectedDayRoutines = this.routines.filter(r => {
                const schedule = this.calculateSchedule(r);
                return schedule.includes(date);
            });
        },

        showDayDetails({ date }) {
            this.updateDayRoutines(date);
        },

        async markAsDone(routineId) {
            try {
                await this.$axios.post(`http://localhost:8000/api/routines/${routineId}/complete`);
                await this.fetchRoutines();
                this.updateDayRoutines(this.selectedDay);
                this.showSnackbar("Routine completata con successo", "success");
            } catch (error) {
                console.error('Error completing routine:', error);
                this.showSnackbar("Errore durante il completamento", "error");
            }
        },

        showSnackbar(text, color) {
            this.snackbarText = text;
            this.snackbarColor = color;
            this.snackbar = true;
        },

        frequencyLabel(frequency) {
            const labels = {
                daily: 'Giornaliera',
                every_2_days: 'Ogni 2 giorni',
                weekly: 'Settimanale',
                monthly: 'Mensile'
            };
            return labels[frequency] || 'Sconosciuta';
        },

        frequencyColor(frequency) {
            const colors = {
                daily: 'blue',
                every_2_days: 'indigo',
                weekly: 'deep-purple',
                monthly: 'teal'
            };
            return colors[frequency] || 'gray';
        },

        routineClass(routine) {
            return {
                'overdue': this.isOverdue(routine.next_due),
                'daily-routine': routine.frequency === 'daily',
                'every-2-days-routine': routine.frequency === 'every_2_days',
                'weekly-routine': routine.frequency === 'weekly',
                'monthly-routine': routine.frequency === 'monthly'
            };
        },

        isOverdue(nextDue) {
            if (!nextDue) return false;
            const now = new Date();
            const dueDate = new Date(nextDue);
            return dueDate < now;
        },

        formatDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString('it-IT', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },

        formatDisplayDate(date) {
            if (!date) return '';
            return new Date(date).toLocaleDateString('it-IT', {
                weekday: 'long',
                day: 'numeric',
                month: 'long'
            });
        },

        async saveRoutine() {
            if (!this.$refs.form.validate()) return;
            this.saving = true;
            try {
                const response = await this.$axios.post('http://localhost:8000/api/routines', {
                    title: this.newRoutine.title,
                    description: this.newRoutine.description,
                    frequency: this.newRoutine.frequency,
                    start_date: this.newRoutine.start_date,
                });
                this.routines.push(response.data);
                this.showSnackbar('Routine creata con successo', 'success');
                this.addDialog = false;
 await this.fetchRoutines();
        this.updateDayRoutines(this.today);            } catch (error) {
                console.error('Error creating routine:', error);
                this.showSnackbar("Errore durante il salvataggio", 'error');
            } finally {
                this.saving = false;
            }
        },

        openDeleteDialog(id) {
            const routine = this.routines.find(r => r.id === id);
            if (routine) {
                this.selectedRoutineId = id;
                this.selectedRoutineTitle = routine.title;
                this.deleteDialog = true;
            }
        },

        async confirmDelete() {
            if (!this.selectedRoutineId) return;
            try {
                await this.$axios.delete(`http://localhost:8000/api/routines/${this.selectedRoutineId}`);
                this.routines = this.routines.filter(r => r.id !== this.selectedRoutineId);
                this.showSnackbar('Routine eliminata con successo', 'success');
                this.deleteDialog = false;
            } catch (error) {
                console.error('Error deleting routine:', error);
                this.showSnackbar('Errore durante l\'eliminazione', 'error');
            }
        },

        eventsForDate(date) {
            return this.routineEvents.filter(event => event.start === date);
        }
    }
};
</script>

<style scoped>
* {
    font-family: 'Uto-Bold', sans-serif !important;
}

.v-main {
    overflow-y: auto !important;
    height: 100vh;
}

.calendar-card {
    border-radius: 12px;
    overflow: visible;
}

.completed {
    opacity: 0.7;
    background-color: #e8f5e9;
}

.completed .v-list-item__title {
    text-decoration: line-through;
}

.v-calendar-day {
    position: relative;
    height: 100px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.v-calendar-day:hover {
    background-color: rgba(0, 0, 0, 0.05);
}

.v-calendar-day.today {
    background-color: rgba(0, 255, 0, 0.1);
}

.day-number {
    font-size: 1rem;
    font-weight: bold;
    margin-bottom: 4px;
}

.event-list {
    max-height: 60px;
    overflow-y: auto;
}

.event-list::-webkit-scrollbar {
    width: 4px;
}


.event-list::-webkit-scrollbar-thumb {
    background: #4caf50;
    border-radius: 4px;
}

.event-chips {
    max-height: 70px;
    overflow-y: auto;
    padding: 2px 0;
}

.chip-title {
    font-size: 0.65rem !important;
    height: 20px !important;
    padding: 0 6px !important;
    margin: 1px 0 !important;
    display: block;
    width: 100%;
    text-overflow: ellipsis;
    overflow: hidden;
    white-space: nowrap;
}

.day-number {
    font-weight: bold;
    text-align: right;
    padding-right: 4px;
    font-size: 0.9rem;
    margin-bottom: 2px;
}

/* Scrollbar personalizzata */
.event-chips::-webkit-scrollbar {
    width: 3px;
}

.event-chips::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 2px;
}

.routine-card {
    border-radius: 16px;
    margin: 12px;
    transition: transform 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
}

.routine-card:hover {
    transform: translateY(-12px) scale(1.02);
    z-index: 3;
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
}

.routine-title {
    font-size: 1.3rem !important;
    background: linear-gradient(145deg, #2196f3, #1976d2);
    color: white !important;
}

.routine-description {
    font-size: 0.95rem;
    color: #555;
}

.routine-item {
    border-left: 4px solid;
    transition: all 0.3s;
}

.routine-item .v-chip {
    font-size: 0.75rem;
}

.v-list-item__action {
    align-self: center;
}

.v-calendar-day:hover .event-list .v-chip {
    transform: scale(1.05);
}

/* Colori avanzati per le routine */
.daily-routine .routine-item {
    border-left-color: #2196f3;
}

.every-2-days-routine .routine-item {
    border-left-color: #3f51b5;
}

.weekly-routine .routine-item {
    border-left-color: #9c27b0;
}

.monthly-routine .routine-item {
    border-left-color: #4caf50;
}

.overdue {
    opacity: 0.85;
    position: relative;
    overflow: visible;
}

.overdue::after {
    content: "SCADUTA";
    position: absolute;
    top: 5px;
    right: -30px;
    background: #ff5252;
    color: white;
    padding: 2px 8px;
    font-size: 0.7rem;
    font-weight: bold;
    transform: rotate(45deg);
    z-index: 2;
}

/* Stili avanzati per il calendario */
.v-calendar-monthly__day {
    min-height: 80px !important;
    padding: 4px;
}

.v-calendar-monthly__day-label {
    font-size: 0.8rem;
    font-weight: 600;
    color: #2c3e50;
}

.v-calendar-monthly__day:nth-child(-n+7) {
    background-color: #f8f9fa !important;
}

.v-calendar-monthly__day--outside {
    opacity: 0.4;
}

.v-calendar-day__events {
    margin-top: 4px;
}

.v-calendar-day__event {
    font-size: 0.7rem;
    padding: 2px 4px;
    border-radius: 4px;
}

@media (max-width: 600px) {
    .v-calendar-monthly__day {
        min-height: 60px !important;
    }
}
</style>
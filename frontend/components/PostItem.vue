<template>
    <v-card elevation="3" class="mb-4">
        <v-card-text>
            <div class="d-flex">
                <v-avatar size="40" color="orange" class="mr-4">
                    <span class="white-text">{{ post.authorInitials }}</span>
                </v-avatar>
                <div class="flex-grow-1">
                    <div class="d-flex align-center">
                        <strong>{{ post.author }}</strong>
                        <span class="caption grey--text ml-2">{{ formatDate(post.date) }}</span>
                    </div>
                    <div class="mt-2" v-html="post.content"></div>

                    <div class="mt-3 d-flex align-center">
                        <v-btn icon x-small @click="likePost(post)">
                            <v-icon :color="post.likedByUser ? 'red' : 'grey'">mdi-heart</v-icon>
                        </v-btn>
                        <span class="caption mr-4">{{ post.likes }}</span>

                        <v-btn icon x-small @click="toggleReply">
                            <v-icon>mdi-reply</v-icon>
                        </v-btn>
                        <span class="caption">Rispondi</span>
                    </div>

                    <!-- Form risposta annidata -->
                    <div v-if="showReplyForm" class="mt-3">
                        <v-textarea v-model="replyContent" label="Scrivi una risposta..." outlined rows="2"
                            auto-grow></v-textarea>
                        <v-btn color="orange darken-3" dark small @click="submitReply">Invia Risposta</v-btn>
                    </div>

                    <!-- Risposte annidate (ricorsione) -->
                    <div v-if="post.replies && post.replies.length > 0" class="mt-4 ml-8">
                        <post-item v-for="reply in post.replies" :key="reply.id" :post="reply" :thread="thread"
                            @reply-submitted="$emit('reply-submitted', $event)" />
                    </div>
                </div>
            </div>
        </v-card-text>
    </v-card>
</template>

<script>
export default {
    props: {
        post: Object,
        thread: Object
    },
    data() {
        return {
            showReplyForm: false,
            replyContent: ''
        }
    },
    methods: {
        toggleReply() {
            if (!this.thread.closed) {
                this.showReplyForm = !this.showReplyForm;
            }
        },
        submitReply() {
            if (!this.replyContent.trim()) return;

            this.$emit('reply-submitted', {
                parentId: this.post.id,
                content: this.replyContent
            });

            this.replyContent = '';
            this.showReplyForm = false;
        },
        formatDate(dateString) {
            const options = { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }
            return new Date(dateString).toLocaleDateString('it-IT', options)
        },
        async likePost(post) {
            try {
                if (post.likedByUser) {
                    // Dislike
                    await this.$axios.delete('http://localhost:8000/api/likes', {
                        data: {
                            likeable_type: 'posts',
                            likeable_id: post.id
                        },
                        headers: {
                            Authorization: `Bearer ${this.$auth.strategy.token.get()}`
                        }
                    });
                    post.likes--;
                    post.likedByUser = false;
                } else {
                    // Like
                    await this.$axios.post('http://localhost:8000/api/likes', {
                        likeable_type: 'posts',
                        likeable_id: post.id
                    }, {
                        headers: {
                            Authorization: `Bearer ${this.$auth.strategy.token.get()}`
                        }
                    });
                    post.likes++;
                    post.likedByUser = true;
                }
            } catch (error) {
                console.error('Errore durante il voto:', error.response?.data || error.message);
                this.$toast.error('Errore durante il voto');
            }
        }
    }
}
</script>

<style scoped>
.white-text {
    color: white !important;
}
</style>
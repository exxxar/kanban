<template>
    <div>
        <div v-if="store.loading">Загрузка комментариев...</div>

        <div
            v-for="comment in store.comments"
            :key="comment.id"
            class="comment-item"
        >
            <div class="comment-header">
                <strong>{{ comment.author || 'Без автора' }}</strong>
                <span class="date">
          {{ new Date(comment.created_at).toLocaleString() }}
        </span>
            </div>

            <div class="comment-text" v-if="comment.text">
                {{ comment.text }}
            </div>

            <div class="attachments" v-if="comment.attachments?.length">
                <div
                    v-for="file in comment.attachments"
                    :key="file.path"
                    class="attachment"
                >
                    <!-- Изображения -->
                    <img
                        v-if="file.mime.startsWith('image/')"
                        :src="`/storage/${file.path}`"
                        class="preview-image"
                    />

                    <!-- Видео -->
                    <video
                        v-else-if="file.mime.startsWith('video/')"
                        controls
                        class="preview-video"
                    >
                        <source :src="`/storage/${file.path}`" :type="file.mime" />
                    </video>

                    <!-- Аудио -->
                    <audio
                        v-else-if="file.mime.startsWith('audio/')"
                        controls
                    >
                        <source :src="`/storage/${file.path}`" :type="file.mime" />
                    </audio>

                    <!-- Документы -->
                    <a
                        v-else
                        :href="`/storage/${file.path}`"
                        target="_blank"
                        class="doc-link"
                    >
                        📄 {{ file.name }}
                    </a>
                </div>
            </div>

            <hr />
        </div>
    </div>
</template>

<script>
import { useCommentsStore } from '@/stores/useCommentsStore'

export default {
    name: 'CommentsList',

    props: {
        taskId: { type: Number, required: true }
    },

    data() {
        return {
            store: useCommentsStore()
        }
    },

    mounted() {
        this.store.fetchComments(this.taskId)
    }
}
</script>

<style scoped>
.preview-image {
    max-width: 200px;
    border-radius: 6px;
    margin-top: 6px;
}
.preview-video {
    max-width: 300px;
    margin-top: 6px;
}
.doc-link {
    display: block;
    margin-top: 6px;
}
.comment-item {
    margin-bottom: 20px;
}
.comment-header {
    display: flex;
    justify-content: space-between;
}
</style>

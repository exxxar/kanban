<template>
    <div>
        <div v-if="store.loading">Загрузка вложений...</div>

        <div v-for="file in store.attachments" :key="file.path" class="attachment">

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
</template>

<script>
import { useTaskAttachmentsStore } from '@/stores/useTaskAttachmentsStore'

export default {
    name: 'TaskAttachmentsList',

    props: {
        taskId: { type: Number, required: true }
    },

    data() {
        return {
            store: useTaskAttachmentsStore()
        }
    },

    mounted() {
        this.store.fetch(this.taskId)
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
</style>

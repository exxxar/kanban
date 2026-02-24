<template>
    <div class="attachment-upload">

        <input
            type="file"
            multiple
            @change="onFilesSelected"
            class="form-control"
        />

        <div v-if="files.length" class="preview-list">
            <div v-for="file in files" :key="file.name" class="preview-item">
                {{ file.name }}
            </div>
        </div>

        <button class="btn btn-primary mt-2" @click="upload">
            Загрузить файлы
        </button>

    </div>
</template>

<script>
import { useTaskAttachmentsStore } from '@/stores/useTaskAttachmentsStore'

export default {
    name: 'TaskAttachmentsUpload',

    props: {
        taskId: { type: Number, required: true }
    },

    data() {
        return {
            files: [],
            store: useTaskAttachmentsStore()
        }
    },

    methods: {
        onFilesSelected(e) {
            this.files = Array.from(e.target.files)
        },

        async upload() {
            await this.store.upload(this.taskId, this.files)
            this.files = []
        }
    }
}
</script>

<style scoped>
.preview-item {
    font-size: 14px;
    margin-top: 4px;
}
</style>

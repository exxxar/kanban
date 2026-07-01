<template>
    <div class="attachment-upload">
        <!-- Заголовок -->
        <div class="upload-header">
            <div class="header-icon">
                <i class="fa-solid fa-cloud-arrow-up"></i>
            </div>
            <div class="header-info">
                <h4 class="header-title">Загрузить вложения</h4>
                <p class="header-subtitle">Перетащите файлы или выберите вручную</p>
            </div>
        </div>

        <!-- Drop Zone -->
        <div
            class="drop-zone"
            :class="{
                'drag-over': isDragOver,
                'has-files': files.length > 0
            }"
            @dragover.prevent="onDragOver"
            @dragleave="onDragLeave"
            @drop.prevent="onDrop"
            @click="triggerFileInput"
        >
            <input
                ref="fileInput"
                type="file"
                multiple
                @change="onFilesSelected"
                class="file-input-hidden"
            />

            <div class="drop-zone-content">
                <div class="drop-icon">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                </div>
                <div class="drop-text">
                    <p class="drop-title">
                        {{ isDragOver ? 'Отпустите файлы здесь' : 'Перетащите файлы сюда' }}
                    </p>
                    <p class="drop-hint">
                        или <span class="drop-link">выберите вручную</span>
                    </p>
                    <p class="drop-formats">
                        Поддерживаются: изображения, PDF, документы, видео, аудио
                    </p>
                </div>
            </div>
        </div>

        <!-- Выбранные файлы -->
        <Transition name="expand">
            <div v-if="files.length > 0" class="selected-files-section">
                <div class="selected-header">
                    <span class="selected-title">
                        Выбрано файлов: {{ files.length }}
                    </span>
                    <button
                        class="btn-clear-all"
                        @click="clearFiles"
                        title="Очистить все"
                    >
                        <i class="fa-solid fa-trash-can"></i>
                        <span>Очистить</span>
                    </button>
                </div>

                <div class="selected-files-list">
                    <TransitionGroup name="file-list">
                        <div
                            v-for="(file, idx) in files"
                            :key="file.name + file.size + idx"
                            class="selected-file-item"
                        >
                            <div class="file-icon" :class="getFileIconClass(file)">
                                <i :class="getFileIcon(file)"></i>
                            </div>
                            <div class="file-info">
                                <div class="file-name" :title="file.name">
                                    {{ file.name }}
                                </div>
                                <div class="file-size">
                                    {{ formatSize(file.size) }}
                                </div>
                            </div>
                            <button
                                class="btn-remove-file"
                                @click="removeFile(idx)"
                                title="Удалить из списка"
                            >
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                    </TransitionGroup>
                </div>
            </div>
        </Transition>

        <!-- Кнопка загрузки -->
        <Transition name="expand">
            <div v-if="files.length > 0" class="upload-actions">
                <button
                    class="btn-upload"
                    :disabled="isUploading"
                    @click="upload"
                >
                    <span v-if="isUploading" class="upload-spinner"></span>
                    <i v-else class="fa-solid fa-upload"></i>
                    <span>{{ isUploading ? 'Загрузка...' : `Загрузить ${files.length} файл(ов)` }}</span>
                </button>
            </div>
        </Transition>

        <!-- Toast уведомление -->
        <Transition name="toast">
            <div v-if="toast.show" class="upload-toast" :class="toast.type">
                <i :class="toast.icon"></i>
                <span>{{ toast.message }}</span>
            </div>
        </Transition>
    </div>
</template>

<script>
import { useTaskAttachmentsStore } from '@/stores/useTaskAttachmentsStore'

export default {
    name: 'TaskAttachmentsUpload',

    props: {
        taskId: { type: Number, required: true }
    },

    emits: ['uploaded'],

    data() {
        return {
            files: [],
            isUploading: false,
            isDragOver: false,
            store: useTaskAttachmentsStore(),
            toast: {
                show: false,
                message: '',
                type: 'success',
                icon: 'fa-solid fa-check-circle'
            }
        }
    },

    methods: {
        // === FILE INPUT ===
        triggerFileInput() {
            this.$refs.fileInput?.click()
        },

        onFilesSelected(e) {
            const selectedFiles = Array.from(e.target.files)
            this.addFiles(selectedFiles)
        },

        // === DRAG & DROP ===
        onDragOver(e) {
            this.isDragOver = true
        },

        onDragLeave() {
            this.isDragOver = false
        },

        onDrop(e) {
            this.isDragOver = false
            const droppedFiles = Array.from(e.dataTransfer.files)
            this.addFiles(droppedFiles)
        },

        // === FILES MANAGEMENT ===
        addFiles(newFiles) {
            // Фильтруем дубликаты по имени и размеру
            const uniqueFiles = newFiles.filter(newFile => {
                return !this.files.some(
                    existing =>
                        existing.name === newFile.name &&
                        existing.size === newFile.size
                )
            })

            this.files.push(...uniqueFiles)

            if (uniqueFiles.length < newFiles.length) {
                this.showToast('Некоторые файлы уже добавлены', 'warning')
            }
        },

        removeFile(index) {
            this.files.splice(index, 1)
        },

        clearFiles() {
            this.files = []
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = ''
            }
        },

        // === UPLOAD ===
        async upload() {
            if (!this.files.length) return

            this.isUploading = true

            try {
                const response = await this.store.upload(this.taskId, this.files)
                this.$emit('uploaded', response)

                this.showToast(
                    `Успешно загружено ${this.files.length} файл(ов)`,
                    'success'
                )

                this.clearFiles()
            } catch (error) {
                console.error('Ошибка при загрузке файлов:', error)
                this.showToast('Не удалось загрузить файлы', 'error')
            } finally {
                this.isUploading = false
            }
        },

        // === HELPERS ===
        formatSize(bytes) {
            if (!bytes) return '0 B'
            const k = 1024
            const sizes = ['B', 'KB', 'MB', 'GB']
            const i = Math.floor(Math.log(bytes) / Math.log(k))
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i]
        },

        getFileIcon(file) {
            const name = file.name.toLowerCase()
            const type = file.type

            if (type.startsWith('image/')) return 'fa-solid fa-image'
            if (type === 'application/pdf' || name.endsWith('.pdf')) return 'fa-solid fa-file-pdf'
            if (type.includes('word') || name.endsWith('.doc') || name.endsWith('.docx')) return 'fa-solid fa-file-word'
            if (type.startsWith('text/') || name.endsWith('.txt') || name.endsWith('.log')) return 'fa-solid fa-file-lines'
            if (type.startsWith('video/')) return 'fa-solid fa-file-video'
            if (type.startsWith('audio/')) return 'fa-solid fa-file-audio'
            return 'fa-solid fa-file'
        },

        getFileIconClass(file) {
            const name = file.name.toLowerCase()
            const type = file.type

            if (type.startsWith('image/')) return 'icon-image'
            if (type === 'application/pdf' || name.endsWith('.pdf')) return 'icon-pdf'
            if (type.includes('word') || name.endsWith('.doc') || name.endsWith('.docx')) return 'icon-word'
            if (type.startsWith('text/') || name.endsWith('.txt') || name.endsWith('.log')) return 'icon-text'
            if (type.startsWith('video/')) return 'icon-video'
            if (type.startsWith('audio/')) return 'icon-audio'
            return 'icon-default'
        },

        showToast(message, type = 'success') {
            const icons = {
                success: 'fa-solid fa-check-circle',
                error: 'fa-solid fa-circle-exclamation',
                warning: 'fa-solid fa-triangle-exclamation'
            }

            this.toast = {
                show: true,
                message,
                type,
                icon: icons[type]
            }

            setTimeout(() => {
                this.toast.show = false
            }, 3000)
        }
    }
}
</script>

<style scoped>
.attachment-upload {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* === HEADER === */
.upload-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 12px;
    border-bottom: 2px solid #f1f3f5;
}

.header-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

.header-title {
    font-size: 15px;
    font-weight: 700;
    color: #212529;
    margin: 0 0 2px 0;
}

.header-subtitle {
    font-size: 11px;
    color: #6c757d;
    margin: 0;
}

/* === DROP ZONE === */
.drop-zone {
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    padding: 32px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.drop-zone:hover {
    border-color: #0d6efd;
    background: #f0f7ff;
}

.drop-zone.drag-over {
    border-color: #0d6efd;
    background: #e7f1ff;
    border-style: solid;
    transform: scale(1.02);
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
}

.drop-zone.has-files {
    padding: 20px;
}

.file-input-hidden {
    display: none;
}

.drop-zone-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
}

.drop-icon {
    width: 64px;
    height: 64px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e7f1ff 0%, #cfe2ff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: #0d6efd;
    transition: all 0.3s ease;
}

.drop-zone:hover .drop-icon {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(13, 110, 253, 0.2);
}

.drop-zone.drag-over .drop-icon {
    animation: bounce 0.6s ease infinite;
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-8px); }
}

.drop-text {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.drop-title {
    font-size: 15px;
    font-weight: 600;
    color: #212529;
    margin: 0;
}

.drop-hint {
    font-size: 13px;
    color: #6c757d;
    margin: 0;
}

.drop-link {
    color: #0d6efd;
    font-weight: 600;
    text-decoration: underline;
}

.drop-formats {
    font-size: 11px;
    color: #adb5bd;
    margin: 4px 0 0 0;
}

/* === SELECTED FILES === */
.selected-files-section {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
}

.selected-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: #ffffff;
    border-bottom: 1px solid #e9ecef;
}

.selected-title {
    font-size: 13px;
    font-weight: 600;
    color: #495057;
}

.btn-clear-all {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border: 1px solid #e9ecef;
    background: #ffffff;
    color: #dc3545;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 12px;
    font-weight: 600;
}

.btn-clear-all:hover {
    background: #fff5f5;
    border-color: #fecaca;
}

.selected-files-list {
    padding: 12px;
    display: flex;
    flex-direction: column;
    gap: 8px;
    max-height: 300px;
    overflow-y: auto;
}

.selected-file-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 12px;
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    transition: all 0.2s;
}

.selected-file-item:hover {
    border-color: #dee2e6;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.file-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: white;
    flex-shrink: 0;
}

.icon-image { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
.icon-pdf { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
.icon-word { background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); }
.icon-text { background: linear-gradient(135deg, #6c757d 0%, #495057 100%); }
.icon-video { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
.icon-audio { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
.icon-default { background: linear-gradient(135deg, #adb5bd 0%, #868e96 100%); }

.file-info {
    flex: 1;
    min-width: 0;
}

.file-name {
    font-size: 13px;
    font-weight: 600;
    color: #212529;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    margin-bottom: 2px;
}

.file-size {
    font-size: 11px;
    color: #6c757d;
}

.btn-remove-file {
    width: 28px;
    height: 28px;
    border: none;
    background: transparent;
    color: #dc3545;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.btn-remove-file:hover {
    background: #fff5f5;
}

/* === UPLOAD BUTTON === */
.upload-actions {
    display: flex;
    justify-content: flex-end;
}

.btn-upload {
    padding: 12px 28px;
    border: none;
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: white;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
}

.btn-upload:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
}

.btn-upload:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.upload-spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* === TOAST === */
.upload-toast {
    position: fixed;
    bottom: 24px;
    right: 24px;
    padding: 14px 20px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 500;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 9999;
}

.upload-toast.success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.upload-toast.error {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
}

.upload-toast.warning {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
}

/* === АНИМАЦИИ === */
.expand-enter-active,
.expand-leave-active {
    transition: all 0.3s ease;
    overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
    opacity: 0;
    max-height: 0;
}

.expand-enter-to,
.expand-leave-from {
    max-height: 1000px;
}

.file-list-enter-active {
    transition: all 0.3s ease;
}

.file-list-leave-active {
    transition: all 0.2s ease;
}

.file-list-enter-from {
    opacity: 0;
    transform: translateX(-20px);
}

.file-list-leave-to {
    opacity: 0;
    transform: translateX(20px);
}

.file-list-move {
    transition: transform 0.3s ease;
}

.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from,
.toast-leave-to {
    opacity: 0;
    transform: translateY(20px);
}

/* === СКРОЛЛБАР === */
.selected-files-list::-webkit-scrollbar {
    width: 6px;
}

.selected-files-list::-webkit-scrollbar-track {
    background: #f1f3f5;
    border-radius: 3px;
}

.selected-files-list::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 3px;
}

.selected-files-list::-webkit-scrollbar-thumb:hover {
    background: #adb5bd;
}

/* === АДАПТИВ === */
@media (max-width: 768px) {
    .drop-zone {
        padding: 24px 16px;
    }

    .drop-icon {
        width: 56px;
        height: 56px;
        font-size: 24px;
    }

    .drop-title {
        font-size: 14px;
    }

    .btn-upload {
        width: 100%;
        justify-content: center;
    }
}
</style>

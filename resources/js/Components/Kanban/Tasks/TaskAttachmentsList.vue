<template>
    <div class="attachments-container">
        <!-- Заголовок -->
        <div class="attachments-header">
            <div class="header-icon">
                <i class="fa-solid fa-paperclip"></i>
            </div>
            <div class="header-info">
                <h4 class="header-title">Вложения</h4>
                <p class="header-subtitle">
                    {{ store.attachments.length > 0
                    ? `${store.attachments.length} файл(ов)`
                    : 'Нет прикреплённых файлов'
                    }}
                </p>
            </div>
        </div>

        <!-- Загрузка -->
        <div v-if="store.loading" class="loading-state">
            <div class="loading-spinner">
                <i class="fa-solid fa-spinner fa-spin"></i>
            </div>
            <p>Загрузка вложений...</p>
        </div>

        <!-- Пустое состояние -->
        <div v-else-if="store.attachments.length === 0" class="empty-state">
            <i class="fa-regular fa-folder-open"></i>
            <p>Нет прикреплённых файлов</p>
            <p class="empty-hint">Загрузите файлы через форму выше</p>
        </div>

        <!-- Список вложений -->
        <div v-else class="attachments-grid">
            <div
                v-for="file in store.attachments"
                :key="file.path"
                class="attachment-card"
            >
                <!-- Preview Area -->
                <div
                    class="attachment-preview"
                    :class="getPreviewClass(file)"
                    @click="openPreview(file)"
                >
                    <!-- Image -->
                    <img
                        v-if="isImage(file)"
                        :src="getFileUrl(file)"
                        :alt="file.name"
                        class="preview-image"
                    >

                    <!-- Other file types -->
                    <div v-else class="preview-icon-wrapper">
                        <div class="preview-icon" :class="getFileIconClass(file)">
                            <i :class="getFileIcon(file)"></i>
                        </div>
                        <div class="file-extension">
                            {{ getFileExt(file.name) }}
                        </div>
                    </div>

                    <!-- Overlay -->
                    <div v-if="canPreview(file)" class="preview-overlay">
                        <i class="fa-solid fa-eye"></i>
                        <span>Просмотр</span>
                    </div>
                </div>

                <!-- Info -->
                <div class="attachment-info">
                    <div class="file-name" :title="file.name">
                        {{ file.name }}
                    </div>
                    <div class="file-meta">
                        <span class="file-size">{{ formatSize(file.size) }}</span>
                        <span class="file-separator">•</span>
                        <span class="file-type">{{ getFileExt(file.name) }}</span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="attachment-actions">
                    <button
                        v-if="canPreview(file)"
                        class="action-btn preview-btn"
                        @click="openPreview(file)"
                        title="Быстрый просмотр"
                    >
                        <i class="fa-solid fa-eye"></i>
                    </button>
                    <a
                        :href="getFileUrl(file)"
                        :download="file.name"
                        class="action-btn download-btn"
                        title="Скачать"
                    >
                        <i class="fa-solid fa-download"></i>
                    </a>
                    <button
                        v-if="showDelete"
                        class="action-btn delete-btn"
                        @click="removeFile(file)"
                        title="Удалить"
                    >
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Quick Preview Modal -->
        <Transition name="modal-fade">
            <div v-if="previewFile" class="preview-overlay-full" @click.self="closePreview">
                <div class="preview-modal">
                    <!-- Header -->
                    <div class="preview-header">
                        <div class="preview-header-content">
                            <div class="preview-header-icon">
                                <i :class="getFileIcon(previewFile)"></i>
                            </div>
                            <div class="preview-header-text">
                                <h3 class="preview-title">{{ previewFile.name }}</h3>
                                <p class="preview-subtitle">
                                    {{ formatSize(previewFile.size) }} • {{ getFileExt(previewFile.name) }}
                                </p>
                            </div>
                        </div>
                        <button class="preview-close-btn" @click="closePreview" title="Закрыть">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="preview-body">
                        <!-- Image -->
                        <div v-if="isImage(previewFile)" class="preview-content image-preview">
                            <img
                                :src="getFileUrl(previewFile)"
                                :alt="previewFile.name"
                                class="preview-image-full"
                            >
                        </div>

                        <!-- PDF -->
                        <div v-else-if="isPdf(previewFile)" class="preview-content pdf-preview">
                            <embed
                                :src="getFileUrl(previewFile)"
                                type="application/pdf"
                                width="100%"
                                height="100%"
                            />
                        </div>

                        <!-- Text -->
                        <div v-else-if="isText(previewFile)" class="preview-content text-preview">
                            <pre v-if="textContent" class="text-content">{{ textContent }}</pre>
                            <div v-else class="text-loading">
                                <div class="loading-spinner">
                                    <i class="fa-solid fa-spinner fa-spin"></i>
                                </div>
                                <p>Загрузка содержимого...</p>
                            </div>
                        </div>

                        <!-- Word / Other -->
                        <div v-else class="preview-content unsupported-preview">
                            <div class="unsupported-icon">
                                <i :class="getFileIcon(previewFile)"></i>
                            </div>
                            <h4>{{ isWord(previewFile) ? 'Документ Word' : 'Предпросмотр недоступен' }}</h4>
                            <p>
                                {{ isWord(previewFile)
                                ? 'Файлы Word нельзя отобразить прямо в браузере. Скачайте файл для просмотра.'
                                : 'Этот формат файла не поддерживает быстрый просмотр.'
                                }}
                            </p>
                            <a
                                :href="getFileUrl(previewFile)"
                                :download="previewFile.name"
                                class="btn-download-full"
                            >
                                <i class="fa-solid fa-download me-2"></i>
                                Скачать и открыть
                            </a>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="preview-footer">
                        <a
                            :href="getFileUrl(previewFile)"
                            :download="previewFile.name"
                            class="preview-action-btn download"
                        >
                            <i class="fa-solid fa-download me-2"></i>
                            Скачать
                        </a>
                        <button class="preview-action-btn close" @click="closePreview">
                            <i class="fa-solid fa-xmark me-2"></i>
                            Закрыть
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script>
import { useTaskAttachmentsStore } from '@/stores/useTaskAttachmentsStore'

export default {
    name: 'TaskAttachmentsList',

    props: {
        taskId: { type: Number, required: true },
        showDelete: { type: Boolean, default: false }
    },

    data() {
        return {
            store: useTaskAttachmentsStore(),
            previewFile: null,
            textContent: null
        }
    },

    mounted() {
        this.store.fetch(this.taskId)
    },

    methods: {
        getFileUrl(file) {
            if (!file) return ''
            return `/storage/${file.path}`
        },

        getFileExt(filename) {
            return filename.split('.').pop().toUpperCase()
        },

        getFileIcon(file) {
            if (this.isImage(file)) return 'fa-solid fa-image'
            if (this.isPdf(file)) return 'fa-solid fa-file-pdf'
            if (this.isWord(file)) return 'fa-solid fa-file-word'
            if (this.isText(file)) return 'fa-solid fa-file-lines'
            if (this.isVideo(file)) return 'fa-solid fa-file-video'
            if (this.isAudio(file)) return 'fa-solid fa-file-audio'
            return 'fa-solid fa-file'
        },

        getFileIconClass(file) {
            if (this.isImage(file)) return 'icon-image'
            if (this.isPdf(file)) return 'icon-pdf'
            if (this.isWord(file)) return 'icon-word'
            if (this.isText(file)) return 'icon-text'
            if (this.isVideo(file)) return 'icon-video'
            if (this.isAudio(file)) return 'icon-audio'
            return 'icon-default'
        },

        getPreviewClass(file) {
            if (this.isImage(file)) return 'preview-image-type'
            return 'preview-file-type'
        },

        isImage(file) {
            if (!file) return false
            return file?.mime?.startsWith('image/')
        },

        isPdf(file) {
            if (!file) return false
            return file?.mime === 'application/pdf' || file?.name.toLowerCase().endsWith('.pdf')
        },

        isWord(file) {
            if (!file) return false
            const wordMimes = [
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
            ]
            return wordMimes.includes(file.mime) ||
                file?.name.toLowerCase().endsWith('.doc') ||
                file?.name.toLowerCase().endsWith('.docx')
        },

        isText(file) {
            if (!file) return false
            return (file.mime && file.mime.startsWith('text/')) ||
                file.name.toLowerCase().endsWith('.txt') ||
                file.name.toLowerCase().endsWith('.log')
        },

        isVideo(file) {
            if (!file) return false
            return file.mime && file.mime.startsWith('video/')
        },

        isAudio(file) {
            if (!file) return false
            return file.mime && file.mime.startsWith('audio/')
        },

        canPreview(file) {
            if (!file) return false
            return this.isImage(file) || this.isPdf(file) || this.isText(file)
        },

        formatSize(bytes) {
            if (!bytes) return '0 B'
            const k = 1024
            const sizes = ['B', 'KB', 'MB', 'GB']
            const i = Math.floor(Math.log(bytes) / Math.log(k))
            return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i]
        },

        async openPreview(file) {
            if (!file) return
            this.previewFile = file
            this.textContent = null

            if (this.isText(file)) {
                try {
                    const response = await axios.get(this.getFileUrl(file))
                    this.textContent = response.data
                } catch (e) {
                    console.error('Text load error:', e)
                    this.textContent = 'Ошибка загрузки текста файла.'
                }
            }
        },

        closePreview() {
            this.previewFile = null
            this.textContent = null
        },

        removeFile(file) {
            if (confirm('Удалить этот файл из задачи?')) {
                this.store.remove(this.taskId, file.path)
            }
        }
    }
}
</script>

<style scoped>
.attachments-container {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* === HEADER === */
.attachments-header {
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
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
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

/* === LOADING === */
.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 48px 20px;
    color: #6c757d;
}

.loading-spinner {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e7f1ff 0%, #cfe2ff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #0d6efd;
    margin-bottom: 12px;
}

.loading-state p {
    font-size: 14px;
    margin: 0;
}

/* === EMPTY STATE === */
.empty-state {
    text-align: center;
    padding: 48px 20px;
    color: #adb5bd;
    border: 2px dashed #dee2e6;
    border-radius: 12px;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 12px;
    opacity: 0.5;
}

.empty-state p {
    font-size: 14px;
    margin: 0 0 4px 0;
    color: #6c757d;
}

.empty-hint {
    font-size: 12px !important;
    color: #adb5bd !important;
}

/* === GRID === */
.attachments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
    gap: 12px;
}

/* === CARD === */
.attachment-card {
    background: #ffffff;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.2s;
    display: flex;
    flex-direction: column;
}

.attachment-card:hover {
    border-color: #0d6efd;
    box-shadow: 0 4px 16px rgba(13, 110, 253, 0.15);
    transform: translateY(-2px);
}

/* === PREVIEW === */
.attachment-preview {
    height: 140px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    background: #f8f9fa;
}

.attachment-preview.preview-image-type {
    background: #ffffff;
}

.attachment-preview.preview-file-type {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.preview-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.preview-icon-wrapper {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
}

.preview-icon {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: white;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.icon-image { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
.icon-pdf { background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%); }
.icon-word { background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); }
.icon-text { background: linear-gradient(135deg, #6c757d 0%, #495057 100%); }
.icon-video { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
.icon-audio { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
.icon-default { background: linear-gradient(135deg, #adb5bd 0%, #868e96 100%); }

.file-extension {
    font-size: 10px;
    font-weight: 700;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Overlay */
.preview-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 8px;
    color: white;
    opacity: 0;
    transition: opacity 0.2s;
}

.attachment-preview:hover .preview-overlay {
    opacity: 1;
}

.preview-overlay i {
    font-size: 24px;
}

.preview-overlay span {
    font-size: 12px;
    font-weight: 600;
}

/* === INFO === */
.attachment-info {
    padding: 12px;
    border-top: 1px solid #e9ecef;
    flex: 1;
}

.file-name {
    font-size: 13px;
    font-weight: 600;
    color: #212529;
    margin-bottom: 4px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.file-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    color: #6c757d;
}

.file-separator {
    color: #dee2e6;
}

/* === ACTIONS === */
.attachment-actions {
    display: flex;
    gap: 6px;
    padding: 10px 12px;
    border-top: 1px solid #e9ecef;
    background: #f8f9fa;
}

.action-btn {
    flex: 1;
    padding: 8px;
    border: 1px solid #e9ecef;
    background: #ffffff;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    text-decoration: none;
}

.preview-btn {
    color: #0d6efd;
}

.preview-btn:hover {
    background: #e7f1ff;
    border-color: #0d6efd;
}

.download-btn {
    color: #10b981;
}

.download-btn:hover {
    background: #d1e7dd;
    border-color: #10b981;
}

.delete-btn {
    color: #dc3545;
}

.delete-btn:hover {
    background: #fff5f5;
    border-color: #dc3545;
}

/* === PREVIEW MODAL === */
.preview-overlay-full {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    padding: 20px;
}

.preview-modal {
    background: #ffffff;
    border-radius: 20px;
    width: 900px;
    max-width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.5);
    animation: modalSlideUp 0.3s ease;
    overflow: hidden;
}

@keyframes modalSlideUp {
    from { opacity: 0; transform: translateY(30px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

/* Header */
.preview-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 20px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: white;
    flex-shrink: 0;
}

.preview-header-content {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
    flex: 1;
}

.preview-header-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    backdrop-filter: blur(10px);
    flex-shrink: 0;
}

.preview-header-text {
    flex: 1;
    min-width: 0;
}

.preview-title {
    font-size: 18px;
    font-weight: 700;
    margin: 0 0 2px 0;
    color: white;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.preview-subtitle {
    font-size: 12px;
    margin: 0;
    opacity: 0.9;
}

.preview-close-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: white;
    font-size: 18px;
    transition: all 0.2s;
    flex-shrink: 0;
    margin-left: 12px;
}

.preview-close-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: rotate(90deg);
}

/* Body */
.preview-body {
    flex: 1;
    overflow: hidden;
    background: #f8f9fa;
}

.preview-content {
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.image-preview {
    background: #ffffff;
    padding: 20px;
}

.preview-image-full {
    max-width: 100%;
    max-height: 70vh;
    object-fit: contain;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

.pdf-preview {
    height: 70vh;
}

.pdf-preview embed {
    width: 100%;
    height: 100%;
}

.text-preview {
    padding: 20px;
    overflow: auto;
    max-height: 70vh;
}

.text-content {
    width: 100%;
    padding: 16px;
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    font-family: 'Courier New', monospace;
    font-size: 13px;
    line-height: 1.6;
    white-space: pre-wrap;
    word-break: break-word;
    margin: 0;
}

.text-loading {
    text-align: center;
    color: #6c757d;
}

.unsupported-preview {
    flex-direction: column;
    padding: 48px 20px;
    text-align: center;
    background: #ffffff;
}

.unsupported-icon {
    width: 80px;
    height: 80px;
    border-radius: 20px;
    background: linear-gradient(135deg, #e9ecef 0%, #dee2e6 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 36px;
    color: #6c757d;
    margin-bottom: 20px;
}

.unsupported-preview h4 {
    font-size: 18px;
    font-weight: 700;
    color: #212529;
    margin: 0 0 8px 0;
}

.unsupported-preview p {
    font-size: 14px;
    color: #6c757d;
    margin: 0 0 24px 0;
    max-width: 400px;
}

.btn-download-full {
    padding: 12px 32px;
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: white;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.2s;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
}

.btn-download-full:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
}

/* Footer */
.preview-footer {
    padding: 16px 24px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    background: #f8f9fa;
    flex-shrink: 0;
}

.preview-action-btn {
    padding: 10px 24px;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    text-decoration: none;
}

.preview-action-btn.download {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

.preview-action-btn.download:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.preview-action-btn.close {
    background: #ffffff;
    color: #6c757d;
    border: 1px solid #dee2e6;
}

.preview-action-btn.close:hover {
    background: #f8f9fa;
    color: #495057;
    border-color: #adb5bd;
}

/* === АНИМАЦИИ === */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

/* === АДАПТИВ === */
@media (max-width: 768px) {
    .attachments-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    }

    .attachment-preview {
        height: 120px;
    }

    .preview-icon {
        width: 48px;
        height: 48px;
        font-size: 20px;
    }

    .preview-modal {
        width: 100%;
        max-height: 95vh;
        border-radius: 16px;
    }

    .preview-header {
        padding: 16px 20px;
    }

    .preview-header-icon {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }

    .preview-title {
        font-size: 16px;
    }

    .preview-footer {
        padding: 12px 20px;
        flex-direction: column-reverse;
    }

    .preview-action-btn {
        width: 100%;
    }
}
</style>

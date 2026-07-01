<template>
    <div class="comments-list">
        <!-- Заголовок -->
        <div class="comments-header">
            <div class="header-icon">
                <i class="fa-solid fa-comments"></i>
            </div>
            <div class="header-info">
                <h4 class="header-title">
                    Комментарии
                    <span v-if="store.comments.length > 0" class="comments-count">
                        {{ store.comments.length }}
                    </span>
                </h4>
                <p class="header-subtitle">
                    {{ store.comments.length > 0
                    ? 'Обсуждение задачи'
                    : 'Пока нет комментариев'
                    }}
                </p>
            </div>
        </div>

        <!-- Загрузка -->
        <div v-if="store.loading" class="loading-state">
            <div class="loading-spinner">
                <i class="fa-solid fa-spinner fa-spin"></i>
            </div>
            <p>Загрузка комментариев...</p>
        </div>

        <!-- Пустое состояние -->
        <div v-else-if="store.comments.length === 0" class="empty-state">
            <div class="empty-icon">
                <i class="fa-regular fa-comment-dots"></i>
            </div>
            <p>Нет комментариев к этой задаче</p>
            <p class="empty-hint">Будьте первым, кто оставит комментарий</p>
        </div>

        <!-- Список комментариев -->
        <TransitionGroup v-else name="comment-list" tag="div" class="comments-container">
            <div
                v-for="comment in sortedComments"
                :key="comment.id"
                class="comment-card"
            >
                <!-- Header комментария -->
                <div class="comment-header">
                    <div class="comment-author">
                        <div class="author-avatar" :style="{ background: getAvatarColor(comment.author) }">
                            {{ getInitial(comment.author) }}
                        </div>
                        <div class="author-info">
                            <div class="author-name">{{ comment.author || 'Пользователь' }}</div>
                            <div class="comment-date">
                                <i class="fa-regular fa-clock"></i>
                                {{ formatDate(comment.created_at) }}
                            </div>
                        </div>
                    </div>
                    <button
                        class="btn-delete-comment"
                        @click="confirmDeleteComment(comment.id)"
                        title="Удалить комментарий"
                    >
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>

                <!-- Текст комментария -->
                <div class="comment-text">{{ comment.text }}</div>

                <!-- Вложения -->
                <div v-if="comment.attachments && comment.attachments.length" class="comment-attachments">
                    <div class="attachments-label">
                        <i class="fa-solid fa-paperclip"></i>
                        <span>{{ comment.attachments.length }} файл(ов)</span>
                    </div>

                    <div class="attachments-grid">
                        <div
                            v-for="(file, idx) in comment.attachments"
                            :key="idx"
                            class="attachment-item"
                            @click="openPreview(file)"
                        >
                            <!-- Превью -->
                            <div class="attachment-preview" :class="getPreviewClass(file)">
                                <img
                                    v-if="isImage(file)"
                                    :src="getFileUrl(file)"
                                    :alt="file.name"
                                    class="preview-image"
                                >
                                <div v-else class="preview-icon">
                                    <i :class="getFileIcon(file)"></i>
                                </div>
                            </div>

                            <!-- Overlay с информацией -->
                            <div class="attachment-overlay">
                                <div class="file-name" :title="file.name">{{ file.name }}</div>
                                <div class="file-actions">
                                    <a
                                        :href="getFileUrl(file)"
                                        :download="file.name"
                                        class="action-btn download"
                                        @click.stop
                                        title="Скачать"
                                    >
                                        <i class="fa-solid fa-download"></i>
                                    </a>
                                    <button
                                        class="action-btn delete"
                                        @click.stop="confirmDeleteAttachment(comment.id, file.path)"
                                        title="Удалить файл"
                                    >
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </TransitionGroup>

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
                                <p class="preview-subtitle">Предпросмотр файла</p>
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

                        <!-- Unavailable -->
                        <div v-else class="preview-content unsupported-preview">
                            <div class="unsupported-icon">
                                <i :class="getFileIcon(previewFile)"></i>
                            </div>
                            <h4>Предпросмотр недоступен</h4>
                            <p>Этот формат файла не поддерживает быстрый просмотр.</p>
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
import { useCommentsStore } from '@/stores/useCommentsStore'

export default {
    name: 'CommentsList',

    props: {
        taskId: { type: Number, required: true }
    },

    data() {
        return {
            store: useCommentsStore(),
            previewFile: null,
            textContent: null
        }
    },

    computed: {
        sortedComments() {
            return [...this.store.comments].sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
        }
    },

    mounted() {
        this.store.fetchComments(this.taskId)
    },

    methods: {
        // === FILE HELPERS ===
        getFileUrl(file) {
            return `/storage/${file.path}`
        },

        isImage(file) {
            return file.mime && file.mime.startsWith('image/')
        },

        isPdf(file) {
            return (file.mime === 'application/pdf') || (file.name && file.name.toLowerCase().endsWith('.pdf'))
        },

        isText(file) {
            return (file.mime && file.mime.startsWith('text/')) || (file.name && file.name.toLowerCase().endsWith('.txt'))
        },

        isVideo(file) {
            return file.mime && file.mime.startsWith('video/')
        },

        isAudio(file) {
            return file.mime && file.mime.startsWith('audio/')
        },

        getFileIcon(file) {
            if (this.isImage(file)) return 'fa-solid fa-image'
            if (this.isPdf(file)) return 'fa-solid fa-file-pdf'
            if (this.isVideo(file)) return 'fa-solid fa-file-video'
            if (this.isAudio(file)) return 'fa-solid fa-file-audio'
            return 'fa-solid fa-file'
        },

        getPreviewClass(file) {
            if (this.isImage(file)) return 'preview-image-type'
            if (this.isPdf(file)) return 'preview-pdf-type'
            if (this.isVideo(file)) return 'preview-video-type'
            if (this.isAudio(file)) return 'preview-audio-type'
            return 'preview-default-type'
        },

        // === DATE & AUTHOR ===
        formatDate(dateStr) {
            const date = new Date(dateStr)
            const now = new Date()
            const diff = now - date
            const hours = diff / (1000 * 60 * 60)

            if (hours < 1) {
                const minutes = Math.floor(diff / (1000 * 60))
                return minutes === 0 ? 'только что' : `${minutes} мин. назад`
            }

            if (hours < 24) {
                return `${Math.floor(hours)} ч. назад`
            }

            return date.toLocaleDateString('ru-RU', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            })
        },

        getInitial(name) {
            if (!name) return 'П'
            return name.charAt(0).toUpperCase()
        },

        getAvatarColor(name) {
            if (!name) return 'linear-gradient(135deg, #6c757d 0%, #495057 100%)'

            const colors = [
                'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)',
                'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)',
                'linear-gradient(135deg, #43e97b 0%, #38f9d7 100%)',
                'linear-gradient(135deg, #fa709a 0%, #fee140 100%)',
                'linear-gradient(135deg, #30cfd0 0%, #330867 100%)',
                'linear-gradient(135deg, #a8edea 0%, #fed6e3 100%)',
                'linear-gradient(135deg, #ff9a9e 0%, #fecfef 100%)',
                'linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%)',
                'linear-gradient(135deg, #ff6e7f 0%, #bfe9ff 100%)'
            ]

            const hash = name.split('').reduce((acc, char) => char.charCodeAt(0) + acc, 0)
            return colors[hash % colors.length]
        },

        // === PREVIEW ===
        async openPreview(file) {
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

        // === DELETE ===
        async confirmDeleteComment(commentId) {
            if (confirm('Удалить этот комментарий вместе со всеми вложениями?')) {
                try {
                    await this.store.deleteComment(commentId)
                } catch (e) {
                    alert('Ошибка при удалении комментария')
                }
            }
        },

        async confirmDeleteAttachment(commentId, path) {
            if (confirm('Удалить этот файл из комментария?')) {
                try {
                    await this.store.removeAttachment(commentId, path)
                } catch (e) {
                    alert('Ошибка при удалении файла')
                }
            }
        }
    }
}
</script>

<style scoped>
.comments-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* === HEADER === */
.comments-header {
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
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
}

.header-title {
    font-size: 15px;
    font-weight: 700;
    color: #212529;
    margin: 0 0 2px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.comments-count {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
    padding: 2px 10px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 700;
    box-shadow: 0 2px 6px rgba(139, 92, 246, 0.3);
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
    background: linear-gradient(135deg, #f3f0ff 0%, #ede9fe 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #8b5cf6;
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

.empty-icon {
    width: 72px;
    height: 72px;
    margin: 0 auto 16px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f3f0ff 0%, #ede9fe 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: #8b5cf6;
    opacity: 0.6;
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

/* === COMMENTS CONTAINER === */
.comments-container {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* === COMMENT CARD === */
.comment-card {
    background: #ffffff;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 16px;
    transition: all 0.2s;
}

.comment-card:hover {
    border-color: #dee2e6;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
}

/* === COMMENT HEADER === */
.comment-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #f1f3f5;
}

.comment-author {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 0;
}

.author-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 14px;
    font-weight: 700;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.author-info {
    flex: 1;
    min-width: 0;
}

.author-name {
    font-size: 14px;
    font-weight: 600;
    color: #212529;
    margin-bottom: 2px;
}

.comment-date {
    font-size: 11px;
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 4px;
}

.comment-date i {
    font-size: 10px;
}

.btn-delete-comment {
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    color: #adb5bd;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    flex-shrink: 0;
}

.btn-delete-comment:hover {
    background: #fff5f5;
    color: #dc3545;
}

/* === COMMENT TEXT === */
.comment-text {
    font-size: 14px;
    line-height: 1.6;
    color: #212529;
    white-space: pre-wrap;
    word-break: break-word;
    margin-bottom: 12px;
}

/* === ATTACHMENTS === */
.comment-attachments {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid #f1f3f5;
}

.attachments-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
    margin-bottom: 10px;
}

.attachments-label i {
    font-size: 11px;
    color: #8b5cf6;
}

.attachments-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    gap: 8px;
}

.attachment-item {
    position: relative;
    border-radius: 10px;
    overflow: hidden;
    cursor: pointer;
    border: 2px solid #e9ecef;
    transition: all 0.2s;
    aspect-ratio: 1;
}

.attachment-item:hover {
    border-color: #8b5cf6;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.15);
}

.attachment-preview {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
}

.attachment-preview.preview-image-type {
    background: #ffffff;
}

.attachment-preview.preview-pdf-type {
    background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
    color: #dc2626;
}

.attachment-preview.preview-video-type {
    background: linear-gradient(135deg, #ede9fe 0%, #ddd6fe 100%);
    color: #7c3aed;
}

.attachment-preview.preview-audio-type {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    color: #d97706;
}

.attachment-preview.preview-default-type {
    background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);
    color: #64748b;
}

.preview-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.preview-icon {
    font-size: 32px;
}

/* Overlay */
.attachment-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(to top, rgba(0, 0, 0, 0.85), rgba(0, 0, 0, 0.4));
    padding: 8px;
    display: flex;
    flex-direction: column;
    gap: 4px;
    opacity: 0;
    transition: opacity 0.2s;
}

.attachment-item:hover .attachment-overlay {
    opacity: 1;
}

.file-name {
    font-size: 10px;
    color: white;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.file-actions {
    display: flex;
    gap: 4px;
    justify-content: flex-end;
}

.action-btn {
    width: 24px;
    height: 24px;
    border: none;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 11px;
    text-decoration: none;
}

.action-btn.download {
    background: rgba(255, 255, 255, 0.2);
    color: white;
}

.action-btn.download:hover {
    background: rgba(255, 255, 255, 0.3);
}

.action-btn.delete {
    background: rgba(220, 53, 69, 0.8);
    color: white;
}

.action-btn.delete:hover {
    background: rgba(220, 53, 69, 1);
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

.comment-list-enter-active {
    transition: all 0.4s ease;
}

.comment-list-leave-active {
    transition: all 0.3s ease;
}

.comment-list-enter-from {
    opacity: 0;
    transform: translateY(-20px);
}

.comment-list-leave-to {
    opacity: 0;
    transform: translateX(30px);
}

.comment-list-move {
    transition: transform 0.3s ease;
}

/* === АДАПТИВ === */
@media (max-width: 768px) {
    .comment-card {
        padding: 14px;
    }

    .author-avatar {
        width: 32px;
        height: 32px;
        font-size: 12px;
    }

    .author-name {
        font-size: 13px;
    }

    .attachments-grid {
        grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
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

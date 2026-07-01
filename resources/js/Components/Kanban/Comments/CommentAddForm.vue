<template>
    <div class="comment-add-container">
        <!-- Заголовок -->
        <div class="comment-header">
            <div class="header-icon">
                <i class="fa-solid fa-comment-dots"></i>
            </div>
            <div class="header-info">
                <h4 class="header-title">Добавить комментарий</h4>
                <p class="header-subtitle">Поделитесь мнением или прикрепите файлы</p>
            </div>
        </div>

        <!-- Форма -->
        <div class="comment-form">
            <!-- Имя автора -->
            <div class="form-group">
                <label class="form-label-custom">
                    Ваше имя
                    <span class="label-hint">(необязательно)</span>
                </label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-user input-icon"></i>
                    <input
                        v-model="author"
                        type="text"
                        class="custom-input"
                        placeholder="Как вас зовут?"
                    >
                </div>
            </div>

            <!-- Текст комментария -->
            <div class="form-group">
                <label class="form-label-custom">
                    Комментарий
                    <span class="required">*</span>
                </label>
                <div class="textarea-wrapper">
                    <textarea
                        v-model="text"
                        class="custom-textarea"
                        placeholder="Напишите ваш комментарий..."
                        rows="4"
                        required
                    ></textarea>
                    <div class="textarea-footer">
                        <span class="char-counter" :class="{ 'limit': text.length > 1000 }">
                            {{ text.length }} / 1000
                        </span>
                    </div>
                </div>
            </div>

            <!-- Drop Zone для файлов -->
            <div class="form-group">
                <label class="form-label-custom">
                    Вложения
                    <span class="label-hint">(необязательно)</span>
                </label>

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
                        accept="image/*,application/pdf,.doc,.docx"
                    />

                    <div class="drop-zone-content">
                        <div class="drop-icon">
                            <i class="fa-solid fa-paperclip"></i>
                        </div>
                        <div class="drop-text">
                            <p class="drop-title">
                                {{ isDragOver ? 'Отпустите файлы здесь' : 'Перетащите файлы сюда' }}
                            </p>
                            <p class="drop-hint">
                                или <span class="drop-link">выберите вручную</span>
                            </p>
                            <p class="drop-formats">
                                Изображения, PDF, документы
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Список выбранных файлов -->
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
                                    v-for="(fileObj, idx) in files"
                                    :key="fileObj.file.name + fileObj.file.size + idx"
                                    class="selected-file-item"
                                >
                                    <!-- Превью -->
                                    <div class="file-preview" :class="getFilePreviewClass(fileObj)">
                                        <img
                                            v-if="fileObj.isImage"
                                            :src="fileObj.previewUrl"
                                            :alt="fileObj.file.name"
                                            class="preview-image"
                                        >
                                        <i v-else :class="getFileIcon(fileObj)"></i>
                                    </div>

                                    <!-- Информация -->
                                    <div class="file-info">
                                        <div class="file-name" :title="fileObj.file.name">
                                            {{ fileObj.file.name }}
                                        </div>
                                        <div class="file-size">
                                            {{ formatSize(fileObj.file.size) }}
                                        </div>
                                    </div>

                                    <!-- Кнопка удаления -->
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
            </div>

            <!-- Кнопка отправки -->
            <div class="submit-section">
                <button
                    class="btn-submit"
                    :disabled="!text.trim() || isSubmitting"
                    @click="submit"
                >
                    <span v-if="isSubmitting" class="submit-spinner"></span>
                    <i v-else class="fa-solid fa-paper-plane"></i>
                    <span>{{ isSubmitting ? 'Отправка...' : 'Отправить комментарий' }}</span>
                </button>
            </div>
        </div>

        <!-- Toast уведомление -->
        <Transition name="toast">
            <div v-if="toast.show" class="comment-toast" :class="toast.type">
                <i :class="toast.icon"></i>
                <span>{{ toast.message }}</span>
            </div>
        </Transition>
    </div>
</template>

<script>
import { useCommentsStore } from '@/stores/useCommentsStore'

export default {
    name: 'CommentAddForm',

    props: {
        taskId: { type: Number, required: true }
    },

    data() {
        return {
            author: localStorage.getItem('last_author') || '',
            text: '',
            files: [],
            isSubmitting: false,
            isDragOver: false,
            store: useCommentsStore(),
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
            const rawFiles = Array.from(e.target.files)
            this.addFiles(rawFiles)
        },

        // === DRAG & DROP ===
        onDragOver() {
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
            const processedFiles = newFiles.map(file => {
                const isImage = file.type.startsWith('image/')
                const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf')

                return {
                    file,
                    isImage,
                    isPdf,
                    previewUrl: isImage ? URL.createObjectURL(file) : null
                }
            })

            // Фильтруем дубликаты
            const uniqueFiles = processedFiles.filter(newFile => {
                return !this.files.some(
                    existing =>
                        existing.file.name === newFile.file.name &&
                        existing.file.size === newFile.file.size
                )
            })

            this.files.push(...uniqueFiles)

            if (uniqueFiles.length < newFiles.length) {
                this.showToast('Некоторые файлы уже добавлены', 'warning')
            }
        },

        removeFile(index) {
            const fileObj = this.files[index]
            if (fileObj.previewUrl) {
                URL.revokeObjectURL(fileObj.previewUrl)
            }
            this.files.splice(index, 1)
        },

        clearFiles() {
            this.files.forEach(f => {
                if (f.previewUrl) URL.revokeObjectURL(f.previewUrl)
            })
            this.files = []
            if (this.$refs.fileInput) {
                this.$refs.fileInput.value = ''
            }
        },

        // === SUBMIT ===
        async submit() {
            if (!this.text.trim()) return

            this.isSubmitting = true

            try {
                // Сохраняем автора
                if (this.author) {
                    localStorage.setItem('last_author', this.author)
                }

                await this.store.addComment(this.taskId, {
                    text: this.text,
                    files: this.files.map(f => f.file),
                    author: this.author || 'Пользователь'
                })

                this.showToast('Комментарий успешно отправлен', 'success')

                // Очистка формы
                this.text = ''
                this.clearFiles()
            } catch (error) {
                console.error('Ошибка при добавлении комментария:', error)
                this.showToast('Не удалось отправить комментарий', 'error')
            } finally {
                this.isSubmitting = false
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

        getFileIcon(fileObj) {
            if (fileObj.isImage) return 'fa-solid fa-image'
            if (fileObj.isPdf) return 'fa-solid fa-file-pdf'
            return 'fa-solid fa-file'
        },

        getFilePreviewClass(fileObj) {
            if (fileObj.isImage) return 'preview-image-type'
            if (fileObj.isPdf) return 'preview-pdf-type'
            return 'preview-default-type'
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
    },

    beforeUnmount() {
        // Очищаем все URL при размонтировании
        this.files.forEach(f => {
            if (f.previewUrl) URL.revokeObjectURL(f.previewUrl)
        })
    }
}
</script>

<style scoped>
.comment-add-container {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* === HEADER === */
.comment-header {
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
}

.header-subtitle {
    font-size: 11px;
    color: #6c757d;
    margin: 0;
}

/* === FORM === */
.comment-form {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-label-custom {
    font-size: 13px;
    font-weight: 600;
    color: #495057;
    display: flex;
    align-items: center;
    gap: 6px;
}

.label-hint {
    font-size: 11px;
    color: #adb5bd;
    font-weight: 400;
}

.required {
    color: #dc3545;
    margin-left: 2px;
}

/* === INPUT === */
.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.input-icon {
    position: absolute;
    left: 14px;
    color: #adb5bd;
    font-size: 14px;
    pointer-events: none;
    z-index: 1;
}

.custom-input {
    width: 100%;
    padding: 10px 14px 10px 42px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 14px;
    outline: none;
    transition: all 0.2s;
    background: #ffffff;
}

.custom-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
}

.custom-input::placeholder {
    color: #adb5bd;
}

/* === TEXTAREA === */
.textarea-wrapper {
    position: relative;
}

.custom-textarea {
    width: 100%;
    padding: 12px 14px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 14px;
    outline: none;
    transition: all 0.2s;
    resize: vertical;
    font-family: inherit;
    min-height: 100px;
}

.custom-textarea:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
}

.custom-textarea::placeholder {
    color: #adb5bd;
}

.textarea-footer {
    display: flex;
    justify-content: flex-end;
    margin-top: 4px;
}

.char-counter {
    font-size: 11px;
    color: #adb5bd;
    transition: color 0.2s;
}

.char-counter.limit {
    color: #dc3545;
    font-weight: 600;
}

/* === DROP ZONE === */
.drop-zone {
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    padding: 24px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.drop-zone:hover {
    border-color: #8b5cf6;
    background: #faf8ff;
}

.drop-zone.drag-over {
    border-color: #8b5cf6;
    background: #f3f0ff;
    border-style: solid;
    transform: scale(1.02);
    box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
}

.drop-zone.has-files {
    padding: 16px;
}

.file-input-hidden {
    display: none;
}

.drop-zone-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.drop-icon {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f3f0ff 0%, #ede9fe 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    color: #8b5cf6;
    transition: all 0.3s ease;
}

.drop-zone:hover .drop-icon {
    transform: translateY(-4px);
    box-shadow: 0 8px 20px rgba(139, 92, 246, 0.2);
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
    gap: 3px;
}

.drop-title {
    font-size: 14px;
    font-weight: 600;
    color: #212529;
    margin: 0;
}

.drop-hint {
    font-size: 12px;
    color: #6c757d;
    margin: 0;
}

.drop-link {
    color: #8b5cf6;
    font-weight: 600;
    text-decoration: underline;
}

.drop-formats {
    font-size: 10px;
    color: #adb5bd;
    margin: 2px 0 0 0;
}

/* === SELECTED FILES === */
.selected-files-section {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    margin-top: 12px;
}

.selected-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 14px;
    background: #ffffff;
    border-bottom: 1px solid #e9ecef;
}

.selected-title {
    font-size: 12px;
    font-weight: 600;
    color: #495057;
}

.btn-clear-all {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 5px 10px;
    border: 1px solid #e9ecef;
    background: #ffffff;
    color: #dc3545;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 11px;
    font-weight: 600;
}

.btn-clear-all:hover {
    background: #fff5f5;
    border-color: #fecaca;
}

.selected-files-list {
    padding: 10px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    max-height: 250px;
    overflow-y: auto;
}

.selected-file-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    transition: all 0.2s;
}

.selected-file-item:hover {
    border-color: #dee2e6;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.file-preview {
    width: 40px;
    height: 40px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: white;
    flex-shrink: 0;
    overflow: hidden;
}

.file-preview.preview-image-type {
    background: #f8f9fa;
}

.file-preview.preview-pdf-type {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
}

.file-preview.preview-default-type {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
}

.preview-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.file-info {
    flex: 1;
    min-width: 0;
}

.file-name {
    font-size: 12px;
    font-weight: 600;
    color: #212529;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    margin-bottom: 2px;
}

.file-size {
    font-size: 10px;
    color: #6c757d;
}

.btn-remove-file {
    width: 26px;
    height: 26px;
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
    font-size: 12px;
}

.btn-remove-file:hover {
    background: #fff5f5;
}

/* === SUBMIT === */
.submit-section {
    display: flex;
    justify-content: flex-end;
    padding-top: 8px;
}

.btn-submit {
    padding: 12px 28px;
    border: none;
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 2px 8px rgba(139, 92, 246, 0.3);
}

.btn-submit:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
}

.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.submit-spinner {
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
.comment-toast {
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

.comment-toast.success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

.comment-toast.error {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
}

.comment-toast.warning {
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
        padding: 20px 16px;
    }

    .drop-icon {
        width: 48px;
        height: 48px;
        font-size: 20px;
    }

    .drop-title {
        font-size: 13px;
    }

    .btn-submit {
        width: 100%;
        justify-content: center;
    }
}
</style>

<template>
    <Transition name="modal-fade">
        <div v-if="isVisible" class="modal-overlay" @click.self="close" @keydown.esc="close">
            <div class="modal-window">
                <!-- HEADER -->
                <div class="modal-header-custom">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fa-solid fa-arrows-up-down"></i>
                        </div>
                        <div class="header-text">
                            <h3 class="modal-title-text">Сортировка колонок</h3>
                            <p class="modal-subtitle">Измените порядок колонок на доске</p>
                        </div>
                    </div>
                    <button class="close-btn" @click="close" title="Закрыть">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- BODY -->
                <div class="modal-body-custom">

                    <!-- Подсказка -->
                    <div class="hint-box">
                        <i class="fa-solid fa-circle-info hint-icon"></i>
                        <div class="hint-content">
                            <p class="hint-title">Как изменить порядок</p>
                            <p class="hint-text">
                                Перетащите колонки за иконку <i class="fa-solid fa-grip-vertical"></i>
                                или используйте стрелки <i class="fa-solid fa-arrow-up"></i> <i class="fa-solid fa-arrow-down"></i>
                            </p>
                        </div>
                    </div>

                    <!-- Список колонок -->
                    <div class="columns-list">
                        <TransitionGroup name="column-list">
                            <div
                                v-for="(col, index) in localColumns"
                                :key="col.id"
                                class="column-item"
                                :class="{
                                    'dragging': dragItem?.id === col.id,
                                    'drag-over': dragOverItem?.id === col.id && dragItem?.id !== col.id
                                }"
                                draggable="true"
                                @dragstart="onDragStart($event, col)"
                                @dragover.prevent="onDragOver($event, col)"
                                @dragleave="onDragLeave"
                                @drop="onDrop($event, col)"
                                @dragend="onDragEnd"
                            >
                                <!-- Drag handle -->
                                <div class="drag-handle" title="Перетащите для изменения порядка">
                                    <i class="fa-solid fa-grip-vertical"></i>
                                </div>

                                <!-- Номер позиции -->
                                <div class="position-badge">
                                    {{ index + 1 }}
                                </div>

                                <!-- ID колонки (опционально) -->
                                <Transition name="fade">
                                    <span v-if="need_id" class="column-id">
                                        #{{ col.id }}
                                    </span>
                                </Transition>

                                <!-- Название -->
                                <div class="column-title">
                                    {{ col.title }}
                                </div>

                                <!-- Счётчик задач -->
                                <div class="tasks-count">
                                    {{ col.tasks?.length || 0 }}
                                    <span class="tasks-label">задач</span>
                                </div>

                                <!-- Кнопки управления -->
                                <div class="column-actions">
                                    <button
                                        class="btn-move"
                                        :disabled="index === 0"
                                        @click="moveUp(index)"
                                        title="Переместить вверх"
                                    >
                                        <i class="fa-solid fa-arrow-up"></i>
                                    </button>
                                    <button
                                        class="btn-move"
                                        :disabled="index === localColumns.length - 1"
                                        @click="moveDown(index)"
                                        title="Переместить вниз"
                                    >
                                        <i class="fa-solid fa-arrow-down"></i>
                                    </button>
                                </div>
                            </div>
                        </TransitionGroup>
                    </div>

                    <!-- Переключатель ID -->
                    <div class="switch-card">
                        <label class="custom-switch">
                            <input type="checkbox" v-model="need_id" class="switch-input">
                            <span class="switch-slider"></span>
                            <div class="switch-content">
                                <span class="switch-label">Отображать ID колонок</span>
                                <span class="switch-hint">Показывать числовые идентификаторы для отладки</span>
                            </div>
                        </label>
                    </div>

                </div>

                <!-- FOOTER -->
                <div class="modal-footer-custom">
                    <button class="btn-footer btn-cancel" @click="close">
                        <i class="fa-solid fa-xmark me-2"></i>
                        Отмена
                    </button>
                    <button class="btn-footer btn-save" @click="saveOrder">
                        <i class="fa-solid fa-check me-2"></i>
                        Сохранить порядок
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script>
export default {
    props: {
        show: Boolean,
        columns: Array
    },
    emits: ['close', 'save'],

    data() {
        return {
            isVisible: false,
            need_id: false,
            dragItem: null,
            dragOverItem: null,
            localColumns: []
        }
    },

    watch: {
        show: {
            immediate: true,
            handler(val) {
                if (val) {
                    this.isVisible = true
                    document.body.style.overflow = 'hidden'
                    this.localColumns = JSON.parse(JSON.stringify(this.columns))
                } else {
                    this.isVisible = false
                    document.body.style.overflow = ''
                }
            }
        }
    },

    methods: {
        close() {
            this.isVisible = false
            document.body.style.overflow = ''
            this.$emit('close')
        },

        // === DRAG & DROP ===
        onDragStart(event, col) {
            this.dragItem = col
            event.dataTransfer.effectAllowed = 'move'
            // Добавляем класс для визуального эффекта
            event.target.classList.add('dragging')
        },

        onDragOver(event, col) {
            if (this.dragItem?.id === col.id) return
            this.dragOverItem = col
            event.dataTransfer.dropEffect = 'move'
        },

        onDragLeave() {
            this.dragOverItem = null
        },

        onDrop(event, target) {
            event.preventDefault()

            if (!this.dragItem || this.dragItem.id === target.id) {
                this.dragOverItem = null
                return
            }

            const from = this.localColumns.findIndex(c => c.id === this.dragItem.id)
            const to = this.localColumns.findIndex(c => c.id === target.id)

            if (from === -1 || to === -1) return

            // Перемещаем элемент
            const [moved] = this.localColumns.splice(from, 1)
            this.localColumns.splice(to, 0, moved)

            this.dragItem = null
            this.dragOverItem = null
        },

        onDragEnd() {
            this.dragItem = null
            this.dragOverItem = null
        },

        // === КНОПКИ ===
        moveUp(index) {
            if (index === 0) return
            const item = this.localColumns[index]
            this.localColumns.splice(index, 1)
            this.localColumns.splice(index - 1, 0, item)
        },

        moveDown(index) {
            if (index === this.localColumns.length - 1) return
            const item = this.localColumns[index]
            this.localColumns.splice(index, 1)
            this.localColumns.splice(index + 1, 0, item)
        },

        // === СОХРАНЕНИЕ ===
        saveOrder() {
            const ids = this.localColumns.map(c => c.id)
            this.$emit('save', ids)
            this.close()
        }
    }
}
</script>

<style scoped>
/* === OVERLAY === */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    padding: 20px;
}

/* === МОДАЛЬНОЕ ОКНО === */
.modal-window {
    background: #ffffff;
    border-radius: 20px;
    width: 600px;
    max-width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
    animation: modalSlideUp 0.3s ease;
    overflow: hidden;
}

@keyframes modalSlideUp {
    from { opacity: 0; transform: translateY(30px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

/* === HEADER === */
.modal-header-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 24px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: white;
    flex-shrink: 0;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 16px;
    min-width: 0;
    flex: 1;
}

.header-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    backdrop-filter: blur(10px);
    flex-shrink: 0;
}

.header-text { flex: 1; min-width: 0; }

.modal-title-text {
    font-size: 20px;
    font-weight: 700;
    margin: 0 0 2px 0;
    color: white;
}

.modal-subtitle {
    font-size: 12px;
    margin: 0;
    opacity: 0.9;
}

.close-btn {
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

.close-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: rotate(90deg);
}

/* === BODY === */
.modal-body-custom {
    padding: 24px 28px;
    overflow-y: auto;
    flex: 1;
}

/* === HINT BOX === */
.hint-box {
    display: flex;
    gap: 12px;
    padding: 14px 16px;
    background: #e7f1ff;
    border-left: 3px solid #0d6efd;
    border-radius: 10px;
    margin-bottom: 20px;
}

.hint-icon {
    font-size: 18px;
    color: #0d6efd;
    flex-shrink: 0;
    margin-top: 2px;
}

.hint-content { flex: 1; }

.hint-title {
    font-size: 13px;
    font-weight: 600;
    color: #0d6efd;
    margin: 0 0 4px 0;
}

.hint-text {
    font-size: 12px;
    color: #495057;
    margin: 0;
    line-height: 1.5;
}

.hint-text i {
    color: #0d6efd;
    margin: 0 2px;
}

/* === COLUMNS LIST === */
.columns-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 20px;
}

/* === COLUMN ITEM === */
.column-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    background: #ffffff;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    cursor: grab;
    transition: all 0.2s;
    position: relative;
}

.column-item:hover {
    border-color: #dee2e6;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    transform: translateY(-1px);
}

.column-item:active {
    cursor: grabbing;
}

.column-item.dragging {
    opacity: 0.5;
    transform: scale(0.98);
    border-color: #0d6efd;
    background: #f8f9ff;
}

.column-item.drag-over {
    border-color: #0d6efd;
    border-style: dashed;
    background: #e7f1ff;
}

/* Drag handle */
.drag-handle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    color: #adb5bd;
    cursor: grab;
    transition: all 0.2s;
    border-radius: 6px;
    flex-shrink: 0;
}

.drag-handle:hover {
    background: #f8f9fa;
    color: #495057;
}

.drag-handle:active {
    cursor: grabbing;
    background: #e9ecef;
}

.drag-handle i {
    font-size: 14px;
}

/* Position badge */
.position-badge {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 700;
    flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(102, 126, 234, 0.3);
}

/* Column ID */
.column-id {
    font-size: 11px;
    font-weight: 700;
    color: #6c757d;
    background: #e9ecef;
    padding: 2px 8px;
    border-radius: 6px;
    flex-shrink: 0;
}

/* Column title */
.column-title {
    flex: 1;
    font-size: 14px;
    font-weight: 600;
    color: #212529;
    min-width: 0;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

/* Tasks count */
.tasks-count {
    display: flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
    flex-shrink: 0;
}

.tasks-label {
    font-size: 10px;
    font-weight: 500;
    color: #adb5bd;
}

/* Column actions */
.column-actions {
    display: flex;
    gap: 4px;
    flex-shrink: 0;
}

.btn-move {
    width: 28px;
    height: 28px;
    border: 1px solid #e9ecef;
    background: #ffffff;
    color: #6c757d;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
}

.btn-move:hover:not(:disabled) {
    background: #f8f9fa;
    border-color: #0d6efd;
    color: #0d6efd;
    transform: translateY(-1px);
}

.btn-move:active:not(:disabled) {
    transform: translateY(0);
}

.btn-move:disabled {
    opacity: 0.3;
    cursor: not-allowed;
}

/* === SWITCH === */
.switch-card {
    padding: 14px 16px;
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 10px;
}

.custom-switch {
    display: flex;
    align-items: center;
    gap: 14px;
    cursor: pointer;
    width: 100%;
}

.switch-input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.switch-slider {
    position: relative;
    width: 44px;
    height: 24px;
    background: #dee2e6;
    border-radius: 12px;
    flex-shrink: 0;
    transition: all 0.2s;
}

.switch-slider::before {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 20px;
    height: 20px;
    background: white;
    border-radius: 50%;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}

.switch-input:checked + .switch-slider {
    background: #0d6efd;
}

.switch-input:checked + .switch-slider::before {
    left: 22px;
}

.switch-content {
    display: flex;
    flex-direction: column;
    flex: 1;
}

.switch-label {
    font-size: 14px;
    font-weight: 600;
    color: #212529;
}

.switch-hint {
    font-size: 11px;
    color: #6c757d;
    margin-top: 2px;
}

/* === FOOTER === */
.modal-footer-custom {
    padding: 20px 28px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    background: #f8f9fa;
    flex-shrink: 0;
}

.btn-footer {
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
}

.btn-cancel {
    background: #ffffff;
    color: #6c757d;
    border: 1px solid #dee2e6;
}

.btn-cancel:hover {
    background: #f8f9fa;
    color: #495057;
    border-color: #adb5bd;
}

.btn-save {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
}

.btn-save:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
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

.column-list-move {
    transition: transform 0.3s ease;
}

.column-list-enter-active,
.column-list-leave-active {
    transition: all 0.3s ease;
}

.column-list-enter-from {
    opacity: 0;
    transform: translateX(-20px);
}

.column-list-leave-to {
    opacity: 0;
    transform: translateX(20px);
}

.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.2s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

/* === АДАПТИВ === */
@media (max-width: 768px) {
    .modal-window {
        width: 100%;
        max-height: 95vh;
        border-radius: 16px;
    }

    .modal-header-custom {
        padding: 20px;
    }

    .header-icon {
        width: 48px;
        height: 48px;
        font-size: 20px;
    }

    .modal-title-text {
        font-size: 17px;
    }

    .modal-body-custom {
        padding: 20px;
    }

    .column-item {
        padding: 10px 12px;
        gap: 8px;
    }

    .column-title {
        font-size: 13px;
    }

    .tasks-count {
        display: none; /* Скрываем на мобильных для экономии места */
    }

    .modal-footer-custom {
        padding: 16px 20px;
        flex-direction: column-reverse;
    }

    .btn-footer {
        width: 100%;
    }
}

/* === СКРОЛЛБАР === */
.modal-body-custom::-webkit-scrollbar {
    width: 8px;
}

.modal-body-custom::-webkit-scrollbar-track {
    background: #f1f3f5;
    border-radius: 4px;
}

.modal-body-custom::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 4px;
}

.modal-body-custom::-webkit-scrollbar-thumb:hover {
    background: #adb5bd;
}
</style>

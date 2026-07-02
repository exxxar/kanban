<template>
    <div
        :data-column-id="column.id"
        :class="['kanban-column', { 'drag-over': isDragOver, 'dragging-column': isDraggingColumn }]"
        draggable="true"
        @dragstart="onColumnDragStart($event)"
        @dragover.prevent="onColumnDragOver($event)"
        @dragleave="onColumnDragLeave($event)"
        @dragend="onColumnDragEnd($event)"
        @drop="onColumnDrop($event)">

        <!-- Заголовок колонки -->
        <div class="column-header">
            <div class="drag-handle" title="Потяните для перемещения колонки">
                <i class="fa-solid fa-grip-vertical"></i>
            </div>

            <div class="column-main">
                <div class="column-top-row">
                    <div class="thread-badge">#{{ column.thread }}</div>

                    <div class="title-wrapper">
                        <h4
                            @dblclick="openEditModal"
                            class="column-title"
                            title="Двойной клик для редактирования"
                        >
                            <span class="title-text">{{ column.title }}</span>
                            <i class="fa-solid fa-pen-to-square edit-icon"></i>
                        </h4>
                    </div>

                    <div class="tasks-counter">
                        {{ column.tasks.length }}
                        <span v-if="column.tasks_count > column.tasks.length" class="total-count">
                            /{{ column.tasks_count }}
                        </span>
                    </div>
                </div>
            </div>

            <ColumnDropdown
                :column="column"
                @add-task="$emit('add-task', column.id)"
                @add-client="$emit('add-client', column.id)"
                @open-sort="$emit('open-sort')"
                @open-notification="$emit('open-notification', column)"
                @delete-column="showDeleteModal = true"
            />
        </div>

        <!-- === КНОПКА ДОБАВЛЕНИЯ (СВЕРХУ) === -->
        <div class="add-section">
            <AddMenuDropdown
                :column-id="column.id"
                @add-task="$emit('add-task', column.id)"
                @add-client="$emit('add-client', column.id)"
            />
        </div>

        <!-- Область задач -->
        <div class="kanban-tasks">
            <TransitionGroup name="task-list">
                <KanbanTask
                    v-for="task in column.tasks"
                    :key="task.id"
                    :task="task"
                    draggable="true"
                    @edit-task="editTaskForced"
                    @dragstart="onTaskDragStart"
                    @drop="onTaskDrop"
                    @edit-client="editClient"
                    @duplicate="duplicateTask"
                    @delete="deleteTask"
                    @chat="openChatModal"
                />

            </TransitionGroup>

            <!-- Drop zone -->
            <div v-if="isDragOver && !isDraggingColumn" class="drop-zone-indicator">
                <div class="drop-zone-content">
                    <i class="fa-solid fa-download"></i>
                    <span>Перетащите задачу сюда</span>
                </div>
            </div>

            <!-- Пустое состояние (компактное) -->
            <div v-else-if="column.tasks.length === 0 && !isDraggingColumn" class="empty-state-compact">
                <i class="fa-regular fa-clipboard"></i>
                <p>Пока нет задач. Добавьте первую!</p>
            </div>
        </div>

        <!-- Кнопка загрузки -->
        <button
            v-if="canLoadMore"
            class="btn-load-more"
            @click="loadMore"
        >
            <i class="fa-solid fa-chevron-down me-1"></i>
            Загрузить ещё
            <span class="load-count">({{ column.tasks_count - column.tasks.length }})</span>
        </button>

        <!-- Модалка редактирования названия -->
        <div v-if="showEditModal" class="edit-modal-overlay" @click.self="closeEditModal">
            <div class="edit-modal">
                <div class="edit-modal-header">
                    <h5 class="edit-modal-title">Редактировать название</h5>
                    <button class="edit-modal-close" @click="closeEditModal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="edit-modal-body">
                    <input
                        v-model="editTitle"
                        class="edit-modal-input"
                        @keyup.enter="saveTitle"
                        @keyup.esc="closeEditModal"
                        autofocus
                        placeholder="Введите название колонки..."
                        ref="editInput"
                    >
                </div>
                <div class="edit-modal-footer">
                    <button class="btn-modal-cancel" @click="closeEditModal">Отмена</button>
                    <button class="btn-modal-save" @click="saveTitle">
                        <i class="fa-solid fa-check me-1"></i> Сохранить
                    </button>
                </div>
            </div>
        </div>

        <ConfirmModal
            v-model:show="showDeleteModal"
            title="Удалить колонку?"
            description="Это действие удалит колонку и все карточки в данной колонке."
            @accept="deleteColumn"
            @reject="showDeleteModal = false"
        />

        <!-- Модалка удаления карточки -->
        <ConfirmModal
            v-model:show="showDeleteTaskModal"
            title="Удалить карточку?"
            :description="`Вы уверены, что хотите удалить ${taskToDelete?.type === 2 ? 'клиента' : 'задачу'} «${taskToDelete?.title || ''}»? Это действие нельзя отменить.`"
            @accept="confirmDeleteTask"
            @reject="cancelDeleteTask"
        />


        <!-- Модальное окно чата -->
        <TaskChatModal
            v-if="showChatModal && chatTask"
            :task="chatTask"
            @close="closeChatModal"
        />
    </div>
</template>

<script>
import { useKanbanStore } from '@/stores/kanban/useKanbanStore.js'
import KanbanTask from './KanbanTask.vue'
import ConfirmModal from "@/Components/Kanban/ConfirmModal.vue"
import ColumnDropdown from '@/Components/Kanban/Column/ColumnDropdown.vue'
import AddMenuDropdown from '@/Components/Kanban/Column/AddMenuDropdown.vue'
import TaskChatModal from '@/Components/Kanban/Tasks/TaskChatModal.vue'
export default {
    components: {
        KanbanTask,
        ConfirmModal,
        TaskChatModal,
        ColumnDropdown,
        AddMenuDropdown
    },
    props: { column: Object },
    emits: ['add-task', 'add-client', 'edit-client', 'edit-task', 'open-sort', 'open-notification'],

    data() {
        return {
            showDeleteTaskModal: false, // ← НОВОЕ
            taskToDelete: null,          // ← НОВОЕ
            showDeleteModal: false,
            showEditModal: false,
            showAddMenu: false,
            editTitle: '',
            isDragOver: false,
            showChatModal: false,
            chatTask: null,
            isDraggingColumn: false
        }
    },

    setup() {
        return { store: useKanbanStore() }
    },

    computed: {
        canLoadMore() {
            const info = this.store.taskPagination[this.column.id] || null
            if (!info) return this.column.tasks_count > this.column.tasks.length
            return info && info.page < info.lastPage
        }
    },
    mounted() {
        document.addEventListener('dragend', this.handleGlobalDragEnd)
    },

    beforeUnmount() {
        document.removeEventListener('dragend', this.handleGlobalDragEnd)
    },
    methods: {
        handleGlobalDragEnd() {
            // Сбрасываем состояние при любом завершении drag
            this.isDragOver = false
            this.isDraggingColumn = false
        },
        // === РЕДАКТИРОВАНИЕ ===
        openEditModal() {
            this.editTitle = this.column.title
            this.showEditModal = true
            this.$nextTick(() => {
                this.$refs.editInput?.focus()
                this.$refs.editInput?.select()
            })
        },
        closeEditModal() {
            this.showEditModal = false
            this.editTitle = ''
        },
        async saveTitle() {
            if (!this.editTitle.trim()) return
            await this.store.renameColumn(this.column.id, this.editTitle)
            this.closeEditModal()
        },
        editTask(task) {
            this.$emit('add-task', this.column.id, task)
        },

        editClient(task) {
            this.$emit('edit-client', this.column.id, task)
        },

        // Принудительное редактирование задачи (всегда открывает TaskModal)
        editTaskForced(task) {
            this.$emit('edit-task', this.column.id, task)
        },
        // === DRAG ЗАДАЧ ===

        onTaskDragStart(event, task) {
            event.stopPropagation()
            // Не сохраняем dragTask, только устанавливаем dataTransfer
            event.dataTransfer.setData('taskid', String(task.id))
            event.dataTransfer.setData('type', 'task')
            event.dataTransfer.effectAllowed = 'move'
        },

        onTaskDrop(event, targetTask) {
            event.preventDefault()
            event.stopPropagation() // Важно! Чтобы не сработал onColumnDrop

            const type = event.dataTransfer.getData('type')

            if (type !== 'task') return

            const taskId = Number(event.dataTransfer.getData('taskid'))
            if (taskId === targetTask.id) return

            // Находим исходную колонку задачи
            const sourceColumn = this.store.columns.find(col =>
                col.tasks.some(t => t.id === taskId)
            )

            if (!sourceColumn) return

            // Если задача в той же колонке — пересортировка
            if (sourceColumn.id === this.column.id) {
                this.store.reorderTask(taskId, targetTask.id, this.column.id)
            } else {
                // Перемещение между колонками
                this.store.moveTask(taskId, this.column.id)
            }

            this.isDragOver = false
        },


        // === DRAG КОЛОНОК ===
        onColumnDragStart(event) {
            if (event.target.closest('.kanban-task')) {
                return
            }
            this.isDraggingColumn = true
            event.dataTransfer.setData('columnid', String(this.column.id))
            event.dataTransfer.setData('type', 'column')
            event.dataTransfer.effectAllowed = 'move'
        },

        onColumnDragOver(event) {
            event.preventDefault()
            event.dataTransfer.dropEffect = 'move'
            this.isDragOver = true
        },
        onColumnDragLeave(event) {
            if (this.$el.contains(event.relatedTarget)) {
                return
            }
            this.isDragOver = false
        },

        onColumnDragEnd() {
            this.isDraggingColumn = false
            this.isDragOver = false
        },

        onColumnDrop(event) {
            event.preventDefault()
            this.isDragOver = false
            this.isDraggingColumn = false

            const type = event.dataTransfer.getData('type')

            if (type === 'column') {
                const draggedColumnId = Number(event.dataTransfer.getData('columnid'))
                if (draggedColumnId !== this.column.id) {
                    this.store.reorderColumns(draggedColumnId, this.column.id)
                }
            } else if (type === 'task') {
                const taskId = Number(event.dataTransfer.getData('taskid'))

                // Находим исходную колонку
                const sourceColumn = this.store.columns.find(col =>
                    col.tasks.some(t => t.id === taskId)
                )

                // Перемещаем только если задача из другой колонки
                if (sourceColumn && sourceColumn.id !== this.column.id) {
                    this.store.moveTask(taskId, this.column.id)
                }
            }
        },
        openChatModal(task) {

            this.chatTask = task
            this.showChatModal = true
        },
        closeChatModal() {
            this.showChatModal = false
            this.chatTask = null
        },
        // === ДЕЙСТВИЯ ===
        loadMore() { this.store.loadTasks(this.column.id) },

        deleteTask(task) {
            this.taskToDelete = task
            this.showDeleteTaskModal = true
        },

        async confirmDeleteTask() {
            if (!this.taskToDelete) return

            try {
                await this.store.deleteTask(this.taskToDelete.id)
                this.cancelDeleteTask()
            } catch (error) {
                console.error('Ошибка удаления задачи:', error)
                alert('Не удалось удалить карточку')
            }
        },

        cancelDeleteTask() {
            this.showDeleteTaskModal = false
            this.taskToDelete = null
        },
        duplicateTask(task) { this.store.duplicateTask(task) },
        deleteColumn() { this.store.deleteColumn(this.column.id) }
    }
}
</script>

<style scoped>
/* === БАЗОВЫЕ СТИЛИ КОЛОНКИ === */
.kanban-column {
    background: #f8f9fa;
    border-radius: 16px;
    padding: 16px;
    min-width: 320px;
    max-width: 320px;
    display: flex;
    flex-direction: column;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 2px solid transparent;
    position: relative;
    max-height: calc(100vh - 140px);
}

.kanban-column:hover {
    background: #ffffff;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.kanban-column.dragging-column {
    opacity: 0.4;
    transform: scale(0.98) rotate(1deg);
    cursor: grabbing;
}

.kanban-column.drag-over:not(.dragging-column) {
    background: linear-gradient(135deg, #e7f1ff 0%, #f0f4ff 100%);
    border-color: #0d6efd;
    box-shadow: 0 8px 30px rgba(13, 110, 253, 0.25);
    transform: scale(1.02);
}

/* === ЗАГОЛОВОК === */
.column-header {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
    gap: 8px;
}

.drag-handle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 40px;
    cursor: grab;
    color: #adb5bd;
    transition: all 0.2s;
    flex-shrink: 0;
    border-radius: 6px;
}

.drag-handle:hover { background: #e9ecef; color: #495057; }
.drag-handle:active { cursor: grabbing; background: #dee2e6; }
.drag-handle i { font-size: 16px; }

.column-main { flex-grow: 1; min-width: 0; overflow: hidden; }
.column-top-row { display: flex; align-items: center; gap: 8px; width: 100%; }

.thread-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    flex-shrink: 0;
}

.title-wrapper { flex-grow: 1; min-width: 0; overflow: hidden; }

.column-title {
    font-size: 15px;
    font-weight: 600;
    color: #212529;
    margin: 0;
    cursor: pointer;
    line-height: 1.4;
    transition: all 0.2s;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    display: flex;
    align-items: center;
    gap: 8px;
}

.title-text { overflow: hidden; text-overflow: ellipsis; }
.column-title:hover { color: #0d6efd; }

.edit-icon {
    font-size: 12px;
    opacity: 0;
    transition: opacity 0.2s;
    color: #6c757d;
    flex-shrink: 0;
}

.column-title:hover .edit-icon { opacity: 0.6; }

.tasks-counter {
    background: #e9ecef;
    color: #495057;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    flex-shrink: 0;
    white-space: nowrap;
}

.total-count { color: #6c757d; font-weight: 500; }

/* === СЕКЦИЯ ДОБАВЛЕНИЯ (СВЕРХУ) === */
.add-section {
    margin-bottom: 12px;
    position: relative;
    z-index: 5;
}

/* === ОБЛАСТЬ ЗАДАЧ === */
.kanban-tasks {
    flex-grow: 1;
    overflow-y: auto;
    margin: 0 -4px;
    padding: 21px 4px;
    min-height: 100px;
    position: relative;
}

/* Drop zone */
.drop-zone-indicator {
    position: absolute;
    inset: 0;
    background: rgba(13, 110, 253, 0.05);
    border: 3px dashed #0d6efd;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    pointer-events: none;
}

.drop-zone-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    color: #0d6efd;
    font-size: 16px;
    font-weight: 600;
}

.drop-zone-content i { font-size: 48px; }

/* Компактное пустое состояние */
.empty-state-compact {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 32px 20px;
    text-align: center;
    color: #adb5bd;
}

.empty-state-compact i {
    font-size: 36px;
    margin-bottom: 8px;
    opacity: 0.4;
}

.empty-state-compact p {
    font-size: 13px;
    margin: 0;
    color: #adb5bd;
}

/* Кнопка загрузки */
.btn-load-more {
    width: 100%;
    padding: 10px;
    margin-top: 12px;
    background: #ffffff;
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    color: #6c757d;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-load-more:hover {
    background: #f8f9fa;
    border-color: #0d6efd;
    color: #0d6efd;
}

.load-count { margin-left: 4px; opacity: 0.7; }

/* === АНИМАЦИИ ЗАДАЧ === */
.task-list-enter-active, .task-list-leave-active { transition: all 0.3s ease; }
.task-list-enter-from { opacity: 0; transform: translateY(-20px); }
.task-list-leave-to { opacity: 0; transform: translateX(30px); }
.task-list-move { transition: transform 0.3s ease; }

/* === СКРОЛЛБАР === */
.kanban-tasks::-webkit-scrollbar { width: 6px; }
.kanban-tasks::-webkit-scrollbar-track { background: transparent; }
.kanban-tasks::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 3px; }
.kanban-tasks::-webkit-scrollbar-thumb:hover { background: #adb5bd; }

/* === МОДАЛКА РЕДАКТИРОВАНИЯ (без изменений) === */
.edit-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
}

.edit-modal {
    background: #ffffff;
    border-radius: 16px;
    width: 400px;
    max-width: 90vw;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.edit-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px 16px;
    border-bottom: 1px solid #e9ecef;
}

.edit-modal-title { font-size: 18px; font-weight: 600; margin: 0; }

.edit-modal-close {
    width: 32px; height: 32px;
    border: none; background: transparent;
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; color: #6c757d;
}

.edit-modal-body { padding: 20px 24px; }

.edit-modal-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #dee2e6;
    border-radius: 10px;
    font-size: 15px;
    outline: none;
}

.edit-modal-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
}

.edit-modal-footer {
    display: flex;
    gap: 12px;
    padding: 16px 24px 20px;
    border-top: 1px solid #e9ecef;
}

.btn-modal-cancel, .btn-modal-save {
    flex: 1;
    padding: 10px 20px;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.btn-modal-cancel {
    background: #f8f9fa;
    color: #6c757d;
    border: 1px solid #dee2e6;
}

.btn-modal-save {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: white;
}
</style>

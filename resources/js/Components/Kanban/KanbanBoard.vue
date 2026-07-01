<template>
    <div class="board-container" data-board-root>

        <!-- === ПАНЕЛЬ СВЯЗАННЫХ ДОСОК === -->
        <div v-if="linkedBoards.length > 0" class="linked-boards-panel">
            <div class="linked-boards-scroll">
                <div class="linked-boards-list">
                    <!-- Текущая доска -->
                    <div class="linked-board-item current">
                        <i class="fa-solid fa-table-columns"></i>
                        <span class="linked-board-title">{{ store.board?.title || 'Текущая доска' }}</span>
                        <span class="current-badge">Текущая</span>
                    </div>

                    <!-- Связанные доски -->
                    <a
                        v-for="board in linkedBoards"
                        :key="board.url"
                        :href="board.url"
                        class="linked-board-item"
                        :title="board.url"
                    >
                        <i class="fa-solid fa-table-columns"></i>
                        <span class="linked-board-title">{{ board.title || 'Доска' }}</span>
                        <i class="fa-solid fa-arrow-up-right-from-square linked-board-arrow"></i>
                    </a>
                </div>
            </div>
        </div>


        <!-- === ШАПКА ДОСКИ === -->
        <header class="board-header">
            <div class="board-title-section">
                <div class="board-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="board-title-wrapper">
                    <h2
                        v-if="!editingBoardTitle"
                        class="board-title"
                        @dblclick="openEditBoardModal"
                        title="Двойной клик для редактирования"
                    >
                        {{ store.board?.title || initialBoard.title }}
                        <i class="fa-solid fa-pen-to-square edit-board-icon"></i>
                    </h2>
                    <div class="board-subtitle">
                        {{ store.columns?.length || 0 }} колонок •
                        {{ totalTasksCount }} задач
                    </div>
                </div>
            </div>

            <div class="board-actions">
                <button class="action-btn" @click="copyLink" title="Скопировать ссылку">
                    <i class="fas fa-link"></i>
                </button>
                <button class="action-btn" @click="openConfigModal" title="Настройки доски">
                    <i class="fa-solid fa-gear"></i>
                </button>
                <button class="action-btn" @click="openTokenModal" title="API токены">
                    <i class="fa-solid fa-key"></i>
                </button>
                <button class="action-btn" @click="openExportModal" title="Экспорт в Excel">
                    <i class="fas fa-file-export"></i>
                </button>
                <div class="action-divider"></div>
                <button class="action-btn action-btn-primary" @click="openColumnModal" title="Добавить колонку">
                    <i class="fas fa-plus"></i>
                    <span class="btn-label">Колонка</span>
                </button>
                <button class="action-btn action-btn-danger" @click="showDeleteModal = true" title="Удалить доску">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </header>

        <!-- === МОБИЛЬНАЯ ВЕРСИЯ === -->
        <div class="mobile-view d-md-none">
            <div class="mobile-tabs-wrapper">
                <div class="mobile-tabs">
                    <button
                        type="button"
                        v-for="col in store.columns"
                        :key="col.id"
                        class="mobile-tab"
                        :class="{ 'active': activeColumn === col.id }"
                        @click="openActiveColumn(col)"
                    >
                        <span class="tab-title">{{ col.title }}</span>
                        <span class="tab-count">{{ col.tasks?.length || 0 }}</span>
                    </button>
                </div>
            </div>

            <div class="mobile-content">
                <template v-if="getActiveColumn">
                    <KanbanColumn
                        :column="getActiveColumn"
                        @open-notification="openNotificationModal"
                        @open-sort="showSortModal = true"
                        @add-task="openTaskModal"
                        @add-client="openClientModal"
                        @edit-task="openTaskModalForced"
                    />
                </template>
            </div>
        </div>

        <!-- === ДЕСКТОПНАЯ ДОСКА (ЦЕНТРИРОВАННАЯ) === -->
        <div class="board-scroll-wrapper d-none d-md-block">
            <div class="board-centered">
                <TransitionGroup name="board-columns" tag="div" class="kanban-board">
                    <KanbanColumn
                        v-for="column in store.columns"
                        :key="column.id"
                        :column="column"
                        @open-sort="showSortModal = true"
                        @open-notification="openNotificationModal"
                        @add-task="openTaskModal"
                        @add-client="openClientModal"
                        @edit-task="openTaskModalForced"
                        @edit-client="openClientModalForced"
                    />
                </TransitionGroup>
            </div>
        </div>

        <!-- === МОДАЛКА СОЗДАНИЯ/РЕДАКТИРОВАНИЯ КЛИЕНТА === -->
        <ClientCreateModal
            v-if="showClientModal"
            :board-uuid="initialBoard.uuid"
            :columns="initialBoard.columns"
            :prefilled-column-id="clientPrefilledColumnId"
            :is-edit="isEditClient"
            :client="editingClient"
            :task="editingClientTask"
            @close="closeClientModal"
            @created="onClientCreated"
            @updated="onClientUpdated"
        />

        <!-- === МОДАЛЬНЫЕ ОКНА === -->

        <!-- Редактирование названия доски -->
        <div v-if="showEditBoardModal" class="modal-overlay" @click.self="closeEditBoardModal">
            <div class="modal-window">
                <div class="modal-header">
                    <h5 class="modal-title-text">Редактировать название доски</h5>
                    <button class="modal-close" @click="closeEditBoardModal">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <input
                        v-model="localBoardTitle"
                        class="modal-input"
                        @keyup.enter="saveBoardTitle"
                        @keyup.esc="closeEditBoardModal"
                        autofocus
                        placeholder="Введите название доски..."
                        ref="boardTitleInput"
                    >
                </div>
                <div class="modal-footer">
                    <button class="modal-btn modal-btn-cancel" @click="closeEditBoardModal">
                        Отмена
                    </button>
                    <button class="modal-btn modal-btn-save" @click="saveBoardTitle">
                        <i class="fa-solid fa-check me-1"></i>
                        Сохранить
                    </button>
                </div>
            </div>
        </div>

        <TaskModal
            v-if="showTaskModal"
            :task="editingTask"
            :column-id="currentColumnId"
            @close="closeTaskModal"
            @save="saveTask"
        />

        <BoardSettings
            v-if="showConfigModal"
            @close="closeConfigModal"
            @save="saveSettings"
        />

        <ColumnModal
            v-if="showColumnModal"
            @close="closeColumnModal"
            @save="addColumn"
        />

        <ConfirmModal
            v-model:show="showDeleteModal"
            title="Удалить доску?"
            description="Это действие удалит все колонки и карточки."
            @accept="clearBoard"
            @reject="showDeleteModal = false"
        />

        <ColumnSortModal
            :show="showSortModal"
            :columns="store.columns"
            @close="showSortModal = false"
            @save="applySort"
        />

        <ColumnNotificationsModal
            :show="showNotifications"
            :column="selectedColumn"
            @close="showNotifications = false"
            @save="saveNotifications"
        />

        <ConfirmModal
            v-model:show="showExportModal"
            title="Выгрузить данные в эксель?"
            description="Сейчас вас направит на страницу выгрузки данных в файле эксель."
            @accept="exportData"
            @reject="showExportModal = false"
        />

        <TokenModal
            v-if="showTokenModal"
            @close="showTokenModal = false"
        />
    </div>
</template>

<script>
import { useKanbanStore } from '@/stores/useKanbanStore'
import KanbanColumn from './KanbanColumn.vue'
import TaskModal from './TaskModal.vue'
import ColumnModal from './ColumnModal.vue'
import ConfirmModal from "@/Components/Kanban/ConfirmModal.vue"
import ColumnSortModal from "@/Components/Kanban/ColumnSortModal.vue"
import ColumnNotificationsModal from "@/Components/Kanban/ColumnNotificationsModal.vue"
import TokenModal from '@/Components/Kanban/TokenModal.vue'
import KanbanTask from './KanbanTask.vue'
import BoardSettings from "@/Components/Kanban/BoardSettingsModal.vue"
import ClientCreateModal from '@/Components/Kanban/Clients/ClientCreateModal.vue'

export default {
    components: {
        BoardSettings,
        ClientCreateModal,
        ColumnNotificationsModal,
        KanbanColumn,
        TaskModal,
        ColumnModal,
        ConfirmModal,
        TokenModal,
        KanbanTask,
        ColumnSortModal
    },
    props: { initialBoard: Object },

    data() {
        return {
            selectedColumn: null,
            showNotifications: false,
            activeColumn: 0,
            showSortModal: false,
            showTokenModal: false,
            showDeleteModal: false,
            showExportModal: false,
            showTaskModal: false,
            showConfigModal: false,
            showColumnModal: false,
            showEditBoardModal: false,
            editingTask: null,
            currentColumnId: null,
            store: useKanbanStore(),

            // === КЛИЕНТЫ ===
            showClientModal: false,
            isEditClient: false,
            clientPrefilledColumnId: null,
            editingClient: null,
            editingClientTask: null,

            localBoardTitle: this.initialBoard.title,

            linkedBoards: [] // ← Локальное состояние для связанных досок
        }
    },

    computed: {
        getActiveColumn() {
            return this.store.columns.find(c => c.id === this.activeColumn)
        },
        totalTasksCount() {
            return this.store.columns.reduce((sum, col) => sum + (col.tasks?.length || 0), 0)
        },
    },

    mounted() {
        this.store.columns = this.initialBoard.columns
        this.store.board = this.initialBoard

        this.linkedBoards = this.initialBoard.config?.linked_boards || []

        this.$nextTick(() => {
            this.activeColumn = this.store.columns[0]?.id || null
        })

        window.addEventListener('select-new-tab', () => {
            this.activeColumn = this.store.columns[0]?.id || null
        })
    },

    methods: {
        // === КЛИЕНТЫ ===
        openClientModalForced(columnId, task) {

            console.log("task", task)
            this.isEditClient = true
            this.editingClient = task.client
            this.editingClientTask = task


            if (task && !task.last_viewed_at) {
                this.store.markTaskViewed(task.id)
            }

            this.clientPrefilledColumnId = columnId
            this.showClientModal = true

        },
        // Открытие модалки СОЗДАНИЯ клиента
        openClientModal(columnId) {
            this.isEditClient = false
            this.editingClient = null
            this.editingClientTask = null
            this.clientPrefilledColumnId = columnId
            this.showClientModal = true
        },

        // Открытие модалки РЕДАКТИРОВАНИЯ клиента
        openEditClientModal(task) {
            if (!task || !task.client) {
                console.warn('Попытка отредактировать задачу без клиента')
                return
            }

            this.isEditClient = true
            this.editingClient = task.client  // ← Данные клиента
            this.editingClientTask = task     // ← Сама задача
            this.clientPrefilledColumnId = task.column_id
            this.showClientModal = true
        },
        closeClientModal() {
            this.showClientModal = false
            this.isEditClient = false
            this.editingClient = null
            this.editingClientTask = null
            this.clientPrefilledColumnId = null
        },

        // Обработка создания клиента
        onClientCreated(newTask) {
            const column = this.store.columns.find(c => c.id === newTask.column_id)
            if (column) {
                if (!column.tasks) column.tasks = []
                column.tasks.push(newTask)
                column.tasks_count = (column.tasks_count || 0) + 1
            }
            this.showToast('Клиент создан')
        },

        // Обработка обновления клиента
        onClientUpdated(updatedTask) {
            // Находим и обновляем задачу во всех колонках
            for (const column of this.store.columns) {
                const taskIndex = column.tasks.findIndex(t => t.id === updatedTask.id)
                if (taskIndex !== -1) {
                    // Если задача переместилась в другую колонку
                    if (column.id !== updatedTask.column_id) {
                        // Удаляем из старой колонки
                        column.tasks.splice(taskIndex, 1)
                        column.tasks_count = Math.max(0, column.tasks_count - 1)

                        // Добавляем в новую колонку
                        const targetColumn = this.store.columns.find(c => c.id === updatedTask.column_id)
                        if (targetColumn) {
                            targetColumn.tasks.push(updatedTask)
                            targetColumn.tasks_count = (targetColumn.tasks_count || 0) + 1
                        }
                    } else {
                        // Обновляем на месте
                        column.tasks[taskIndex] = updatedTask
                    }
                    break
                }
            }
            this.showToast('Клиент обновлён')
        },

        // === ЗАДАЧИ ===
        openTaskModal(columnId, task = null) {
            // Если это клиент — открываем модалку редактирования клиента
            if (task && task.type === 2 && task.client) {
                this.openEditClientModal(task)
                return
            }

            this.currentColumnId = columnId
            this.editingTask = task

            if (task && !task.last_viewed_at) {
                this.store.markTaskViewed(task.id)
            }

            this.showTaskModal = true
        },

        // Принудительное открытие модалки ЗАДАЧИ (игнорирует тип клиента)
        openTaskModalForced(columnId, task) {
            this.currentColumnId = columnId
            this.editingTask = task

            if (task && !task.last_viewed_at) {
                this.store.markTaskViewed(task.id)
            }

            this.showTaskModal = true
        },


        closeTaskModal() {
            this.showTaskModal = false
            this.editingTask = null
        },

        async saveTask(task) {
            if (task.id) {
                await this.updateTask(task)
            } else {
                await this.createTask(this.store.board.uuid, task)
            }
            this.closeTaskModal()
        },

        // === НАЗВАНИЕ ДОСКИ ===
        openEditBoardModal() {
            this.localBoardTitle = this.store.board.title
            this.showEditBoardModal = true
            this.$nextTick(() => {
                this.$refs.boardTitleInput?.focus()
                this.$refs.boardTitleInput?.select()
            })
        },
        closeEditBoardModal() {
            this.showEditBoardModal = false
            this.localBoardTitle = this.store.board.title
        },
        async saveBoardTitle() {
            if (!this.localBoardTitle.trim()) return
            await this.store.renameBoard(this.store.board.uuid, this.localBoardTitle)
            this.closeEditBoardModal()
        },

        // === ДЕЙСТВИЯ С КОЛОНКАМИ ===
        openNotificationModal(column) {
            this.selectedColumn = null
            this.$nextTick(() => {
                this.selectedColumn = column
                this.showNotifications = true
            })
        },
        saveNotifications(settings) {
            this.store.updateColumnNotifications(this.selectedColumn.id, settings)
            this.showNotifications = false
        },
        openActiveColumn(col) {
            this.activeColumn = null
            this.$nextTick(() => {
                this.activeColumn = col.id
            })
        },
        applySort(newOrder) {
            this.store.reorderColumns(newOrder)
            this.showSortModal = false
        },

        // === НАСТРОЙКИ ===
        openConfigModal() { this.showConfigModal = true },
        closeConfigModal() { this.showConfigModal = false },
        async saveSettings(settings) {
            try {
                await this.store.saveConfig(this.initialBoard.uuid, settings)

                // ← ОБНОВЛЯЕМ ЛОКАЛЬНОЕ СОСТОЯНИЕ ПОСЛЕ СОХРАНЕНИЯ
                this.linkedBoards = settings.linked_boards || []

                // Обновляем config в store
                if (this.store.board) {
                    this.store.board.config = settings
                }

                this.showToast('Настройки сохранены')
            } catch (error) {
                console.error('Ошибка сохранения настроек:', error)
                this.showToast('Ошибка сохранения настроек')
            }
        },

        // === КОЛОНКИ ===
        openColumnModal() { this.showColumnModal = true },
        closeColumnModal() { this.showColumnModal = false },
        addColumn(title) {
            this.store.createColumn(this.store.board.uuid, title)
            this.closeColumnModal()
        },

        // === ПРОЧЕЕ ===
        openTokenModal() { this.showTokenModal = true },
        openExportModal() { this.showExportModal = true },
        exportData() {
            window.open(`/api/boards/${this.initialBoard.id}/export`, '_blank')
        },
        clearBoard() { this.store.clearBoard() },
        copyLink() {
            navigator.clipboard.writeText(window.location.href)
            this.showToast('Ссылка скопирована!')
        },
        showToast(message) {
            const toast = document.createElement('div')
            toast.className = 'board-toast'
            toast.innerHTML = `<i class="fa-solid fa-check-circle me-2"></i>${message}`
            document.body.appendChild(toast)
            setTimeout(() => toast.classList.add('show'), 10)
            setTimeout(() => {
                toast.classList.remove('show')
                setTimeout(() => toast.remove(), 300)
            }, 2000)
        }
    }
}
</script>

<style scoped>

/* === ПАНЕЛЬ СВЯЗАННЫХ ДОСОК === */
.linked-boards-panel {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-bottom: 1px solid #e9ecef;
    padding: 12px 24px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
    margin-bottom: 10px;
    border-radius: 12px;
}

.linked-boards-scroll {
    overflow-x: auto;
    scrollbar-width: thin;
}

.linked-boards-scroll::-webkit-scrollbar {
    height: 6px;
}

.linked-boards-scroll::-webkit-scrollbar-track {
    background: transparent;
}

.linked-boards-scroll::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 3px;
}

.linked-boards-list {
    display: flex;
    gap: 8px;
    min-width: min-content;
}

.linked-board-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 500;
    color: #495057;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
    flex-shrink: 0;
}

.linked-board-item:hover {
    background: #f8f9fa;
    border-color: #8b5cf6;
    color: #7c3aed;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.15);
}

.linked-board-item.current {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
    border-color: transparent;
    box-shadow: 0 2px 8px rgba(139, 92, 246, 0.3);
    cursor: default;
}

.linked-board-item.current:hover {
    transform: none;
    box-shadow: 0 2px 8px rgba(139, 92, 246, 0.3);
}

.linked-board-item i {
    font-size: 12px;
}

.linked-board-title {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
}

.current-badge {
    font-size: 10px;
    font-weight: 700;
    background: rgba(255, 255, 255, 0.25);
    padding: 2px 8px;
    border-radius: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.linked-board-arrow {
    font-size: 10px;
    opacity: 0.6;
    transition: all 0.2s;
}

.linked-board-item:hover .linked-board-arrow {
    opacity: 1;
    transform: translate(2px, -2px);
}

/* === АДАПТИВ === */
@media (max-width: 767px) {
    .linked-boards-panel {
        padding: 10px 16px;
    }

    .linked-board-item {
        padding: 6px 12px;
        font-size: 12px;
    }

    .linked-board-title {
        max-width: 150px;
    }
}

/* === ОСТАЛЬНЫЕ СТИЛИ (без изменений) === */
.board-container {
    background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf3 100%);
    padding: 10px;
    border-radius: 10px;
}

.board-header {
    background: #ffffff;
    padding: 16px 24px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    border-bottom: 1px solid #e9ecef;
    position: sticky;
    top: 0;
    z-index: 100;
    flex-wrap: wrap;
}


.board-title-section {
    display: flex;
    align-items: center;
    gap: 16px;
    min-width: 0;
    flex: 1;
}

.board-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.board-title-wrapper {
    min-width: 0;
    flex: 1;
}

.board-title {
    font-size: 20px;
    font-weight: 700;
    color: #212529;
    margin: 0 0 2px 0;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: color 0.2s;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.board-title:hover {
    color: #0d6efd;
}

.edit-board-icon {
    font-size: 12px;
    opacity: 0;
    transition: opacity 0.2s;
    color: #6c757d;
}

.board-title:hover .edit-board-icon {
    opacity: 0.6;
}

.board-subtitle {
    font-size: 13px;
    color: #6c757d;
    font-weight: 500;
}

/* === КНОПКИ ДЕЙСТВИЙ === */
.board-actions {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.action-btn {
    width: 40px;
    height: 40px;
    border: 1px solid #e9ecef;
    background: #ffffff;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    cursor: pointer;
    color: #495057;
    transition: all 0.2s;
    font-size: 14px;
}

.action-btn:hover {
    background: #f8f9fa;
    border-color: #dee2e6;
    color: #212529;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
}

.action-btn:active {
    transform: translateY(0);
}

.action-btn-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: white;
    border-color: transparent;
    width: auto;
    padding: 0 16px;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
}

.action-btn-primary:hover {
    background: linear-gradient(135deg, #0b5ed7 0%, #0a58ca 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
}

.action-btn-primary .btn-label {
    font-size: 13px;
    font-weight: 600;
}

.action-btn-danger {
    color: #dc3545;
}

.action-btn-danger:hover {
    background: #fff5f5;
    border-color: #fecaca;
    color: #dc2626;
}

.action-divider {
    width: 1px;
    height: 24px;
    background: #e9ecef;
    margin: 0 4px;
}

/* === МОБИЛЬНАЯ ВЕРСИЯ === */
.mobile-view {
    display: flex;
    flex-direction: column;
    height: calc(100vh - 80px);
}

.mobile-tabs-wrapper {
    position: sticky;
    top: 73px;
    z-index: 50;
    background: #ffffff;
    border-bottom: 1px solid #e9ecef;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.04);
}

.mobile-tabs {
    display: flex;
    overflow-x: auto;
    padding: 12px;
    gap: 8px;
    scrollbar-width: none;
}

.mobile-tabs::-webkit-scrollbar {
    display: none;
}

.mobile-tab {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 16px;
    border: 1px solid #e9ecef;
    background: #ffffff;
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    color: #495057;
    cursor: pointer;
    white-space: nowrap;
    transition: all 0.2s;
    flex-shrink: 0;
}

.mobile-tab:hover {
    background: #f8f9fa;
    border-color: #dee2e6;
}

.mobile-tab.active {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: white;
    border-color: transparent;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
}

.tab-count {
    background: rgba(0, 0, 0, 0.1);
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 11px;
    font-weight: 600;
}

.mobile-tab.active .tab-count {
    background: rgba(255, 255, 255, 0.25);
}

.mobile-content {
    flex: 1;
    overflow-y: auto;
    padding: 5px;
}

/* === ЦЕНТРИРОВАННАЯ ДОСКА === */
.board-scroll-wrapper {
    overflow-x: auto;
    padding: 10px 0px 24px;
    height: calc(75vh);
}

.board-centered {
    max-width: 1600px;
    margin: 0 auto;
    min-width: min-content;
}

.kanban-board {
    display: flex;
    gap: 16px;
    align-items: flex-start;
    min-height: 100%;
    justify-content: flex-start;
}

/* === АНИМАЦИИ КОЛОНОК === */
.board-columns-move {
    transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.board-columns-enter-active {
    transition: all 0.4s ease;
}

.board-columns-leave-active {
    transition: all 0.3s ease;
    position: absolute;
}

.board-columns-enter-from {
    opacity: 0;
    transform: translateX(-30px) scale(0.95);
}

.board-columns-leave-to {
    opacity: 0;
    transform: translateX(30px) scale(0.95);
}

/* === МОДАЛЬНОЕ ОКНО === */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    animation: fadeIn 0.2s ease;
}

.modal-window {
    background: #ffffff;
    border-radius: 16px;
    width: 440px;
    max-width: 90vw;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    animation: slideUp 0.3s ease;
}

.modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 20px 24px 16px;
    border-bottom: 1px solid #e9ecef;
}

.modal-title-text {
    font-size: 18px;
    font-weight: 600;
    color: #212529;
    margin: 0;
}

.modal-close {
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #6c757d;
    transition: all 0.2s;
}

.modal-close:hover {
    background: #f8f9fa;
    color: #212529;
}

.modal-body {
    padding: 20px 24px;
}

.modal-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #dee2e6;
    border-radius: 10px;
    font-size: 15px;
    outline: none;
    transition: all 0.2s;
}

.modal-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
}

.modal-footer {
    display: flex;
    gap: 12px;
    padding: 16px 24px 20px;
    border-top: 1px solid #e9ecef;
}

.modal-btn {
    flex: 1;
    padding: 10px 20px;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.modal-btn-cancel {
    background: #f8f9fa;
    color: #6c757d;
    border: 1px solid #dee2e6;
}

.modal-btn-cancel:hover {
    background: #e9ecef;
    color: #495057;
}

.modal-btn-save {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
}

.modal-btn-save:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* === TOAST УВЕДОМЛЕНИЕ === */
:global(.board-toast) {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 14px 20px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 500;
    box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s ease;
    z-index: 9999;
    display: flex;
    align-items: center;
}

:global(.board-toast.show) {
    opacity: 1;
    transform: translateY(0);
}

/* === АДАПТИВ === */
@media (max-width: 767px) {
    .board-header {
        padding: 12px 16px;
    }

    .board-icon {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }

    .board-title {
        font-size: 16px;
    }

    .action-btn {
        width: 36px;
        height: 36px;
    }

    .action-btn-primary .btn-label {
        display: none;
    }

    .action-btn-primary {
        width: 36px;
        padding: 0;
    }
}

/* === СКРОЛЛБАР === */
.board-scroll-wrapper::-webkit-scrollbar {
    height: 10px;
    width: 10px;
}

.board-scroll-wrapper::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.03);
    border-radius: 5px;
}

.board-scroll-wrapper::-webkit-scrollbar-thumb {
    background: #adb5bd;
    border-radius: 5px;
}

.board-scroll-wrapper::-webkit-scrollbar-thumb:hover {
    background: #868e96;
}
</style>

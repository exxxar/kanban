import {defineStore} from 'pinia'
import axios from 'axios'
import {detectChanges, notifyChange} from './utils/boardChanges'
import {apiRequest} from '@/stores/utils/api.js'


export const useKanbanStore = defineStore('kanban', {
    state: () => ({
        board: null,
        columns: [],
        leadSources: [],
        tags: [],
        loading: false,
        taskPagination: {},
        error: null,

        webhookTestResult: null,
        emailTestResult: null,
    }),

    getters: {
        getColumnById: (state) => (id) => {
            return state.columns.find(c => c.id === id)
        },

        getTaskById: (state) => (id) => {
            for (const col of state.columns) {
                const task = col.tasks.find(t => t.id === id)
                if (task) return task
            }
            return null
        }
    },

    actions: {
        async fetchLeadSources() {
            try {
                const response = await axios.get(`/api/boards/${this.board.uuid}/lead-sources`)
                this.leadSources = response.data.sources || []
                return this.leadSources
            } catch (error) {
                console.error('Ошибка загрузки источников:', error)
                return []
            }
        },
        async testCreateCard(type) {
            try {
                const { data } = await axios.post('/api/test/card', { type })
                return data
            } catch (e) {
                console.error('Ошибка тестового создания карточки', e)
            }
        },
        async testWebhook(payload = {
            url: null
        }) {
            this.webhookTestResult = null
            this.loading = true
            this.error = null
            try {
                const {data} = await axios.post('/api/test/webhook', payload)
                this.webhookTestResult = data
                return data
            } catch (e) {
                this.error = 'Ошибка теста вебхука'
                console.error(e)
            } finally {
                this.loading = false
            }
        },


        async updateColumnNotifications(columnId, settings) {
            try {
                await axios.post(`/api/columns/${columnId}/notifications`, {
                    notifications: settings
                })

                const col = this.columns.find(c => c.id === columnId)
                if (col) col.notifications = settings

            } catch (e) {
                console.error("Ошибка обновления уведомлений", e)
            }
        },
        async testEmail(payload = {
            email: null,
        }) {
            this.emailTestResult = null
            this.loading = true
            this.error = null
            try {
                const {data} = await axios.post('/api/test/email', payload)
                this.emailTestResult = data
                return data
            } catch (e) {
                this.error = 'Ошибка теста email'
                console.error(e)
            } finally {
                this.loading = false
            }
        },
        async saveConfig(uuid, config) {
            const {data} = await apiRequest('post', `/api/boards/${uuid}/config`, config)
            this.board.config = data
        },
        async renameBoard(uuid, title) {
            const {data} = await apiRequest('put', `/api/boards/${uuid}`, {title})
            this.board.title = data.title
        },

        async loadTasks(columnId) {
            let page = 2
            const info = this.taskPagination[columnId]

            if (info && info.page < info.lastPage) {
                page = (info.page || 1) + 1
            }

            const {data} = await axios.get(`/api/columns/${columnId}/tasks?page=${page}`)
            const column = this.getColumnById(columnId)
            if (!column) return

            column.tasks = [...column.tasks, ...data.data]
            this.taskPagination[columnId] = {
                page: data.current_page,
                lastPage: data.last_page
            }
        },
        async createColumn(uuid, title) {
            const {data} = await apiRequest('post', `/api/boards/${uuid}/columns`, {title})
            this.columns.push({...data, tasks: []})
            return data
        },
        async reorderColumns(draggedColumnId, targetColumnId) {
            if (draggedColumnId === targetColumnId) return

            // Сохраняем старый порядок для отката
            const oldOrder = this.board.columns.map(c => c.id)

            // Находим индексы
            const draggedIndex = this.board.columns.findIndex(c => c.id === draggedColumnId)
            const targetIndex = this.board.columns.findIndex(c => c.id === targetColumnId)

            if (draggedIndex === -1 || targetIndex === -1) return

            // Создаём новый массив с правильным порядком
            const newColumns = [...this.board.columns]
            const [draggedColumn] = newColumns.splice(draggedIndex, 1)
            newColumns.splice(targetIndex, 0, draggedColumn)

            // Обновляем position у всех колонок
            newColumns.forEach((col, index) => {
                col.position = index
            })

            // ВАЖНО: заменяем массив полностью, чтобы Vue отследил изменения
            this.board.columns = newColumns

            // Формируем payload для бэкенда
            const order = newColumns.map(c => c.id)

            try {
                await axios.put(`/api/boards/${this.board.uuid}/columns/reorder`, {
                    order: order,
                    board_uuid: this.board.uuid
                })
            } catch (error) {
                console.error('Ошибка сортировки колонок:', error)
                // Откатываем изменения
                const rollbackColumns = oldOrder
                    .map(id => newColumns.find(c => c.id === id))
                    .filter(Boolean)

                rollbackColumns.forEach((col, index) => {
                    col.position = index
                })

                this.board.columns = rollbackColumns
            }
        },

        // Загрузка всей доски
        async loadBoard(uuid) {
            this.loading = true
            this.error = null


            // сохраняем старые данные
            const oldBoard = JSON.parse(JSON.stringify(this.board))
            const oldColumns = JSON.parse(JSON.stringify(this.columns))

            this.taskPagination = {}

            try {
                const {data} = await axios.get(`/api/boards/${this.board.uuid}`)

                detectChanges(oldBoard, oldColumns, data, notifyChange)

                this.board = data
                this.columns = data.columns
            } catch (e) {
                this.error = 'Не удалось загрузить доску'
                console.error(e)
            } finally {
                this.loading = false
            }
        },
        async markTaskViewed(taskId) {
            await apiRequest('post', `/api/tasks/${taskId}/view`)
            const task = this.columns.flatMap(col => col.tasks).find(t => t.id === taskId)
            if (task) task.last_viewed_at = new Date().toISOString()
        },


        async updateColumn(columnId, payload) {
            const {data} = await apiRequest('put', `/api/columns/${columnId}`, payload)
            const idx = this.columns.findIndex(c => c.id === columnId)
            if (idx !== -1) this.columns[idx] = data
        },

        async deleteColumn(columnId) {
            await apiRequest('delete', `/api/columns/${columnId}`)
            this.columns = this.columns.filter(c => c.id !== columnId)
        },

        async clearBoard() {
            const ids = this.columns.map(c => c.id)
            for (const id of ids) {
                await this.deleteColumn(id)
            }
        },

        async createTask(uuid, task) {
            this.loading = true
            try {
                const {data} = await apiRequest('post', `/api/boards/${uuid}/tasks`, task)
                const column = this.getColumnById(data.column_id)
                if (column) column.tasks.unshift(data)
                return data
            } catch (e) {
                this.error = 'Не удалось создать задачу'
                throw e
            } finally {
                this.loading = false
            }
        },

        async updateTask(task) {
            this.loading = true
            this.error = null
            try {
                const {data} = await apiRequest('put', `/api/tasks/${task.id}`, task)
                const column = this.getColumnById(data.column_id)
                if (column) {
                    const idx = column.tasks.findIndex(t => t.id === data.id)
                    if (idx !== -1) column.tasks[idx] = data
                }
                return data
            } catch (e) {
                this.error = 'Не удалось обновить задачу'
                throw e
            } finally {
                this.loading = false
            }
        },

        async deleteTask(taskId) {
            await apiRequest('delete', `/api/tasks/${taskId}`)
            this.columns.forEach(col => {
                col.tasks = col.tasks.filter(t => t.id !== taskId)
            })
        },

        async moveTask(taskId, targetColumnId) {
            // Находим задачу
            let task = null
            let sourceColumn = null

            for (const col of this.board.columns) {
                const found = col.tasks.find(t => t.id === taskId)
                if (found) {
                    task = found
                    sourceColumn = col
                    break
                }
            }

            if (!task || sourceColumn.id === targetColumnId) return

            const targetColumn = this.board.columns.find(c => c.id === targetColumnId)
            if (!targetColumn) return

            // Удаляем из старой колонки
            sourceColumn.tasks = sourceColumn.tasks.filter(t => t.id !== taskId)
            sourceColumn.tasks_count--

            // Добавляем в новую колонку
            task.column_id = targetColumnId
            targetColumn.tasks.push(task)
            targetColumn.tasks_count++

            // Отправляем на бэкенд
            try {
                await axios.post(`/api/tasks/move`, {
                    task_id: taskId,
                    column_id: targetColumnId
                })
            } catch (error) {
                console.error('Ошибка перемещения задачи:', error)
                // Можно откатить изменения
            }
        },
        async renameColumn(columnId, newTitle) {
            const {data} = await apiRequest('put', `/api/columns/${columnId}`, {title: newTitle})
            const idx = this.columns.findIndex(c => c.id === columnId)
            if (idx !== -1) this.columns[idx].title = data.title
            return data
        },

        async reorderTask(taskId, targetTaskId, columnId) {
            const column = this.getColumnById(columnId)
            if (!column) return
            const tasks = [...column.tasks]
            const fromIndex = tasks.findIndex(t => t.id === taskId)
            const toIndex = tasks.findIndex(t => t.id === targetTaskId)
            const [moved] = tasks.splice(fromIndex, 1)
            tasks.splice(toIndex, 0, moved)
            column.tasks = tasks

            await apiRequest('put', `/api/columns/${columnId}/tasks/reorder`, {
                order: tasks.map(t => t.id)
            })
        },

        async duplicateTask(task) {
            const {data} = await apiRequest('post', `/api/tasks/${task.id}/duplicate`)
            const column = this.getColumnById(data.column_id)
            if (column) column.tasks.push(data)
            return data
        },

        async loadTags(uuid) {
            const {data} = await axios.get(`/api/boards/${uuid}/tags`)
            this.tags = data
        },

        async createTag(uuid, name, color = '#999999') {
            const {data} = await axios.post(`/api/boards/${uuid}/tags`, {name, color})
            this.tags.push(data)
            return data
        },

        async moveColumn(fromIndex, toIndex) {
            const cols = [...this.columns]
            const moved = cols.splice(fromIndex, 1)[0]
            cols.splice(toIndex, 0, moved)
            this.columns = cols
            const order = this.columns.map(c => c.id)
            return await apiRequest('put', `/api/boards/${this.board.uuid}/columns/reorder`, {order})
        },

        async deleteTag(tagId) {
            await axios.delete(`/api/tags/${tagId}`)
            this.tags = this.tags.filter(t => t.id !== tagId)
        },
        async refreshUuid(uuid) {
            this.loading = true
            this.error = null

            try {
                const {data} = await apiRequest('post', `/api/boards/${uuid}/refresh-uuid`)
                return data
            } catch (error) {
                this.error = error.response?.data?.message || 'Ошибка при обновлении ключа доски'
                console.error('Refresh UUID error:', error)
                throw error
            } finally {
                this.loading = false
            }
        }
    }
})

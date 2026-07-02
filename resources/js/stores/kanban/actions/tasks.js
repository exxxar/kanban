import axios from 'axios'
import { apiRequest } from '@/stores/utils/api.js'

export default {
    async createTask(uuid, task) {
        this.loading = true
        try {
            const { data } = await apiRequest('post', `/api/boards/${uuid}/tasks`, task)
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
            const { data } = await apiRequest('put', `/api/tasks/${task.id}`, task)
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

    async duplicateTask(task) {
        const { data } = await apiRequest('post', `/api/tasks/${task.id}/duplicate`)
        const column = this.getColumnById(data.column_id)
        if (column) column.tasks.push(data)
        return data
    },

    async moveTask(taskId, targetColumnId) {
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

        sourceColumn.tasks = sourceColumn.tasks.filter(t => t.id !== taskId)
        sourceColumn.tasks_count--

        task.column_id = targetColumnId
        targetColumn.tasks.push(task)
        targetColumn.tasks_count++

        try {
            await axios.post(`/api/tasks/move`, {
                task_id: taskId,
                column_id: targetColumnId
            })
        } catch (error) {
            console.error('Ошибка перемещения задачи:', error)
        }
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

    async loadTasks(columnId) {
        let page = 2
        const info = this.taskPagination[columnId]

        if (info && info.page < info.lastPage) {
            page = (info.page || 1) + 1
        }

        const { data } = await axios.get(`/api/columns/${columnId}/tasks?page=${page}`)
        const column = this.getColumnById(columnId)
        if (!column) return

        column.tasks = [...column.tasks, ...data.data]
        this.taskPagination[columnId] = {
            page: data.current_page,
            lastPage: data.last_page
        }
    },

    async markTaskViewed(taskId) {
        await apiRequest('post', `/api/tasks/${taskId}/view`)
        const task = this.columns.flatMap(col => col.tasks).find(t => t.id === taskId)
        if (task) task.last_viewed_at = new Date().toISOString()
    }
}

import axios from 'axios'
import { apiRequest } from '@/stores/utils/api.js'

export default {
    async createColumn(uuid, title) {
        const { data } = await apiRequest('post', `/api/boards/${uuid}/columns`, { title })
        this.columns.push({ ...data, tasks: [] })
        return data
    },

    async updateColumn(columnId, payload) {
        const { data } = await apiRequest('put', `/api/columns/${columnId}`, payload)
        const idx = this.columns.findIndex(c => c.id === columnId)
        if (idx !== -1) this.columns[idx] = data
    },

    async renameColumn(columnId, newTitle) {
        const { data } = await apiRequest('put', `/api/columns/${columnId}`, { title: newTitle })
        const idx = this.columns.findIndex(c => c.id === columnId)
        if (idx !== -1) this.columns[idx].title = data.title
        return data
    },

    async deleteColumn(columnId) {
        await apiRequest('delete', `/api/columns/${columnId}`)
        this.columns = this.columns.filter(c => c.id !== columnId)
    },

    async reorderColumns(draggedColumnId, targetColumnId) {
        if (draggedColumnId === targetColumnId) return

        const oldOrder = this.board.columns.map(c => c.id)

        const draggedIndex = this.board.columns.findIndex(c => c.id === draggedColumnId)
        const targetIndex = this.board.columns.findIndex(c => c.id === targetColumnId)

        if (draggedIndex === -1 || targetIndex === -1) return

        const newColumns = [...this.board.columns]
        const [draggedColumn] = newColumns.splice(draggedIndex, 1)
        newColumns.splice(targetIndex, 0, draggedColumn)

        newColumns.forEach((col, index) => {
            col.position = index
        })

        this.board.columns = newColumns

        const order = newColumns.map(c => c.id)

        try {
            await axios.put(`/api/boards/${this.board.uuid}/columns/reorder`, {
                order: order,
                board_uuid: this.board.uuid
            })
        } catch (error) {
            console.error('Ошибка сортировки колонок:', error)

            const rollbackColumns = oldOrder
                .map(id => newColumns.find(c => c.id === id))
                .filter(Boolean)

            rollbackColumns.forEach((col, index) => {
                col.position = index
            })

            this.board.columns = rollbackColumns
        }
    },

    async moveColumn(fromIndex, toIndex) {
        const cols = [...this.columns]
        const moved = cols.splice(fromIndex, 1)[0]
        cols.splice(toIndex, 0, moved)
        this.columns = cols
        const order = this.columns.map(c => c.id)
        return await apiRequest('put', `/api/boards/${this.board.uuid}/columns/reorder`, { order })
    },

    async updateColumnNotifications(columnId, settings) {
        try {
            await axios.post(`/api/columns/${columnId}/notifications`, {
                notifications: settings
            })

            const col = this.columns.find(c => c.id === columnId)
            if (col) col.notifications = settings
        } catch (e) {
            console.error('Ошибка обновления уведомлений', e)
        }
    }
}

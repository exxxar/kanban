import axios from 'axios'
import { apiRequest } from '@/stores/utils/api.js'
import { detectChanges, notifyChange } from '@/stores/utils/boardChanges'

export default {
    async loadBoard(uuid) {
        this.loading = true
        this.error = null

        const oldBoard = JSON.parse(JSON.stringify(this.board))
        const oldColumns = JSON.parse(JSON.stringify(this.columns))
        this.taskPagination = {}

        try {
            const { data } = await axios.get(`/api/boards/${this.board.uuid}`)

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

    async saveConfig(uuid, config) {
        const { data } = await apiRequest('post', `/api/boards/${uuid}/config`, config)
        this.board.config = data
    },

    async renameBoard(uuid, title) {
        const { data } = await apiRequest('put', `/api/boards/${uuid}`, { title })
        this.board.title = data.title
    },

    async refreshUuid(uuid) {
        this.loading = true
        this.error = null

        try {
            const { data } = await apiRequest('post', `/api/boards/${uuid}/refresh-uuid`)
            return data
        } catch (error) {
            this.error = error.response?.data?.message || 'Ошибка при обновлении ключа доски'
            console.error('Refresh UUID error:', error)
            throw error
        } finally {
            this.loading = false
        }
    },

    async clearBoard() {
        const ids = this.columns.map(c => c.id)
        for (const id of ids) {
            await this.deleteColumn(id)
        }
    }
}

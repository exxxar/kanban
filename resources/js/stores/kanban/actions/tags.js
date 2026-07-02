import axios from 'axios'

export default {
    async loadTags(uuid) {
        const { data } = await axios.get(`/api/boards/${uuid}/tags`)
        this.tags = data
    },

    async createTag(uuid, name, color = '#999999') {
        const { data } = await axios.post(`/api/boards/${uuid}/tags`, { name, color })
        this.tags.push(data)
        return data
    },

    async deleteTag(tagId) {
        await axios.delete(`/api/tags/${tagId}`)
        this.tags = this.tags.filter(t => t.id !== tagId)
    },

    async fetchLeadSources() {
        try {
            const response = await axios.get(`/api/boards/${this.board.uuid}/lead-sources`)
            this.leadSources = response.data.sources || []
            return this.leadSources
        } catch (error) {
            console.error('Ошибка загрузки источников:', error)
            return []
        }
    }
}

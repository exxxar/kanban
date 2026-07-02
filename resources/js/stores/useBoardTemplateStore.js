import { defineStore } from 'pinia'
import axios from 'axios'

export const useBoardTemplateStore = defineStore('boardTemplate', {
    state: () => ({
        templates: [],
        loading: false,
    }),

    actions: {

        async loadTemplates() {
            this.loading = true
            try {
                const { data } = await axios.get('/api/boards/choose-template')
                this.templates = data
            } catch (error) {
                console.error('Ошибка загрузки шаблонов:', error)
            } finally {
                this.loading = false
            }
        },

        async applyTemplate(uuid, templateId) {
            this.loading = true
            await axios.post(`/api/boards/${uuid}/apply-template`, {
                template: templateId
            })
            this.loading = false
        }
    }
})

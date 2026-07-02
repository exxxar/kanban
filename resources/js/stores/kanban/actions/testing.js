import axios from 'axios'

export default {
    async testCreateCard(type) {
        try {
            const { data } = await axios.post('/api/test/card', { type })
            return data
        } catch (e) {
            console.error('Ошибка тестового создания карточки', e)
        }
    },

    async testWebhook(payload = { url: null }) {
        this.webhookTestResult = null
        this.loading = true
        this.error = null
        try {
            const { data } = await axios.post('/api/test/webhook', payload)
            this.webhookTestResult = data
            return data
        } catch (e) {
            this.error = 'Ошибка теста вебхука'
            console.error(e)
        } finally {
            this.loading = false
        }
    },

    async testEmail(payload = { email: null }) {
        this.emailTestResult = null
        this.loading = true
        this.error = null
        try {
            const { data } = await axios.post('/api/test/email', payload)
            this.emailTestResult = data
            return data
        } catch (e) {
            this.error = 'Ошибка теста email'
            console.error(e)
        } finally {
            this.loading = false
        }
    },

    async testCrmIntegration(crm) {
        this.crmTestResult = null
        this.loading = true
        try {
            const { data } = await axios.post('/api/test/crm', crm)
            this.crmTestResult = data
            return data
        } catch (e) {
            this.crmTestResult = 'Ошибка подключения'
            console.error(e)
        } finally {
            this.loading = false
        }
    }
}

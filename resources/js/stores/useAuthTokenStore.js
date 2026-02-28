import { defineStore } from 'pinia'
import axios from 'axios'

export const useAuthTokenStore = defineStore('authToken', {
    state: () => ({
        token: null,
        loading: false,
    }),

    actions: {
        async fetchToken(uuid) {
            this.loading = true
            try {
                const response = await axios.post('/api/token',{
                    uuid: uuid
                })
                this.token = response.data.token
            } catch (e) {
                console.error('Ошибка получения токена', e)
            } finally {
                this.loading = false
            }
        }
    }
})

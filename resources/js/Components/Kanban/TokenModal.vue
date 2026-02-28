<template>
    <div class="modal fade show d-block" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fa-solid fa-key me-2"></i>
                        Ваш токен
                    </h5>
                    <button type="button" class="btn-close" @click="$emit('close')"></button>
                </div>

                <div class="modal-body">

                    <div v-if="store.loading" class="text-center py-3">
                        <div class="spinner-border text-primary"></div>
                        <p class="mt-2">Загрузка...</p>
                    </div>

                    <div v-else>
                        <label class="form-label fw-bold">Ваш токен:</label>
                        <pre class="bg-light p-3 rounded border">{{ store.token }}</pre>
                        <p class="mb-2 small">Документация к API
                            <a
                                class="underline fw-bold text-primary"
                                href="https://packagist.org/packages/exxxar/kanban-laravel" target="_blank">тут</a>
                        </p>
                        <button class="btn btn-outline-secondary mt-2" @click="copyToken">
                            <i class="fa-solid fa-copy me-2"></i>
                            Скопировать
                        </button>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="$emit('close')">
                        Закрыть
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- затемнение -->
    <div class="modal-backdrop fade show"></div>
</template>

<script>
import { useAuthTokenStore } from '@/stores/useAuthTokenStore.js'
import {useKanbanStore} from "@/stores/useKanbanStore.js";

export default {
    data() {
        return {
            store: useAuthTokenStore(),
            kanbanStore: useKanbanStore()
        }
    },

    mounted() {
        this.store.fetchToken(this.kanbanStore.board.uuid)
    },

    methods: {
        copyToken() {
            navigator.clipboard.writeText(this.store.token)
            alert("Токен успешно скопирован")
        }
    }
}
</script>

<style scoped>
.modal {
    display: block;
}
</style>

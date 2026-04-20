<template>
    <div class="btn-group-vertical w-100">
        <button class="btn btn-outline-primary  p-3" @click="test(1)">
            <i class="fa-solid fa-user"></i> Создание пользователя
        </button>

        <button class="btn btn-outline-primary p-3" @click="test(6)">
            <i class="fa-solid fa-user"></i> Карточка с отправкой сообщения
        </button>

        <button class="btn btn-outline-success  p-3" @click="test(2)">
            <i class="fa-solid fa-cart-shopping"></i> Создание заказа
        </button>

        <button class="btn btn-outline-secondary  p-3" @click="test(3)">
            <i class="fa-solid fa-align-left"></i> Текстовая карточка
        </button>

        <button class="btn btn-outline-warning  p-3" @click="test(4)">
            <i class="fa-solid fa-coins"></i> Приход финансов
        </button>

        <button class="btn btn-outline-dark  p-3" @click="test(5)">
            <i class="fa-solid fa-code"></i> Карточка с задачами
        </button>
    </div>

    <template v-if="result">
        <h6 class="fw-bold my-3">Результат выполнения</h6>
        <vue-json-pretty :data="result" :deep="3" />
    </template>
</template>

<script>
import { useKanbanStore } from '@/stores/useKanbanStore'
import VueJsonPretty from 'vue-json-pretty'
import 'vue-json-pretty/lib/styles.css'

export default {
    name: 'TestPanel',
    components: {
        VueJsonPretty
    },
    data() {
        return {
            result: null
        }
    },

    created() {
        this.store = useKanbanStore()
    },

    methods: {
        async test(type) {
            this.result = await this.store.testCreateCard(type)
        }
    }
}
</script>
<style scoped>
.btn-group .btn {
    font-size: 13px;
}
</style>

<template>
    <div class="test-panel">
        <!-- Заголовок -->
        <div class="panel-header">
            <div class="panel-icon">
                <i class="fa-solid fa-flask"></i>
            </div>
            <div class="panel-info">
                <h4 class="panel-title">Тестирование API</h4>
                <p class="panel-subtitle">Создайте тестовые карточки для текущей доски</p>
            </div>
        </div>

        <!-- Кнопки тестирования -->
        <div class="test-grid">
            <button
                v-for="action in testActions"
                :key="action.type"
                class="test-btn"
                :class="action.colorClass"
                @click="test(action.type)"
                :disabled="loading"
            >
                <div class="test-btn-icon">
                    <i :class="action.icon"></i>
                </div>
                <div class="test-btn-content">
                    <div class="test-btn-title">{{ action.title }}</div>
                    <div class="test-btn-desc">{{ action.description }}</div>
                </div>
                <i v-if="loading && currentType === action.type" class="fa-solid fa-spinner fa-spin test-btn-spinner"></i>
            </button>
        </div>

        <!-- Результат -->
        <div v-if="result !== null" class="result-section">
            <div class="result-header">
                <div class="result-icon">
                    <i class="fa-solid fa-check-circle"></i>
                </div>
                <div class="result-info">
                    <div class="result-title">
                        {{ result.ok ? 'Успешно создано' : 'Ошибка' }}
                    </div>
                    <div class="result-subtitle" v-if="result.type_name">
                        Тип: {{ result.type_name }}
                    </div>
                </div>
                <button class="result-clear" @click="clearResult" title="Очистить">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <div v-if="result.ok && result.task" class="result-task-info">
                <div class="task-info-row">
                    <span class="info-label">ID:</span>
                    <span class="info-value">#{{ result.task.id }}</span>
                </div>
                <div class="task-info-row">
                    <span class="info-label">Название:</span>
                    <span class="info-value">{{ result.task.title }}</span>
                </div>
                <div class="task-info-row">
                    <span class="info-label">Колонка:</span>
                    <span class="info-value">{{ result.column?.title }}</span>
                </div>
                <div v-if="result.custom_data_generated?.task && Object.keys(result.custom_data_generated.task).length > 0" class="task-info-row">
                    <span class="info-label">Кастомные поля:</span>
                    <span class="info-value">{{ Object.keys(result.custom_data_generated.task).length }} шт.</span>
                </div>
            </div>

            <div class="result-body">
                <vue-json-pretty :data="result" :deep="3" :showLength="true" />
            </div>
        </div>

        <!-- Пустое состояние -->
        <div v-else-if="!loading" class="empty-result">
            <i class="fa-regular fa-circle-question"></i>
            <p>Нажмите на любую кнопку для тестирования API</p>
        </div>
    </div>
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
            store: null,
            result: null,
            loading: false,
            currentType: null,
            testActions: [
                {
                    type: 1,
                    title: 'Задача',
                    description: 'Обычная карточка задачи',
                    icon: 'fa-solid fa-list-check',
                    colorClass: 'color-task'
                },
                {
                    type: 2,
                    title: 'Клиент',
                    description: 'CRM-карточка клиента',
                    icon: 'fa-solid fa-user-tie',
                    colorClass: 'color-client'
                },
                {
                    type: 6,
                    title: 'Заказ',
                    description: 'Карточка с заказом',
                    icon: 'fa-solid fa-cart-shopping',
                    colorClass: 'color-order'
                },
                {
                    type: 3,
                    title: 'Текст',
                    description: 'Текстовая заметка',
                    icon: 'fa-solid fa-align-left',
                    colorClass: 'color-text'
                },
                {
                    type: 4,
                    title: 'Финансы',
                    description: 'Финансовая операция',
                    icon: 'fa-solid fa-coins',
                    colorClass: 'color-finance'
                },
                {
                    type: 5,
                    title: 'Разработка',
                    description: 'Техническая задача',
                    icon: 'fa-solid fa-code',
                    colorClass: 'color-dev'
                }
            ]
        }
    },

    created() {
        this.store = useKanbanStore()
    },

    methods: {
        async test(type) {
            this.loading = true
            this.currentType = type
            this.result = null

            try {
                const response = await axios.post('/api/test/card', {
                    type: type,
                    board_uuid: this.store.board.uuid // ← Передаём UUID текущей доски
                })
                this.result = response.data

                // Обновляем доску, чтобы увидеть новую задачу
                await this.store.loadBoard(this.store.board.uuid)

            } catch (error) {
                console.error('Ошибка тестирования:', error)
                this.result = {
                    ok: false,
                    error: error.response?.data?.message || error.message
                }
            } finally {
                this.loading = false
                this.currentType = null
            }
        },

        clearResult() {
            this.result = null
        }
    }
}
</script>

<style scoped>
.test-panel {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

/* === ЗАГОЛОВОК ПАНЕЛИ === */
.panel-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding-bottom: 12px;
    border-bottom: 1px solid #e9ecef;
}

.panel-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.panel-title {
    font-size: 15px;
    font-weight: 700;
    color: #212529;
    margin: 0 0 2px 0;
}

.panel-subtitle {
    font-size: 11px;
    color: #6c757d;
    margin: 0;
}

/* === GRID КНОПОК === */
.test-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* ← было 2 */
    gap: 10px;
}


/* === КНОПКА ТЕСТА === */
.test-btn {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.2s;
    text-align: left;
    position: relative;
}

.test-btn:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.test-btn:active:not(:disabled) {
    transform: translateY(0);
}

.test-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.test-btn-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
    color: white;
}

.test-btn-content {
    flex: 1;
    min-width: 0;
}

.test-btn-title {
    font-size: 13px;
    font-weight: 600;
    color: #212529;
    margin-bottom: 1px;
}

.test-btn-desc {
    font-size: 10px;
    color: #6c757d;
    line-height: 1.3;
}

.test-btn-spinner {
    font-size: 14px;
    color: #0d6efd;
    flex-shrink: 0;
}

/* === ЦВЕТОВЫЕ КЛАССЫ === */
.color-task .test-btn-icon {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
}
.color-task:hover:not(:disabled) {
    border-color: #0d6efd;
    background: #f8f9ff;
}

.color-client .test-btn-icon {
    background: linear-gradient(135deg, #7c3aed 0%, #6d28d9 100%);
}
.color-client:hover:not(:disabled) {
    border-color: #7c3aed;
    background: #faf8ff;
}

.color-text .test-btn-icon {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
}
.color-text:hover:not(:disabled) {
    border-color: #6c757d;
    background: #f8f9fa;
}

.color-finance .test-btn-icon {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}
.color-finance:hover:not(:disabled) {
    border-color: #f59e0b;
    background: #fffdf8;
}

.color-dev .test-btn-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}
.color-dev:hover:not(:disabled) {
    border-color: #10b981;
    background: #f8fffa;
}

/* === РЕЗУЛЬТАТ === */
.result-section {
    margin-top: 8px;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    animation: slideUp 0.3s ease;
}

@keyframes slideUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.result-header {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    padding: 14px 18px;
    display: flex;
    align-items: center;
    gap: 12px;
    color: white;
}

.result-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.result-info {
    flex: 1;
}

.result-title {
    font-size: 14px;
    font-weight: 600;
}

.result-subtitle {
    font-size: 11px;
    opacity: 0.9;
    margin-top: 2px;
}

.result-clear {
    width: 32px;
    height: 32px;
    border: none;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: white;
    font-size: 14px;
    transition: all 0.2s;
}

.result-clear:hover {
    background: rgba(255, 255, 255, 0.25);
}

/* === ИНФОРМАЦИЯ О ЗАДАЧЕ === */
.result-task-info {
    padding: 12px 18px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.task-info-row {
    display: flex;
    gap: 8px;
    font-size: 12px;
}

.info-label {
    color: #6c757d;
    font-weight: 600;
    min-width: 120px;
}

.info-value {
    color: #212529;
    flex: 1;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.result-body {
    padding: 16px;
    background: #ffffff;
    max-height: 300px;
    overflow-y: auto;
}

/* === ПУСТОЕ СОСТОЯНИЕ === */
.empty-result {
    text-align: center;
    padding: 32px 20px;
    color: #adb5bd;
    border: 2px dashed #dee2e6;
    border-radius: 12px;
}

.empty-result i {
    font-size: 36px;
    margin-bottom: 12px;
    opacity: 0.5;
}

.empty-result p {
    font-size: 13px;
    margin: 0;
    color: #6c757d;
}

/* === СКРОЛЛБАР === */
.result-body::-webkit-scrollbar {
    width: 6px;
}

.result-body::-webkit-scrollbar-track {
    background: #f1f3f5;
}

.result-body::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 3px;
}

/* === АДАПТИВ === */
@media (max-width: 768px) {
    .test-grid {
        grid-template-columns: repeat(2, 1fr); /* на мобильных 2 колонки */
    }
}

.color-order .test-btn-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.color-order:hover:not(:disabled) {
    border-color: #10b981;
    background: #f8fffa;
}
</style>

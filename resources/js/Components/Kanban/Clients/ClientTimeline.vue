<template>
    <div class="client-timeline">
        <!-- Загрузка -->
        <div v-if="loading" class="timeline-loading">
            <div class="loading-spinner">
                <i class="fa-solid fa-spinner fa-spin"></i>
            </div>
            <p>Загрузка истории...</p>
        </div>

        <!-- Пустое состояние -->
        <div v-else-if="activities.length === 0" class="timeline-empty">
            <div class="empty-icon">
                <i class="fa-regular fa-clock-rotate-left"></i>
            </div>
            <h5>История пуста</h5>
            <p>Здесь будут отображаться все действия по клиенту</p>
        </div>

        <!-- Таймлайн -->
        <div v-else class="timeline-list">
            <div
                v-for="(group, dateKey) in groupedActivities"
                :key="dateKey"
                class="timeline-group"
            >
                <!-- Дата группы -->
                <div class="timeline-date">
                    <span class="date-text">{{ group.label }}</span>
                </div>

                <!-- События -->
                <div
                    v-for="activity in group.items"
                    :key="activity.id"
                    class="timeline-item"
                >
                    <!-- Линия + точка -->
                    <div class="timeline-track">
                        <div
                            class="timeline-dot"
                            :class="getActivityClass(activity.action_type)"
                        >
                            <i :class="getActivityIcon(activity.action_type)"></i>
                        </div>
                        <div class="timeline-line"></div>
                    </div>

                    <!-- Контент -->
                    <div class="timeline-content">
                        <div class="timeline-header">
                            <span class="timeline-title">{{ activity.title }}</span>
                            <span class="timeline-time">{{ formatTime(activity.created_at) }}</span>
                        </div>
                        <div v-if="activity.description" class="timeline-description">
                            {{ activity.description }}
                        </div>
                        <div v-if="activity.user_name" class="timeline-user">
                            <i class="fa-solid fa-user"></i>
                            {{ activity.user_name }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Кнопка "Загрузить ещё" -->
            <button
                v-if="hasMore"
                class="btn-load-more"
                @click="loadMore"
                :disabled="loadingMore"
            >
                <span v-if="loadingMore" class="spinner-small"></span>
                <template v-else>
                    <i class="fa-solid fa-chevron-down me-2"></i>
                    Загрузить ещё
                </template>
            </button>
        </div>
    </div>
</template>

<script>
import axios from 'axios'

export default {
    props: {
        clientId: {
            type: Number,
            required: true
        }
    },

    data() {
        return {
            loading: false,
            loadingMore: false,
            activities: [],
            currentPage: 1,
            lastPage: 1
        }
    },

    computed: {
        hasMore() {
            return this.currentPage < this.lastPage
        },

        groupedActivities() {
            const groups = {}

            this.activities.forEach(activity => {
                const date = new Date(activity.created_at)
                const dateKey = this.getDateKey(date)

                if (!groups[dateKey]) {
                    groups[dateKey] = {
                        label: this.getDateLabel(date),
                        items: []
                    }
                }

                groups[dateKey].items.push(activity)
            })

            return groups
        }
    },

    mounted() {
        this.loadActivities()
    },

    methods: {
        async loadActivities() {
            this.loading = true
            try {
                const response = await axios.get(`/api/clients/${this.clientId}/activities`)
                this.activities = response.data.data
                this.currentPage = response.data.current_page
                this.lastPage = response.data.last_page
            } catch (error) {
                console.error('Ошибка загрузки истории:', error)
            } finally {
                this.loading = false
            }
        },

        async loadMore() {
            this.loadingMore = true
            try {
                const response = await axios.get(`/api/clients/${this.clientId}/activities?page=${this.currentPage + 1}`)
                this.activities.push(...response.data.data)
                this.currentPage = response.data.current_page
                this.lastPage = response.data.last_page
            } catch (error) {
                console.error('Ошибка загрузки:', error)
            } finally {
                this.loadingMore = false
            }
        },

        getDateKey(date) {
            return date.toISOString().split('T')[0]
        },

        getDateLabel(date) {
            const now = new Date()
            const today = new Date(now.getFullYear(), now.getMonth(), now.getDate())
            const yesterday = new Date(today)
            yesterday.setDate(yesterday.getDate() - 1)

            const dateOnly = new Date(date.getFullYear(), date.getMonth(), date.getDate())

            if (dateOnly.getTime() === today.getTime()) {
                return 'Сегодня'
            } else if (dateOnly.getTime() === yesterday.getTime()) {
                return 'Вчера'
            } else {
                return new Intl.DateTimeFormat('ru-RU', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric'
                }).format(date)
            }
        },

        formatTime(dateString) {
            const date = new Date(dateString)
            return new Intl.DateTimeFormat('ru-RU', {
                hour: '2-digit',
                minute: '2-digit'
            }).format(date)
        },

        getActivityIcon(type) {
            const icons = {
                created: 'fa-solid fa-plus',
                updated: 'fa-solid fa-pen',
                stage_changed: 'fa-solid fa-arrow-right-arrow-left',
                cost_changed: 'fa-solid fa-ruble-sign',
                message: 'fa-solid fa-comment',
                comment: 'fa-solid fa-message',
                deal_closed: 'fa-solid fa-handshake',
                client_deleted: 'fa-solid fa-trash'
            }
            return icons[type] || 'fa-solid fa-circle-info'
        },

        getActivityClass(type) {
            const classes = {
                created: 'dot-success',
                updated: 'dot-info',
                stage_changed: 'dot-primary',
                cost_changed: 'dot-warning',
                message: 'dot-purple',
                comment: 'dot-purple',
                deal_closed: 'dot-success',
                client_deleted: 'dot-danger'
            }
            return classes[type] || 'dot-default'
        }
    }
}
</script>

<style scoped>
.client-timeline {
    min-height: 200px;
}

/* === ЗАГРУЗКА === */
.timeline-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 48px 20px;
    color: #6c757d;
}

.loading-spinner {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e7f1ff 0%, #cfe2ff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #0d6efd;
    margin-bottom: 12px;
}

.timeline-loading p {
    font-size: 14px;
    margin: 0;
}

/* === ПУСТОЕ СОСТОЯНИЕ === */
.timeline-empty {
    text-align: center;
    padding: 48px 20px;
    color: #adb5bd;
}

.empty-icon {
    width: 72px;
    height: 72px;
    margin: 0 auto 16px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e7f1ff 0%, #cfe2ff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: #0d6efd;
    opacity: 0.6;
}

.timeline-empty h5 {
    font-size: 16px;
    font-weight: 600;
    color: #495057;
    margin: 0 0 4px 0;
}

.timeline-empty p {
    font-size: 13px;
    margin: 0;
    color: #6c757d;
}

/* === ТАЙМЛАЙН === */
.timeline-list {
    display: flex;
    flex-direction: column;
}

.timeline-group {
    margin-bottom: 24px;
}

.timeline-group:last-child {
    margin-bottom: 0;
}

/* Дата группы */
.timeline-date {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
    padding-left: 44px;
    position: relative; /* ← ДОБАВИТЬ! */
}

.date-text {
    font-size: 13px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    background: #ffffff; /* ← Фон, чтобы перекрывать линию */
    padding: 2px 12px;
    border-radius: 6px;
    border: 1px solid #e9ecef;
    position: relative;
    z-index: 1;
}


.timeline-date::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e9ecef;
    min-width: 20px;
    max-width: 100px; /* ← Ограничиваем длину */
}

/* Убираем старый ::before, который ломал всё */
.timeline-date::before {
    display: none;
}

/* Элемент таймлайна */
.timeline-item {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
    position: relative;
}

.timeline-item:last-child .timeline-line {
    display: none;
}

/* Трек (линия + точка) */
.timeline-track {
    position: relative;
    display: flex;
    flex-direction: column;
    align-items: center;
    flex-shrink: 0;
    width: 44px;
}

.timeline-dot {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: white;
    flex-shrink: 0;
    z-index: 1;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.timeline-line {
    width: 2px;
    flex-grow: 1;
    background: #e9ecef;
    margin-top: 8px;
}

/* Цвета точек */
.dot-success {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.dot-info {
    background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%);
}

.dot-primary {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
}

.dot-warning {
    background: linear-gradient(135deg, #ffc107 0%, #ffb300 100%);
}

.dot-purple {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
}

.dot-danger {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
}

.dot-default {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
}

/* Контент */
.timeline-content {
    flex: 1;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 10px;
    padding: 12px 16px;
    min-width: 0;
    transition: all 0.2s;
}

.timeline-content:hover {
    background: #ffffff;
    border-color: #dee2e6;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.timeline-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 4px;
}

.timeline-title {
    font-size: 14px;
    font-weight: 600;
    color: #212529;
}

.timeline-time {
    font-size: 12px;
    color: #6c757d;
    flex-shrink: 0;
}

.timeline-description {
    font-size: 13px;
    color: #495057;
    line-height: 1.5;
    margin-bottom: 4px;
}

.timeline-user {
    font-size: 11px;
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 4px;
}

.timeline-user i {
    font-size: 10px;
}

/* Кнопка загрузки */
.btn-load-more {
    width: 100%;
    padding: 12px;
    margin-top: 16px;
    background: #ffffff;
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    color: #6c757d;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-load-more:hover:not(:disabled) {
    background: #f8f9fa;
    border-color: #0d6efd;
    color: #0d6efd;
}

.btn-load-more:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* Спиннер */
.spinner-small {
    width: 14px;
    height: 14px;
    border: 2px solid rgba(108, 117, 125, 0.3);
    border-top-color: #6c757d;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
</style>

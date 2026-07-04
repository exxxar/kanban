<template>
    <div class="order-card">
        <!-- Header -->
        <div class="order-header">
            <div class="order-icon">
                <i class="fa-solid fa-receipt"></i>
            </div>
            <div class="order-info">
                <div class="order-title">Заказ №{{ order.id }}</div>
                <div class="order-subtitle">{{ formatDate(order.created_at) }}</div>
            </div>
            <div class="order-badge" :class="statusClass">
                {{ statusText }}
            </div>
        </div>

        <!-- Customer -->
        <div class="order-section">
            <div class="section-title">
                <i class="fa-solid fa-user"></i>
                Клиент
            </div>
            <div class="section-content">
                <div class="info-row">
                    <span class="info-label">Имя:</span>
                    <span class="info-value">{{ order.customer?.name || 'Не указано' }}</span>
                </div>
                <div class="info-row" v-if="order.customer?.phone">
                    <span class="info-label">Телефон:</span>
                    <a :href="`tel:${order.customer.phone}`" class="info-link">
                        {{ order.customer.phone }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Summary -->
        <div class="order-section">
            <div class="section-title">
                <i class="fa-solid fa-calculator"></i>
                Итого
            </div>
            <div class="summary-grid">
                <div class="summary-item">
                    <div class="summary-label">Сумма</div>
                    <div class="summary-value price">{{ formatPrice(order.summary?.price) }}</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Товаров</div>
                    <div class="summary-value">{{ order.summary?.count }} шт.</div>
                </div>
                <div class="summary-item">
                    <div class="summary-label">Оплата</div>
                    <div class="summary-value">
                        <i :class="paymentIcon"></i>
                        {{ paymentText }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Message -->
        <div v-if="order.text" class="order-message">
            <div class="message-text" v-html="renderedText"></div>
        </div>

        <!-- Actions -->
        <div class="order-actions">
            <button class="action-btn" @click="$emit('view', order.id)">
                <i class="fa-solid fa-eye"></i>
                Подробнее
            </button>
            <button class="action-btn primary" @click="$emit('process', order.id)">
                <i class="fa-solid fa-check"></i>
                Принять
            </button>
        </div>
    </div>
</template>

<script>
import { marked } from 'marked'
import DOMPurify from 'dompurify'

export default {
    props: {
        order: {
            type: Object,
            required: true
        }
    },
    emits: ['view', 'process'],
    computed: {
        statusClass() {
            // Можно определить статус из payload или order.status
            return 'new'
        },
        statusText() {
            return 'Новый'
        },
        paymentIcon() {
            const type = this.order.payment_type
            const icons = {
                1: 'fa-solid fa-money-bill',      // Наличные
                2: 'fa-solid fa-credit-card',     // Карта
                3: 'fa-solid fa-wallet',          // Электронные
                4: 'fa-solid fa-qrcode'           // СБП
            }
            return icons[type] || 'fa-solid fa-credit-card'
        },
        paymentText() {
            const type = this.order.payment_type
            const texts = {
                1: 'Наличные',
                2: 'Карта',
                3: 'Электронные',
                4: 'СБП'
            }
            return texts[type] || 'Не указано'
        },
        renderedText() {
            if (!this.order.text) return ''
            const html = marked(this.order.text)
            return DOMPurify.sanitize(html)
        }
    },
    methods: {
        formatDate(dateString) {
            if (!dateString) return ''
            const date = new Date(dateString)
            return new Intl.DateTimeFormat('ru-RU', {
                day: '2-digit',
                month: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            }).format(date)
        },
        formatPrice(price) {
            if (!price && price !== 0) return '0 ₽'
            return new Intl.NumberFormat('ru-RU', {
                style: 'currency',
                currency: 'RUB',
                minimumFractionDigits: 0
            }).format(price)
        }
    }
}
</script>

<style scoped>
.order-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

/* Header */
.order-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.order-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    backdrop-filter: blur(10px);
}

.order-info {
    flex: 1;
    min-width: 0;
}

.order-title {
    font-size: 15px;
    font-weight: 700;
    margin-bottom: 2px;
}

.order-subtitle {
    font-size: 11px;
    opacity: 0.9;
}

.order-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 600;
    background: rgba(255, 255, 255, 0.25);
}

.order-badge.new {
    background: rgba(16, 185, 129, 0.3);
}

/* Sections */
.order-section {
    padding: 12px 16px;
    border-bottom: 1px solid #f1f3f5;
}

.order-section:last-of-type {
    border-bottom: none;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
    font-weight: 700;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 8px;
}

.section-title i {
    color: #667eea;
    font-size: 10px;
}

.section-content {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.info-row {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
}

.info-label {
    color: #6c757d;
    font-weight: 500;
    min-width: 80px;
}

.info-value {
    color: #212529;
    font-weight: 600;
}

.info-link {
    color: #0d6efd;
    text-decoration: none;
    font-weight: 600;
}

.info-link:hover {
    text-decoration: underline;
}

/* Summary Grid */
.summary-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}

.summary-item {
    display: flex;
    flex-direction: column;
    gap: 4px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
}

.summary-label {
    font-size: 10px;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    font-weight: 600;
}

.summary-value {
    font-size: 14px;
    font-weight: 700;
    color: #212529;
    display: flex;
    align-items: center;
    gap: 4px;
}

.summary-value.price {
    color: #10b981;
    font-size: 16px;
}

.summary-value i {
    font-size: 12px;
    color: #667eea;
}

/* Message */
.order-message {
    padding: 12px 16px;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}

.message-text {
    font-size: 13px;
    line-height: 1.5;
    color: #495057;
    white-space: pre-wrap;
}

.message-text :deep(b),
.message-text :deep(strong) {
    color: #212529;
    font-weight: 700;
}

.message-text :deep(code) {
    background: rgba(0, 0, 0, 0.05);
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px;
    font-family: 'Courier New', monospace;
}

/* Actions */
.order-actions {
    display: flex;
    gap: 8px;
    padding: 12px 16px;
    background: #ffffff;
    border-top: 1px solid #e9ecef;
}

.action-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px 16px;
    border: 1px solid #dee2e6;
    background: #ffffff;
    color: #495057;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.action-btn:hover {
    background: #f8f9fa;
    border-color: #adb5bd;
}

.action-btn.primary {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border-color: transparent;
    color: white;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

.action-btn.primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.action-btn i {
    font-size: 12px;
}

/* Адаптив */
@media (max-width: 640px) {
    .summary-grid {
        grid-template-columns: 1fr;
    }

    .order-actions {
        flex-direction: column;
    }
}
</style>

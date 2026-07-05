<template>
    <div class="message-renderer">
        <!-- 1. СНАЧАЛА проверяем заказ (более специфичный) -->
        <template v-if="isOrder">
            <OrderCard
                v-if="isOrder"
                :order="orderData"
                :task-id="msg.task_id"
                @view="handleOrderView"
                @accepted="handleOrderAccepted"
            />
        </template>

        <!-- 2. Потом статусы и системные -->
        <template v-else-if="isStatusChange">
            <div class="status-message" :class="statusClass">
                <i :class="statusIcon"></i>
                <span>{{ parsedText }}</span>
            </div>
        </template>

        <!-- 3. Системные сообщения -->
        <template v-else-if="isSystem">
            <div class="system-message">
                <i class="fa-solid fa-circle-info"></i>
                <span>{{ parsedText }}</span>
            </div>
        </template>

        <!-- 4. Платёжные сообщения -->
        <template v-else-if="isPayment">
            <PaymentMessage :message="message" />
        </template>

        <!-- 5. Обычное сообщение с markdown -->
        <template v-else>
            <div class="message-content" v-html="renderedMarkdown"></div>
        </template>
    </div>
</template>

<script>
import { marked } from 'marked'
import DOMPurify from 'dompurify'
import OrderCard from './OrderCard.vue'
import PaymentMessage from './PaymentMessage.vue'

marked.setOptions({
    breaks: true,
    gfm: true,
    headerIds: false,
    mangle: false
})

export default {
    components: { OrderCard, PaymentMessage },
    props: {
        message: {
            type: Object,
            required: true
        }
    },
    computed: {
        // === ТИПЫ СООБЩЕНИЙ (по приоритету!) ===

        /** Заказ — самый специфичный, проверяется первым */
        isOrder() {
            const type = this.message.payload?.type
            return [
                'new_order',
                'new_client_and_order',
                'order_created',
                'order_updated'
            ].includes(type)
        },

        /** Изменение статуса */
        isStatusChange() {
            return this.message.payload?.type === 'status_change'
        },

        /** Платёж */
        isPayment() {
            const type = this.message.payload?.type
            return [
                'payment_received',
                'payment_instruction',
                'invoice_attached'
            ].includes(type)
        },

        /** Системное (общее) */
        isSystem() {
            return this.message.sender_type === 'system'
        },

        // === ДАННЫЕ ДЛЯ КАРТОЧКИ ЗАКАЗА ===
        orderData() {
            const payload = this.message.payload || {}
            return {
                id: payload.order_id,
                created_at: this.message.created_at,
                customer: {
                    name: payload.customer_name,
                    phone: payload.customer_phone
                },
                summary: {
                    price: payload.summary_price,
                    count: payload.summary_count
                },
                payment_type: payload.payment_type,
                source: payload.source,
                tenant_id: payload.tenant_id,
                text: this.message.message,
                type: payload.type
            }
        },

        // === СТАТУСНЫЕ СООБЩЕНИЯ ===
        statusClass() {
            const status = this.message.payload?.status
            return {
                'new': 'status-new',
                'processing': 'status-processing',
                'ready': 'status-ready',
                'delivered': 'status-delivered',
                'cancelled': 'status-cancelled'
            }[status] || 'status-new'
        },

        statusIcon() {
            const status = this.message.payload?.status
            return {
                'new': 'fa-solid fa-sparkles',
                'processing': 'fa-solid fa-gear',
                'ready': 'fa-solid fa-circle-check',
                'delivered': 'fa-solid fa-truck-fast',
                'cancelled': 'fa-solid fa-circle-xmark'
            }[status] || 'fa-solid fa-circle-info'
        },

        parsedText() {
            return this.message.message || ''
        },

        renderedMarkdown() {
            if (!this.message.message) return ''
            const html = marked(this.message.message)
            return DOMPurify.sanitize(html, {
                ALLOWED_TAGS: ['b', 'i', 'em', 'strong', 'code', 'pre', 'br', 'p', 'ul', 'ol', 'li', 'a'],
                ALLOWED_ATTR: ['href', 'target']
            })
        }
    },
    methods: {
        // ... существующие методы ...

        /**
         * Открыть детали заказа
         */
        handleOrderView({ orderId, taskId, order }) {
            // Вариант 1: Открыть модалку
            this.$emit('show-order-modal', { orderId, taskId, order })

            // Вариант 2: Перейти на страницу заказа
            // window.open(`/orders/${orderId}`, '_blank')
        },

        /**
         * Заказ принят — обновляем UI
         */
        handleOrderAccepted({ taskId, orderId, data }) {
            console.log('[Chat] Заказ принят:', { taskId, orderId, data })

            // Можно добавить уведомление в общий список
            // или обновить задачу в store
        }
    }
}
</script>

<style scoped>
.message-renderer {
    width: 100%;
}

/* Системное сообщение */
.system-message {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: rgba(108, 117, 125, 0.1);
    border-left: 3px solid #6c757d;
    border-radius: 6px;
    font-size: 12px;
    color: #495057;
}

.system-message i {
    color: #6c757d;
}

/* Статусные сообщения */
.status-message {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 600;
}

.status-new {
    background: rgba(13, 110, 253, 0.1);
    border-left: 3px solid #0d6efd;
    color: #084298;
}

.status-processing {
    background: rgba(255, 193, 7, 0.15);
    border-left: 3px solid #ffc107;
    color: #664d03;
}

.status-ready {
    background: rgba(16, 185, 129, 0.1);
    border-left: 3px solid #10b981;
    color: #065f46;
}

.status-delivered {
    background: rgba(16, 185, 129, 0.15);
    border-left: 3px solid #059669;
    color: #064e3b;
}

.status-cancelled {
    background: rgba(239, 68, 68, 0.1);
    border-left: 3px solid #ef4444;
    color: #991b1b;
}

.status-message i {
    font-size: 14px;
}

/* Markdown контент */
.message-content {
    word-wrap: break-word;
    line-height: 1.5;
}

.message-content :deep(code) {
    background: rgba(0, 0, 0, 0.1);
    padding: 2px 6px;
    border-radius: 4px;
    font-size: 12px;
    font-family: 'Courier New', monospace;
}

.message-content :deep(b),
.message-content :deep(strong) {
    font-weight: 700;
}

.message-content :deep(a) {
    color: inherit;
    text-decoration: underline;
}
</style>

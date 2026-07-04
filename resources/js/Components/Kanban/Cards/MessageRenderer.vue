<template>
    <div class="message-renderer">
        <!-- Системное сообщение -->
        <template v-if="isSystem">
            <div class="system-message">
                <i class="fa-solid fa-circle-info"></i>
                <span>{{ parsedText }}</span>
            </div>
        </template>

        <!-- Заказ -->
        <template v-else-if="isOrder">
            <OrderCard :order="orderData" />
        </template>

        <!-- Обычное сообщение с markdown -->
        <template v-else>
            <div class="message-content" v-html="renderedMarkdown"></div>
        </template>
    </div>
</template>

<script>
import { marked } from 'marked'
import DOMPurify from 'dompurify'
import OrderCard from '@/Components/Kanban/Cards/OrderCard.vue'

// Настройки marked
marked.setOptions({
    breaks: true,
    gfm: true,
    headerIds: false,
    mangle: false
})

export default {
    components: { OrderCard },
    props: {
        message: {
            type: Object,
            required: true
        }
    },
    computed: {
        isSystem() {
            return this.message.sender_type === 'system' ||
                this.message.payload?.type === 'status_change'
        },
        isOrder() {
            return this.message.payload?.type === 'new_order' ||
                this.message.payload?.type === 'new_client_and_order'
        },
        orderData() {
            return {
                id: this.message.payload?.order_id,
                customer: {
                    name: this.message.payload?.customer_name,
                    phone: this.message.payload?.customer_phone
                },
                summary: {
                    price: this.message.payload?.summary_price,
                    count: this.message.payload?.summary_count
                },
                payment_type: this.message.payload?.payment_type,
                text: this.message.message
            }
        },
        parsedText() {
            return this.message.message || ''
        },
        renderedMarkdown() {
            if (!this.message.message) return ''

            // Рендерим markdown
            const html = marked(this.message.message)

            // Очищаем от XSS
            return DOMPurify.sanitize(html, {
                ALLOWED_TAGS: ['b', 'i', 'em', 'strong', 'code', 'pre', 'br', 'p', 'ul', 'ol', 'li'],
                ALLOWED_ATTR: []
            })
        }
    }
}
</script>

<style scoped>
.message-renderer {
    width: 100%;
}

.system-message {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: rgba(16, 185, 129, 0.1);
    border-left: 3px solid #10b981;
    border-radius: 6px;
    font-size: 12px;
    color: #065f46;
}

.system-message i {
    color: #10b981;
}

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

.message-content :deep(em) {
    font-style: italic;
    opacity: 0.9;
}
</style>

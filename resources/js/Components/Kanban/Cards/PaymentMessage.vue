<template>
    <div class="payment-message">
        <div class="payment-icon">
            <i :class="icon"></i>
        </div>
        <div class="payment-content">
            <div class="payment-title">{{ title }}</div>
            <div class="payment-text" v-if="message.message" v-html="renderedText"></div>
        </div>
    </div>
</template>

<script>
import { marked } from 'marked'
import DOMPurify from 'dompurify'

export default {
    props: {
        message: Object
    },
    computed: {
        type() {
            return this.message.payload?.type
        },
        icon() {
            const icons = {
                'payment_received': 'fa-solid fa-circle-check',
                'payment_instruction': 'fa-solid fa-credit-card',
                'invoice_attached': 'fa-solid fa-file-invoice'
            }
            return icons[this.type] || 'fa-solid fa-money-bill'
        },
        title() {
            const titles = {
                'payment_received': '💰 Оплата получена',
                'payment_instruction': '💳 Инструкция по оплате',
                'invoice_attached': '📄 Чек прикреплён'
            }
            return titles[this.type] || 'Платёж'
        },
        renderedText() {
            if (!this.message.message) return ''
            return DOMPurify.sanitize(marked(this.message.message))
        }
    }
}
</script>

<style scoped>
.payment-message {
    display: flex;
    gap: 10px;
    padding: 10px 12px;
    background: linear-gradient(135deg, rgba(245, 158, 11, 0.1) 0%, rgba(251, 191, 36, 0.1) 100%);
    border: 1px solid rgba(245, 158, 11, 0.3);
    border-radius: 8px;
}

.payment-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
}

.payment-content {
    flex: 1;
    min-width: 0;
}

.payment-title {
    font-size: 13px;
    font-weight: 700;
    color: #92400e;
    margin-bottom: 4px;
}

.payment-text {
    font-size: 12px;
    color: #78350f;
    line-height: 1.4;
}

.payment-text :deep(b),
.payment-text :deep(strong) {
    color: #451a03;
}
</style>

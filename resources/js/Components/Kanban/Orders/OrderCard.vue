<template>
    <div class="order-card">
        <!-- Заголовок заказа -->
        <div class="order-header">
            <div class="order-icon">
                <i class="fa-solid fa-cart-shopping"></i>
            </div>
            <div class="order-info">
                <div class="order-id">Заказ #{{ order.order_id }}</div>
                <div class="order-date">{{ formatDate(order.meta?.generated_at) }}</div>
            </div>
            <div class="order-total">
                {{ formatMoney(order.sum) }}
            </div>
        </div>

        <!-- Информация о клиенте -->
        <div class="order-customer">
            <div class="customer-row">
                <i class="fa-solid fa-user customer-icon"></i>
                <span class="customer-name">{{ order.customer?.name }}</span>
            </div>
            <div class="customer-row">
                <i class="fa-solid fa-phone customer-icon"></i>
                <a :href="`tel:${order.customer?.phone}`" class="customer-link">
                    {{ order.customer?.phone }}
                </a>
            </div>
            <div v-if="order.customer?.email" class="customer-row">
                <i class="fa-solid fa-envelope customer-icon"></i>
                <a :href="`mailto:${order.customer?.email}`" class="customer-link">
                    {{ order.customer?.email }}
                </a>
            </div>
            <div v-if="order.customer?.address" class="customer-row">
                <i class="fa-solid fa-location-dot customer-icon"></i>
                <span class="customer-address">{{ order.customer?.address }}</span>
            </div>
        </div>

        <!-- Позиции заказа -->
        <div class="order-items">
            <div class="items-header">
                <span>Позиции</span>
                <span class="items-count">{{ order.items?.length || 0 }}</span>
            </div>

            <div class="items-list">
                <div
                    v-for="(item, index) in order.items"
                    :key="index"
                    class="item-row"
                >
                    <div class="item-info">
                        <span class="item-name">{{ item.name }}</span>
                        <span class="item-qty">× {{ item.qty }}</span>
                    </div>
                    <div class="item-total">
                        {{ formatMoney(item.total || item.price * item.qty) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Мета-информация -->
        <div v-if="order.meta" class="order-meta">
            <div v-if="order.meta.payment_status" class="meta-badge" :class="paymentStatusClass">
                <i :class="paymentStatusIcon"></i>
                {{ paymentStatusLabel }}
            </div>
            <div v-if="order.meta.delivery_type" class="meta-badge delivery">
                <i class="fa-solid fa-truck"></i>
                {{ deliveryLabel }}
            </div>
        </div>

        <!-- Комментарий -->
        <div v-if="order.meta?.comment" class="order-comment">
            <i class="fa-solid fa-comment"></i>
            <span>{{ order.meta.comment }}</span>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        order: { type: Object, required: true }
    },

    computed: {
        paymentStatusClass() {
            const status = this.order.meta?.payment_status
            return {
                'paid': 'status-paid',
                'pending': 'status-pending',
                'failed': 'status-failed'
            }[status] || 'status-pending'
        },

        paymentStatusIcon() {
            const status = this.order.meta?.payment_status
            return {
                'paid': 'fa-solid fa-check-circle',
                'pending': 'fa-solid fa-clock',
                'failed': 'fa-solid fa-times-circle'
            }[status] || 'fa-solid fa-clock'
        },

        paymentStatusLabel() {
            const status = this.order.meta?.payment_status
            return {
                'paid': 'Оплачен',
                'pending': 'Ожидает оплаты',
                'failed': 'Ошибка оплаты'
            }[status] || 'Ожидает оплаты'
        },

        deliveryLabel() {
            const type = this.order.meta?.delivery_type
            return {
                'courier': 'Курьер',
                'pickup': 'Самовывоз',
                'delivery': 'Доставка'
            }[type] || 'Доставка'
        }
    },

    methods: {
        formatMoney(amount) {
            if (!amount) return '0 ₽'
            return new Intl.NumberFormat('ru-RU', {
                style: 'currency',
                currency: 'RUB',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(amount)
        },

        formatDate(dateString) {
            if (!dateString) return ''
            const date = new Date(dateString)
            return new Intl.DateTimeFormat('ru-RU', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }).format(date)
        }
    }
}
</script>

<style scoped>
.order-card {
    background: #ffffff;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
}

/* === HEADER === */
.order-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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
    flex-shrink: 0;
}

.order-info {
    flex: 1;
    min-width: 0;
}

.order-id {
    font-size: 15px;
    font-weight: 700;
    margin-bottom: 2px;
}

.order-date {
    font-size: 11px;
    opacity: 0.9;
}

.order-total {
    font-size: 18px;
    font-weight: 700;
    flex-shrink: 0;
}

/* === CUSTOMER === */
.order-customer {
    padding: 14px 16px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.customer-row {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 13px;
}

.customer-icon {
    width: 16px;
    color: #6c757d;
    flex-shrink: 0;
}

.customer-name {
    font-weight: 600;
    color: #212529;
}

.customer-link {
    color: #0d6efd;
    text-decoration: none;
}

.customer-link:hover {
    text-decoration: underline;
}

.customer-address {
    color: #495057;
}

/* === ITEMS === */
.order-items {
    padding: 14px 16px;
}

.items-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
    font-size: 13px;
    font-weight: 600;
    color: #495057;
}

.items-count {
    background: #e9ecef;
    color: #495057;
    padding: 2px 8px;
    border-radius: 8px;
    font-size: 11px;
}

.items-list {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.item-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 10px;
    background: #f8f9fa;
    border-radius: 8px;
}

.item-info {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
    min-width: 0;
}

.item-name {
    font-size: 13px;
    color: #212529;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.item-qty {
    font-size: 11px;
    color: #6c757d;
    flex-shrink: 0;
}

.item-total {
    font-size: 13px;
    font-weight: 600;
    color: #10b981;
    flex-shrink: 0;
    margin-left: 8px;
}

/* === META === */
.order-meta {
    display: flex;
    gap: 6px;
    padding: 12px 16px;
    border-top: 1px solid #e9ecef;
    flex-wrap: wrap;
}

.meta-badge {
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 600;
}

.meta-badge i {
    font-size: 10px;
}

.status-paid {
    background: #d1e7dd;
    color: #0f5132;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-failed {
    background: #f8d7da;
    color: #842029;
}

.meta-badge.delivery {
    background: #e7f1ff;
    color: #0d6efd;
}

/* === COMMENT === */
.order-comment {
    display: flex;
    gap: 8px;
    padding: 12px 16px;
    background: #fff9e6;
    border-top: 1px solid #ffe58f;
    font-size: 12px;
    color: #856404;
}

.order-comment i {
    flex-shrink: 0;
    margin-top: 1px;
}
</style>

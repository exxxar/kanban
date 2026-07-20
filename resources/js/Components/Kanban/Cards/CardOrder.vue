<template>
    <div class="card-order-wrapper">
        <!-- HEADER: Статус и сумма -->
        <div class="order-header">
            <div class="order-id-badge">
                <i class="fa-solid fa-receipt me-1"></i>
                Заказ #{{ orderId }}
            </div>
            <div class="order-total">
                {{ formatPrice(totalPrice) }}
            </div>
        </div>

        <!-- BODY: Основная информация -->
        <div class="order-body">
            <!-- Клиент -->
            <div class="info-block">
                <div class="info-label"><i class="fa-solid fa-user me-1"></i> Клиент</div>
                <div class="info-value" :class="{ 'text-muted fst-italic': !customerName }">
                    {{ getVal(customerName) }}
                </div>
                <div class="info-value text-muted small">
                    <i class="fa-solid fa-phone me-1"></i> {{ getVal(customerPhone) }}
                </div>
            </div>

            <!-- Доставка / Самовывоз -->
            <div class="info-block">
                <div class="info-label"><i class="fa-solid fa-truck me-1"></i> Тип получения</div>
                <div class="info-value">
        <span :class="isPickup ? 'badge bg-warning text-dark' : 'badge bg-info'">
            {{ isPickup ? 'Самовывоз' : 'Доставка' }}
        </span>
                </div>
                <div v-if="!isPickup" class="info-value text-muted small mt-1">
                    <i class="fa-solid fa-location-dot me-1"></i>
                    <span :class="{ 'fst-italic': !deliveryNote }">{{ getVal(deliveryNote) }}</span>
                    <span v-if="deliveryPrice > 0" class="ms-2 text-primary">
            (Доставка: {{ formatPrice(deliveryPrice) }})
        </span>
                </div>
            </div>

            <!-- Оплата -->
            <div class="info-block">
                <div class="info-label"><i class="fa-solid fa-credit-card me-1"></i> Оплата</div>
                <div class="info-value" :class="{ 'text-muted fst-italic': paymentLabel === 'Неизвестно' }">
                    {{ getVal(paymentLabel, 'Неизвестно') }}
                </div>
            </div>

            <!-- Состав заказа -->
            <div class="info-block products-block">
                <div class="info-label"><i class="fa-solid fa-basket-shopping me-1"></i> Состав заказа ({{ totalCount }} поз.)</div>
                <ul class="products-list">
                    <template v-if="hasProductDetails">
                        <li v-for="(detail, idx) in productDetails" :key="idx" class="product-item">
                            <span class="product-from text-muted small">🏪 {{ detail.from }}</span>
                            <ul class="product-parts">
                                <li v-for="p in detail.products" :key="p.name" class="product-part">
                                    <span class="part-name">{{ p.name }}</span>
                                    <span class="part-count">x{{ p.count }}</span>
                                    <span class="part-price">{{ formatPrice(p.price) }}</span>
                                </li>
                            </ul>
                        </li>
                    </template>
                    <template v-else-if="hasLegacyParts">
                        <li v-for="(p, idx) in legacyParts" :key="idx" class="product-item legacy">
                            <span class="part-name">{{ p.name }}</span>
                            <span class="part-count">x{{ p.amount || 1 }}</span>
                        </li>
                    </template>
                    <li v-else class="text-muted small fst-italic">
                        Детали заказа не найдены в данных карточки
                    </li>
                </ul>
            </div>
        </div>

        <!-- FOOTER: Действия и отладка -->
        <div class="order-footer">
            <button
                class="btn-debug-json"
                @click="showJson = !showJson"
                :class="{ active: showJson }"
            >
                <i class="fa-solid fa-code me-1"></i>
                {{ showJson ? 'Скрыть JSON' : 'Показать JSON (Debug)' }}
            </button>

            <Transition name="slide-fade">
                <div v-if="showJson" class="json-debug-wrapper">
                    <div class="json-header">
                        <span>Raw Card Data</span>
                        <button class="btn-copy" @click="copyJson" title="Копировать">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                    </div>
                    <pre class="json-content"><code>{{ formattedJson }}</code></pre>
                </div>
            </Transition>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        card: {
            type: Object,
            required: true
        }
    },
    data() {
        return {
            showJson: false
        }
    },
    computed: {
        // Извлекаем данные из разных возможных мест (в зависимости от того, как бэкенд маппит Order в Task)
        orderData() {
            return this.card.data || this.card.custom_data || {};
        },
        clientData() {
            return this.card.client || {};
        },

        orderId() {
            return this.orderData.last_order_id || this.orderData.order_id || this.card.id;
        },
        customerName() {
            return this.clientData.company_name || this.clientData.contact_person || this.card.title || 'Неизвестный клиент';
        },
        customerPhone() {
            return this.clientData.phone || this.orderData.customer_phone;
        },
        totalPrice() {
            return this.orderData.summary_price || this.clientData.cost || 0;
        },
        totalCount() {
            return this.orderData.summary_count || this.orderData.product_count || 0;
        },
        isPickup() {
            return this.clientData.placement_type === 'Самовывоз' || this.orderData.need_pickup === true;
        },
        deliveryPrice() {
            return this.orderData.delivery_price || 0;
        },
        deliveryNote() {
            return this.orderData.delivery_note || this.clientData.address || '';
        },
        paymentType() {
            return this.orderData.payment_type || 4;
        },
        paymentLabel() {
            const types = {
                0: 'Не выбрано',
                1: 'Наличные',
                2: 'Карта (терминал)',
                3: 'Онлайн-оплата',
                4: 'СБП'
            };
            return types[this.paymentType] || 'Неизвестно';
        },
        hasProductDetails() {
            return Array.isArray(this.orderData.product_details) && this.orderData.product_details.length > 0;
        },
        productDetails() {
            return this.orderData.product_details || [];
        },
        hasLegacyParts() {
            return Array.isArray(this.orderData.parts) && this.orderData.parts.length > 0;
        },
        legacyParts() {
            return this.orderData.parts || [];
        },
        formattedJson() {
            return JSON.stringify(this.card, null, 2);
        }
    },
    methods: {

        getVal(val, fallback = 'Не заполнено') {
            if (val === null || val === undefined || val === '') {
                return fallback;
            }
            return val;
        },
        formatPrice(price) {
            if (price === null || price === undefined || price === '') return '0 ₽';
            return new Intl.NumberFormat('ru-RU', {
                style: 'currency',
                currency: 'RUB',
                minimumFractionDigits: 0
            }).format(price);
        },
        copyJson() {
            navigator.clipboard.writeText(this.formattedJson).then(() => {
                const btn = document.querySelector('.btn-copy');
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<i class="fa-solid fa-check"></i>';
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                }, 1500);
            });
        }
    }
}
</script>

<style scoped>
.card-order-wrapper {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    font-size: 13px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

/* === HEADER === */
.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 16px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-bottom: 1px solid #dee2e6;
}

.order-id-badge {
    font-weight: 700;
    color: #495057;
    font-size: 13px;
}

.order-total {
    font-weight: 800;
    color: #10b981;
    font-size: 15px;
}

/* === BODY === */
.order-body {
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.info-block {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info-label {
    font-size: 11px;
    font-weight: 700;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.info-value {
    font-size: 14px;
    font-weight: 600;
    color: #212529;
}

/* === СПИСОК ТОВАРОВ === */
.products-block {
    margin-top: 4px;
    padding-top: 14px;
    border-top: 1px dashed #dee2e6;
}

.products-list {
    list-style: none;
    padding: 0;
    margin: 8px 0 0 0;
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.product-item {
    padding: 8px 10px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 3px solid #667eea;
}

.product-item.legacy {
    border-left-color: #adb5bd;
}

.product-from {
    display: block;
    margin-bottom: 4px;
    font-weight: 600;
}

.product-parts {
    list-style: none;
    padding: 0;
    margin: 0;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.product-part {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
}

.part-name {
    flex: 1;
    font-weight: 500;
    color: #212529;
    padding-right: 8px;
}

.part-count {
    color: #6c757d;
    font-weight: 600;
    background: #e9ecef;
    padding: 1px 6px;
    border-radius: 4px;
    margin-right: 8px;
}

.part-price {
    font-weight: 700;
    color: #0d6efd;
    min-width: 60px;
    text-align: right;
}

/* === FOOTER & DEBUG === */
.order-footer {
    padding: 12px 16px;
    background: #f8f9fa;
    border-top: 1px solid #e9ecef;
}

.btn-debug-json {
    width: 100%;
    padding: 8px;
    border: 1px solid #dee2e6;
    background: #ffffff;
    color: #6c757d;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-debug-json:hover {
    background: #e9ecef;
    color: #495057;
}

.btn-debug-json.active {
    background: #212529;
    color: #10b981;
    border-color: #212529;
}

.json-debug-wrapper {
    margin-top: 10px;
    border: 1px solid #212529;
    border-radius: 8px;
    overflow: hidden;
    background: #1e1e1e;
}

.json-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 6px 10px;
    background: #2d2d2d;
    color: #adb5bd;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.btn-copy {
    background: transparent;
    border: none;
    color: #adb5bd;
    cursor: pointer;
    padding: 2px 6px;
    border-radius: 4px;
    transition: all 0.2s;
}

.btn-copy:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #ffffff;
}

.json-content {
    margin: 0;
    padding: 12px;
    max-height: 300px;
    overflow: auto;
    color: #d4d4d4;
    font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
    font-size: 11px;
    line-height: 1.4;
}

/* Скроллбар для JSON */
.json-content::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.json-content::-webkit-scrollbar-track {
    background: #1e1e1e;
}
.json-content::-webkit-scrollbar-thumb {
    background: #495057;
    border-radius: 3px;
}

/* === АНИМАЦИЯ === */
.slide-fade-enter-active,
.slide-fade-leave-active {
    transition: all 0.3s ease;
    max-height: 350px;
    opacity: 1;
}

.slide-fade-enter-from,
.slide-fade-leave-to {
    max-height: 0;
    opacity: 0;
    margin-top: 0;
    overflow: hidden;
}
</style>

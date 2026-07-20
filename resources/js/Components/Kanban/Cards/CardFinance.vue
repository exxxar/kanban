<template>
    <div class="card-finance-wrapper">
        <!-- HEADER: Сумма и тип операции -->
        <div class="finance-header" :class="transactionClass">
            <div class="finance-icon-wrapper">
                <i :class="transactionIcon"></i>
            </div>
            <div class="finance-amount-block">
                <div class="finance-label">Сумма операции</div>
                <div class="finance-amount">
                    {{ isNegative ? '-' : '' }}{{ formattedAmount }} {{ currency }}
                </div>
            </div>
            <div class="finance-badge" :class="transactionClass">
                {{ operationLabel }}
            </div>
        </div>

        <!-- BODY: Детали -->
        <div class="finance-body">
            <!-- Баланс после операции -->
            <div class="info-block balance-block">
                <div class="info-label">
                    <i class="fa-solid fa-wallet me-1"></i> Баланс после операции
                </div>
                <div class="info-value highlight">
                    {{ formattedBalance }} {{ currency }}
                </div>
            </div>

            <!-- Комментарий -->
            <div v-if="comment" class="info-block">
                <div class="info-label">
                    <i class="fa-solid fa-comment-dots me-1"></i> Комментарий
                </div>
                <div class="info-value comment-text">
                    {{ comment }}
                </div>
            </div>

            <!-- Дата и время (если есть) -->
            <div v-if="formattedDate" class="info-block date-block">
                <i class="fa-regular fa-clock me-1"></i>
                {{ formattedDate }}
            </div>
        </div>

        <!-- FOOTER: Действия и отладка -->
        <div class="finance-footer">
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
        data() {
            return this.card?.data || {};
        },
        amount() {
            return Math.abs(parseFloat(this.data.amount) || 0);
        },
        isNegative() {
            // Определяем, является ли операция расходом (минус)
            const op = (this.data.operation || '').toLowerCase();
            const isExpenseKeyword = ['расход', 'списание', 'оплата', 'возврат', 'expense', 'withdrawal'].some(k => op.includes(k));
            return isExpenseKeyword || (parseFloat(this.data.amount) < 0);
        },
        currency() {
            return this.data.currency || '₽';
        },
        operationLabel() {
            return this.data.operation || 'Неизвестная операция';
        },
        balanceAfter() {
            return parseFloat(this.data.balanceAfter) || 0;
        },
        comment() {
            return this.data.comment || this.data.description || '';
        },
        formattedDate() {
            const dateStr = this.card?.created_at || this.card?.updated_at;
            if (!dateStr) return '';
            return new Date(dateStr).toLocaleString('ru-RU', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            });
        },
        transactionClass() {
            return this.isNegative ? 'expense' : 'income';
        },
        transactionIcon() {
            return this.isNegative ? 'fa-solid fa-arrow-trend-down' : 'fa-solid fa-arrow-trend-up';
        },
        formattedAmount() {
            return new Intl.NumberFormat('ru-RU', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(this.amount);
        },
        formattedBalance() {
            return new Intl.NumberFormat('ru-RU', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }).format(this.balanceAfter);
        },
        formattedJson() {
            return JSON.stringify(this.card, null, 2);
        }
    },
    methods: {
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
.card-finance-wrapper {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    font-size: 13px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
}

/* === HEADER === */
.finance-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    border-bottom: 1px solid #e9ecef;
}

.finance-header.income {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border-bottom-color: #bbf7d0;
}

.finance-header.expense {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    border-bottom-color: #fecaca;
}

.finance-icon-wrapper {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
}

.finance-header.income .finance-icon-wrapper {
    background: #ffffff;
    color: #10b981;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
}

.finance-header.expense .finance-icon-wrapper {
    background: #ffffff;
    color: #ef4444;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.2);
}

.finance-amount-block {
    flex: 1;
    min-width: 0;
}

.finance-label {
    font-size: 11px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 2px;
}

.finance-amount {
    font-size: 22px;
    font-weight: 800;
    color: #212529;
    line-height: 1;
}

.finance-header.income .finance-amount {
    color: #059669;
}

.finance-header.expense .finance-amount {
    color: #dc2626;
}

.finance-badge {
    padding: 4px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    white-space: nowrap;
}

.finance-header.income .finance-badge {
    background: #10b981;
    color: white;
}

.finance-header.expense .finance-badge {
    background: #ef4444;
    color: white;
}

/* === BODY === */
.finance-body {
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
    display: flex;
    align-items: center;
}

.info-value {
    font-size: 14px;
    font-weight: 600;
    color: #212529;
}

/* Блок баланса */
.balance-block {
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    padding: 10px 12px;
}

.info-value.highlight {
    font-size: 16px;
    color: #0d6efd;
    font-weight: 800;
}

/* Комментарий */
.comment-text {
    font-size: 13px;
    font-weight: 400;
    color: #495057;
    font-style: italic;
    line-height: 1.4;
    background: #fffbeb;
    border-left: 3px solid #f59e0b;
    padding: 8px 12px;
    border-radius: 0 6px 6px 0;
}

/* Дата */
.date-block {
    font-size: 11px;
    color: #adb5bd;
    font-weight: 500;
    display: flex;
    align-items: center;
    margin-top: 4px;
}

/* === FOOTER & DEBUG === */
.finance-footer {
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

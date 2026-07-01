<template>
    <div class="client-card-compact">
        <!-- Основная информация -->
        <div class="client-main-row">
            <div class="client-avatar">
                {{ getInitials(task.client.company_name || task.client.contact_person) }}
            </div>
            <div class="client-info">
                <div class="client-company">
                    {{ task.client.company_name || 'Без названия' }}
                </div>
                <div class="client-contact">
                    <span v-if="task.client.contact_person" class="contact-name">
                        {{ task.client.contact_person }}
                    </span>
                    <span v-if="task.client.phone" class="contact-phone">
                        <i class="fa-solid fa-phone"></i>
                        {{ task.client.phone }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Индикаторы дополнительных полей -->
        <div v-if="hasIndicators" class="client-indicators">
            <!-- Источник -->
            <span
                v-if="task.client.source"
                class="indicator"
                :title="`Источник: ${task.client.source}`"
            >
                <i class="fa-solid fa-bullseye"></i>
            </span>

            <!-- Стоимость -->
            <span
                v-if="task.client.cost"
                class="indicator indicator-cost"
                :title="`Стоимость: ${formatCost(task.client.cost)}`"
            >
                <i class="fa-solid fa-ruble-sign"></i>
            </span>

            <!-- Адрес -->
            <span
                v-if="task.client.address"
                class="indicator"
                :title="`Адрес: ${task.client.address}`"
            >
                <i class="fa-solid fa-location-dot"></i>
            </span>

            <!-- Партнёр -->
            <span
                v-if="task.client.partner"
                class="indicator"
                :title="`Партнёр: ${task.client.partner}`"
            >
                <i class="fa-solid fa-user-group"></i>
            </span>

            <!-- Вид услуги -->
            <span
                v-if="task.client.placement_type"
                class="indicator"
                :title="`Услуга: ${task.client.placement_type}`"
            >
                <i class="fa-solid fa-box"></i>
            </span>

            <!-- Ссылки -->
            <span
                v-if="task.client.links && task.client.links.length > 0"
                class="indicator"
                :title="`Ссылок: ${task.client.links.length}`"
            >
                <i class="fa-solid fa-link"></i>
            </span>

            <!-- Теги -->
            <span
                v-if="task.tags && task.tags.length > 0"
                class="indicator indicator-tags"
                :title="`Теги: ${task.tags.map(t => '#' + t.name).join(', ')}`"
            >
                <i class="fa-solid fa-tags"></i>
                <span class="indicator-count">{{ task.tags.length }}</span>
            </span>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        task: Object
    },

    computed: {
        hasIndicators() {
            const c = this.task.client || {}
            return c.source || c.cost || c.address || c.partner ||
                c.placement_type || (c.links && c.links.length > 0) ||
                (this.task.tags && this.task.tags.length > 0)
        }
    },

    methods: {
        getInitials(name) {
            if (!name) return '?'
            return name.split(' ')
                .map(word => word[0])
                .join('')
                .toUpperCase()
                .slice(0, 2)
        },

        formatCost(cost) {
            return new Intl.NumberFormat('ru-RU', {
                style: 'currency',
                currency: 'RUB',
                minimumFractionDigits: 0,
                maximumFractionDigits: 0
            }).format(cost)
        }
    }
}
</script>

<style scoped>
.client-card-compact {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

/* === ОСНОВНАЯ СТРОКА === */
.client-main-row {
    display: flex;
    align-items: center;
    gap: 10px;
}

.client-avatar {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 12px;
    flex-shrink: 0;
}

.client-info {
    flex: 1;
    min-width: 0;
}

.client-company {
    font-size: 13px;
    font-weight: 600;
    color: #212529;
    line-height: 1.2;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.client-contact {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 2px;
    font-size: 11px;
    color: #6c757d;
    flex-wrap: wrap;
}

.contact-name {
    font-weight: 500;
    color: #495057;
}

.contact-phone {
    display: flex;
    align-items: center;
    gap: 3px;
    color: #6c757d;
}

.contact-phone i {
    font-size: 9px;
    opacity: 0.7;
}

/* === ИНДИКАТОРЫ === */
.client-indicators {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
    padding-top: 6px;
    border-top: 1px dashed #e9ecef;
}

.indicator {
    position: relative;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 5px;
    background: #f1f3f5;
    color: #6c757d;
    font-size: 10px;
    cursor: help;
    transition: all 0.15s ease;
}

.indicator:hover {
    background: #e7f1ff;
    color: #0d6efd;
    transform: translateY(-1px);
}

.indicator-cost {
    background: #d1e7dd;
    color: #0f5132;
}

.indicator-cost:hover {
    background: #10b981;
    color: white;
}

.indicator-tags {
    background: #f3f0ff;
    color: #7c3aed;
}

.indicator-tags:hover {
    background: #7c3aed;
    color: white;
}

.indicator-count {
    position: absolute;
    top: -4px;
    right: -4px;
    min-width: 14px;
    height: 14px;
    padding: 0 3px;
    background: #7c3aed;
    color: white;
    border-radius: 7px;
    font-size: 8px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1.5px solid white;
}

/* === TOOLTIP === */
.indicator[title] {
    position: relative;
}

.indicator[title]:hover::before {
    content: attr(title);
    position: absolute;
    top: calc(100% + 6px);
    left: 50%;
    transform: translateX(-50%);
    background: #212529;
    color: white;
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 500;
    white-space: nowrap;
    max-width: 250px;
    overflow: hidden;
    text-overflow: ellipsis;
    pointer-events: none;
    z-index: 1000;
    animation: tooltipFadeIn 0.15s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
}

.indicator[title]:hover::after {
    content: '';
    position: absolute;
    top: calc(100% + 2px);
    left: 50%;
    transform: translateX(-50%);
    border: 4px solid transparent;
    border-bottom-color: #212529;
    pointer-events: none;
    z-index: 1000;
    animation: tooltipFadeIn 0.15s ease;
}

@keyframes tooltipFadeIn {
    from {
        opacity: 0;
        transform: translateX(-50%) translateY(-4px);
    }
    to {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
    }
}
</style>

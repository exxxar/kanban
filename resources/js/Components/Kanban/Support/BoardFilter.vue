<template>
    <div class="board-filter">
        <!-- Кнопка открытия + быстрые иконки -->
        <!-- === ДЕСКТОПНАЯ ВЕРСИЯ (кнопка + быстрые иконки) === -->
        <template v-if="!isMobile">
            <div class="filter-controls">
                <button
                    class="filter-toggle-btn"
                    :class="{ active: store.hasActiveFilters }"
                    @click="isOpen = true"
                >
                    <i class="fa-solid fa-filter"></i>
                    <span>Фильтры</span>
                    <span v-if="store.activeFiltersCount > 0" class="filter-badge">
                        {{ store.activeFiltersCount }}
                    </span>
                </button>

                <TransitionGroup name="filter-icons" tag="div" class="active-filter-icons">
                    <!-- ... все быстрые иконки как было ... -->
                </TransitionGroup>
            </div>
        </template>

        <!-- === МОБИЛЬНАЯ FAB КНОПКА === -->
        <template v-else>
            <button
                class="filter-fab"
                :class="{ active: store.hasActiveFilters }"
                @click="isOpen = true"
                aria-label="Открыть фильтры"
            >
                <i class="fa-solid fa-filter"></i>

                <!-- Бейдж с количеством -->
                <span v-if="store.activeFiltersCount > 0" class="fab-badge">
                    {{ store.activeFiltersCount > 9 ? '9+' : store.activeFiltersCount }}
                </span>

                <!-- Пульсация при активных фильтрах -->
                <span v-if="store.hasActiveFilters" class="fab-pulse"></span>
            </button>

            <!-- Подсказка с активными фильтрами -->
            <Transition name="fade">
                <div
                    v-if="store.hasActiveFilters && showFabHint"
                    class="fab-hint"
                    @click="isOpen = true"
                >
                    <i class="fa-solid fa-filter"></i>
                    <span>Активных фильтров: {{ store.activeFiltersCount }}</span>
                    <button class="hint-close" @click.stop="showFabHint = false">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </Transition>
        </template>

        <!-- МОДАЛКА -->
        <Transition name="modal-fade">
            <div v-if="isOpen" class="filter-modal-overlay" @click.self="isOpen = false">
                <div class="filter-modal">
                    <!-- HEADER -->
                    <div class="modal-header-custom">
                        <div class="header-content">
                            <div class="header-icon">
                                <i class="fa-solid fa-filter"></i>
                            </div>
                            <div class="header-text">
                                <h3 class="modal-title-text">
                                    Фильтры и поиск
                                    <span v-if="store.activeFiltersCount > 0" class="count-badge">
                                        {{ store.activeFiltersCount }}
                                    </span>
                                </h3>
                                <p class="modal-subtitle">
                                    Найдено: <strong>{{ totalFilteredCount }}</strong> из {{ totalTasksCount }}
                                </p>
                            </div>
                        </div>
                        <button class="close-btn" @click="isOpen = false" title="Закрыть">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- BODY -->
                    <div class="modal-body-custom">
                        <!-- ПОИСК -->
                        <div class="filter-section">
                            <div class="section-title">
                                <i class="fa-solid fa-magnifying-glass"></i>
                                Поиск
                            </div>
                            <div class="search-wrapper">
                                <i class="fa-solid fa-magnifying-glass search-icon"></i>
                                <input
                                    v-model="store.filters.search"
                                    type="text"
                                    class="search-input"
                                    placeholder="Название, описание, ID, телефон..."
                                >
                                <button
                                    v-if="store.filters.search"
                                    class="search-clear"
                                    @click="store.filters.search = ''"
                                >
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>
                        </div>

                        <div class="filters-grid">
                            <!-- ПРИОРИТЕТ -->
                            <div class="filter-section">
                                <div class="section-title">
                                    <i class="fa-solid fa-flag"></i>
                                    Приоритет
                                </div>
                                <div class="priority-filters">
                                    <button
                                        v-for="p in ['high', 'medium', 'low']"
                                        :key="p"
                                        class="priority-btn"
                                        :class="[`priority-${p}`, { active: store.filters.priority.includes(p) }]"
                                        @click="togglePriority(p)"
                                    >
                                        <i :class="getPriorityIcon(p)"></i>
                                        <span>{{ getPriorityLabel(p) }}</span>
                                    </button>
                                </div>
                            </div>

                            <!-- ДЕДЛАЙН -->
                            <div class="filter-section">
                                <div class="section-title">
                                    <i class="fa-solid fa-calendar-days"></i>
                                    Срок выполнения
                                </div>
                                <div class="due-filters">
                                    <button
                                        v-for="d in ['today', 'week', 'overdue', 'has']"
                                        :key="d"
                                        class="due-btn"
                                        :class="{ active: store.filters.dueDate === d }"
                                        @click="toggleDueDate(d)"
                                    >
                                        <i :class="getDueIcon(d)"></i>
                                        <span>{{ getDueDateLabel(d) }}</span>
                                    </button>
                                </div>
                            </div>

                            <!-- ТЕГИ -->
                            <div v-if="store.tags.length > 0" class="filter-section full-width">
                                <div class="section-title">
                                    <i class="fa-solid fa-tags"></i>
                                    Теги
                                </div>
                                <div class="tags-grid">
                                    <button
                                        v-for="tag in store.tags"
                                        :key="tag.id"
                                        class="tag-btn"
                                        :class="{ active: store.filters.tags.includes(tag.id) }"
                                        :style="{
                                            borderColor: tag.color,
                                            background: store.filters.tags.includes(tag.id) ? tag.color + '20' : 'transparent',
                                            color: store.filters.tags.includes(tag.id) ? tag.color : '#495057'
                                        }"
                                        @click="toggleTag(tag.id)"
                                    >
                                        <span class="tag-dot" :style="{ background: tag.color }"></span>
                                        {{ tag.name }}
                                    </button>
                                </div>
                            </div>

                            <!-- КАТЕГОРИИ (включая кастомные) -->
                            <div class="filter-section full-width">
                                <div class="section-title">
                                    <i class="fa-solid fa-layer-group"></i>
                                    Категории
                                </div>
                                <div class="labels-grid">
                                    <button
                                        v-for="label in allLabels"
                                        :key="label.key"
                                        class="label-btn"
                                        :class="{ active: store.filters.labels.includes(label.key) }"
                                        @click="toggleLabel(label.key)"
                                    >
                                        <i :class="label.icon"></i>
                                        <span>{{ label.name }}</span>
                                    </button>
                                </div>
                            </div>

                            <!-- БЫСТРЫЕ ФИЛЬТРЫ -->
                            <div class="filter-section">
                                <div class="section-title">
                                    <i class="fa-solid fa-bolt"></i>
                                    Быстрые фильтры
                                </div>
                                <div class="quick-filters">
                                    <button
                                        class="quick-btn"
                                        :class="{ active: store.filters.onlyClients }"
                                        @click="toggleOnlyClients"
                                    >
                                        <i class="fa-solid fa-user-tie"></i>
                                        <span>Только клиенты</span>
                                    </button>
                                    <button
                                        class="quick-btn"
                                        :class="{ active: store.filters.hasAttachments === true }"
                                        @click="toggleAttachments(true)"
                                    >
                                        <i class="fa-solid fa-paperclip"></i>
                                        <span>С вложениями</span>
                                    </button>
                                    <button
                                        class="quick-btn"
                                        :class="{ active: store.filters.hasSubtasks === true }"
                                        @click="toggleSubtasks(true)"
                                    >
                                        <i class="fa-solid fa-list-check"></i>
                                        <span>С подзадачами</span>
                                    </button>
                                </div>
                            </div>

                            <!-- СТОИМОСТЬ -->
                            <div class="filter-section">
                                <div class="section-title">
                                    <i class="fa-solid fa-ruble-sign"></i>
                                    Стоимость
                                </div>
                                <div class="cost-range">
                                    <div class="range-input">
                                        <label>От</label>
                                        <input
                                            v-model.number="store.filters.costRange.min"
                                            type="number"
                                            min="0"
                                            placeholder="0"
                                            class="range-field"
                                        >
                                    </div>
                                    <span class="range-separator">—</span>
                                    <div class="range-input">
                                        <label>До</label>
                                        <input
                                            v-model.number="store.filters.costRange.max"
                                            type="number"
                                            min="0"
                                            placeholder="∞"
                                            class="range-field"
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- ДАТА СОЗДАНИЯ -->
                            <div class="filter-section">
                                <div class="section-title">
                                    <i class="fa-solid fa-calendar-plus"></i>
                                    Дата создания
                                </div>
                                <div class="date-range">
                                    <div class="range-input">
                                        <label>С</label>
                                        <input
                                            v-model="store.filters.createdRange.from"
                                            type="date"
                                            class="range-field"
                                        >
                                    </div>
                                    <span class="range-separator">—</span>
                                    <div class="range-input">
                                        <label>По</label>
                                        <input
                                            v-model="store.filters.createdRange.to"
                                            type="date"
                                            class="range-field"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FOOTER -->
                    <div class="modal-footer-custom">
                        <button
                            v-if="store.hasActiveFilters"
                            class="btn-footer btn-reset"
                            @click="resetAll"
                        >
                            <i class="fa-solid fa-rotate-left me-2"></i>
                            Сбросить всё
                        </button>
                        <div class="footer-actions">
                            <button class="btn-footer btn-cancel" @click="isOpen = false">
                                <i class="fa-solid fa-xmark me-2"></i>
                                Отмена
                            </button>
                            <button class="btn-footer btn-apply" @click="isOpen = false">
                                <i class="fa-solid fa-check me-2"></i>
                                Применить
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script>
import { useKanbanStore } from '@/stores/kanban/useKanbanStore.js'

export default {
    data() {
        return {
            store: useKanbanStore(),
            isOpen: false,

            isMobile: false,
            showFabHint: true,

            standardLabels: [
                { key: 'development', name: 'Разработка', icon: 'fa-solid fa-code' },
                { key: 'bug', name: 'Баг', icon: 'fa-solid fa-bug' },
                { key: 'client', name: 'Клиент', icon: 'fa-solid fa-user-tie' },
                { key: 'urgent', name: 'Срочно', icon: 'fa-solid fa-fire' },
                { key: 'design', name: 'Дизайн', icon: 'fa-solid fa-palette' },
                { key: 'finance', name: 'Финансы', icon: 'fa-solid fa-money-bill' },
                { key: 'marketing', name: 'Маркетинг', icon: 'fa-solid fa-bullhorn' },
                { key: 'sales', name: 'Продажи', icon: 'fa-solid fa-chart-line' },
                { key: 'support', name: 'Поддержка', icon: 'fa-solid fa-headset' },
                { key: 'testing', name: 'Тестирование', icon: 'fa-solid fa-vial' },
                { key: 'documentation', name: 'Документация', icon: 'fa-solid fa-book' }
            ]
        }
    },

    computed: {
        allLabels() {
            // Стандартные + кастомные из конфига доски
            const customLabels = (this.store.board?.config?.custom_categories || []).map(cat => ({
                key: cat.key,
                name: cat.name,
                icon: cat.icon || 'fa-solid fa-tag'
            }))

            return [...this.standardLabels, ...customLabels]
        },

        totalFilteredCount() {
            return this.store.filteredColumns.reduce(
                (sum, col) => sum + (col.filteredCount || col.tasks.length), 0
            )
        },
        totalTasksCount() {
            return this.store.columns.reduce(
                (sum, col) => sum + col.tasks.length, 0
            )
        }
    },
    mounted() {
        this.checkMobile()
        window.addEventListener('resize', this.checkMobile)
    },

    beforeUnmount() {
        window.removeEventListener('resize', this.checkMobile)
    },
    methods: {
        checkMobile() {
            this.isMobile = window.innerWidth < 768
            if (!this.isMobile) {
                this.showFabHint = true
            }
        },
        // === TOGGLES ===
        toggleOnlyClients() {
            this.store.filters.onlyClients = !this.store.filters.onlyClients
        },

        toggleAttachments(value) {
            this.store.filters.hasAttachments =
                this.store.filters.hasAttachments === value ? null : value
        },

        toggleSubtasks(value) {
            this.store.filters.hasSubtasks =
                this.store.filters.hasSubtasks === value ? null : value
        },

        togglePriority(priority) {
            const idx = this.store.filters.priority.indexOf(priority)
            if (idx === -1) {
                this.store.filters.priority.push(priority)
            } else {
                this.store.filters.priority.splice(idx, 1)
            }
        },

        toggleDueDate(due) {
            this.store.filters.dueDate =
                this.store.filters.dueDate === due ? null : due
        },

        toggleTag(tagId) {
            const idx = this.store.filters.tags.indexOf(tagId)
            if (idx === -1) {
                this.store.filters.tags.push(tagId)
            } else {
                this.store.filters.tags.splice(idx, 1)
            }
        },

        toggleLabel(label) {
            const idx = this.store.filters.labels.indexOf(label)
            if (idx === -1) {
                this.store.filters.labels.push(label)
            } else {
                this.store.filters.labels.splice(idx, 1)
            }
        },

        // === CLEAR ===
        clearFilter(key) {
            if (key === 'search') {
                this.store.filters.search = ''
            } else if (key === 'onlyClients') {
                this.store.filters.onlyClients = false
            } else if (key === 'dueDate') {
                this.store.filters.dueDate = null
            } else if (key === 'hasAttachments') {
                this.store.filters.hasAttachments = null
            } else if (key === 'hasSubtasks') {
                this.store.filters.hasSubtasks = null
            } else if (key === 'createdRange') {
                this.store.filters.createdRange = { from: null, to: null }
            } else if (key === 'costRange') {
                this.store.filters.costRange = { min: null, max: null }
            }
        },

        removeType(type) {
            this.store.filters.types = this.store.filters.types.filter(t => t !== type)
        },
        getTypeIcon(type) {
            const icons = {
                1: 'fa-solid fa-list-check',  // Задача
                2: 'fa-solid fa-user-tie',    // Клиент
                3: 'fa-solid fa-align-left',  // Текст
                4: 'fa-solid fa-coins',       // Финансы
                5: 'fa-solid fa-code',        // Разработка
                6: 'fa-solid fa-cart-shopping' // Заказ
            }
            return icons[type] || 'fa-solid fa-square'
        },

        getTypeName(type) {
            const names = {
                1: 'Задача',
                2: 'Клиент',
                3: 'Текст',
                4: 'Финансы',
                5: 'Разработка',
                6: 'Заказ'
            }
            return names[type] || 'Тип ' + type
        },

        getCreatedRangeLabel() {
            const { from, to } = this.store.filters.createdRange
            if (from && to) {
                return `Создано: ${this.formatDateShort(from)} — ${this.formatDateShort(to)}`
            }
            if (from) return `Создано после: ${this.formatDateShort(from)}`
            if (to) return `Создано до: ${this.formatDateShort(to)}`
            return ''
        },

        getCostRangeLabel() {
            const { min, max } = this.store.filters.costRange
            if (min !== null && max !== null) {
                return `Стоимость: ${this.formatMoney(min)} — ${this.formatMoney(max)}`
            }
            if (min !== null) return `Стоимость от: ${this.formatMoney(min)}`
            if (max !== null) return `Стоимость до: ${this.formatMoney(max)}`
            return ''
        },

        formatDateShort(dateStr) {
            if (!dateStr) return ''
            const date = new Date(dateStr)
            return date.toLocaleDateString('ru-RU', {
                day: '2-digit',
                month: '2-digit',
                year: '2-digit'
            })
        },

        formatMoney(amount) {
            if (amount === null || amount === undefined) return '0'
            return new Intl.NumberFormat('ru-RU').format(amount) + ' ₽'
        },

        getCategoryIcon(label) {
            const icons = {
                development: 'fa-solid fa-code',
                bug: 'fa-solid fa-bug',
                client: 'fa-solid fa-user-tie',
                urgent: 'fa-solid fa-fire',
                design: 'fa-solid fa-palette',
                finance: 'fa-solid fa-money-bill',
                marketing: 'fa-solid fa-bullhorn',
                sales: 'fa-solid fa-chart-line',
                support: 'fa-solid fa-headset',
                testing: 'fa-solid fa-vial',
                documentation: 'fa-solid fa-book'
            }
            // Сначала проверяем стандартные
            if (icons[label]) return icons[label]

            // Потом ищем в кастомных категориях
            const custom = (this.store.board?.config?.custom_categories || [])
                .find(c => c.key === label)
            return custom?.icon || 'fa-solid fa-tag'
        },

        getCategoryLabel(label) {
            const labels = {
                development: 'Разработка',
                bug: 'Баг',
                client: 'Клиент',
                urgent: 'Срочно',
                design: 'Дизайн',
                finance: 'Финансы',
                marketing: 'Маркетинг',
                sales: 'Продажи',
                support: 'Поддержка',
                testing: 'Тестирование',
                documentation: 'Документация'
            }
            if (labels[label]) return labels[label]

            const custom = (this.store.board?.config?.custom_categories || [])
                .find(c => c.key === label)
            return custom?.name || label
        },

        removeTag(tagId) {
            this.store.filters.tags = this.store.filters.tags.filter(id => id !== tagId)
        },

        removeTag(tagId) {
            this.store.filters.tags = this.store.filters.tags.filter(id => id !== tagId)
        },

        removeLabel(label) {
            this.store.filters.labels = this.store.filters.labels.filter(l => l !== label)
        },

        removePriority(priority) {
            this.store.filters.priority = this.store.filters.priority.filter(p => p !== priority)
        },

        resetAll() {
            this.store.resetFilters()
        },



        // === HELPERS ===
        getTagColor(tagId) {
            const tag = this.store.tags.find(t => t.id === tagId)
            return tag?.color || '#6c757d'
        },

        getTagName(tagId) {
            const tag = this.store.tags.find(t => t.id === tagId)
            return tag?.name || 'Тег'
        },

        getPriorityIcon(priority) {
            const icons = {
                high: 'fa-solid fa-arrow-up',
                medium: 'fa-solid fa-minus',
                low: 'fa-solid fa-arrow-down'
            }
            return icons[priority] || 'fa-solid fa-minus'
        },

        getPriorityLabel(priority) {
            const labels = {
                high: 'Высокий',
                medium: 'Средний',
                low: 'Низкий'
            }
            return labels[priority] || priority
        },

        getDueIcon(due) {
            const icons = {
                today: 'fa-solid fa-calendar-day',
                week: 'fa-solid fa-calendar-week',
                overdue: 'fa-solid fa-triangle-exclamation',
                has: 'fa-solid fa-calendar-check'
            }
            return icons[due] || 'fa-solid fa-calendar'
        },

        getDueDateLabel(due) {
            const labels = {
                today: 'Сегодня',
                week: 'На неделе',
                overdue: 'Просрочено',
                has: 'Есть дедлайн'
            }
            return labels[due] || due
        }
    }
}
</script>

<style scoped>
/* === CONTROLS === */
.filter-controls {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.filter-toggle-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border: 1px solid #e9ecef;
    background: #ffffff;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 13px;
    font-weight: 600;
    color: #495057;
}

.filter-toggle-btn:hover {
    background: #f8f9fa;
    border-color: #dee2e6;
}

.filter-toggle-btn.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: transparent;
    color: white;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.filter-badge {
    background: rgba(255, 255, 255, 0.25);
    padding: 2px 8px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 700;
    min-width: 20px;
    text-align: center;
}

.filter-toggle-btn:not(.active) .filter-badge {
    background: #667eea;
    color: white;
}

/* === БЫСТРЫЕ ИКОНКИ === */
.active-filter-icons {
    display: flex;
    align-items: center;
    gap: 6px;
}

.quick-filter-icon {
    position: relative;
    width: 32px;
    height: 32px;
    border: 1px solid #e9ecef;
    background: #ffffff;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

.quick-filter-icon:hover {
    background: #f8f9fa;
    border-color: #dee2e6;
    transform: translateY(-1px);
}

.quick-filter-icon i {
    color: #6c757d;
}

.quick-filter-icon .icon-close {
    position: absolute;
    top: -6px;
    right: -6px;
    width: 16px;
    height: 16px;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    font-size: 11px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1;
}

/* Цвета для разных типов */
.quick-filter-icon.icon-clients i {
    color: #7c3aed;
}

.quick-filter-icon.icon-priority-high i {
    color: #dc2626;
}

.quick-filter-icon.icon-priority-medium i {
    color: #d97706;
}

.quick-filter-icon.icon-priority-low i {
    color: #6c757d;
}

.quick-filter-icon.icon-due i {
    color: #0d6efd;
}

.quick-filter-icon.icon-attachments i {
    color: #f59e0b;
}

.quick-filter-icon.icon-subtasks i {
    color: #10b981;
}

.quick-filter-icon.icon-tag i {
    color: #8b5cf6;
}

.quick-filter-icon.icon-tag-more {
    background: #f3f0ff;
    color: #7c3aed;
    border-color: #ddd6fe;
    font-size: 11px;
    font-weight: 700;
}

/* === МОДАЛКА === */
.filter-modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    padding: 5px;
}

.filter-modal {
    background: #ffffff;
    border-radius: 20px;
    width: 900px;
    max-width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
    animation: modalSlideUp 0.3s ease;
    overflow: hidden;
}

@keyframes modalSlideUp {
    from { opacity: 0; transform: translateY(30px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

/* HEADER */
.modal-header-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 24px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: white;
    flex-shrink: 0;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 16px;
    min-width: 0;
    flex: 1;
}

.header-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    backdrop-filter: blur(10px);
    flex-shrink: 0;
}

.header-text { flex: 1; min-width: 0; }

.modal-title-text {
    font-size: 20px;
    font-weight: 700;
    margin: 0 0 2px 0;
    color: white;
    display: flex;
    align-items: center;
    gap: 10px;
}

.count-badge {
    background: rgba(255, 255, 255, 0.25);
    padding: 4px 12px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
}

.modal-subtitle {
    font-size: 12px;
    margin: 0;
    opacity: 0.9;
}

.modal-subtitle strong {
    color: white;
}

.close-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: white;
    font-size: 18px;
    transition: all 0.2s;
    flex-shrink: 0;
    margin-left: 12px;
}

.close-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: rotate(90deg);
}

/* BODY */
.modal-body-custom {
    padding: 28px;
    overflow-y: auto;
    flex: 1;
}

/* GRID */
.filters-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 24px;
}

.filter-section.full-width {
    grid-column: 1 / -1;
}

/* СЕКЦИИ */
.filter-section {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 700;
    color: #495057;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.section-title i {
    font-size: 12px;
    color: #667eea;
}

/* ПОИСК */
.search-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.search-icon {
    position: absolute;
    left: 14px;
    color: #adb5bd;
    font-size: 14px;
    pointer-events: none;
}

.search-input {
    width: 100%;
    padding: 12px 40px 12px 42px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 14px;
    outline: none;
    transition: all 0.2s;
    background: #ffffff;
}

.search-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.search-clear {
    position: absolute;
    right: 10px;
    width: 28px;
    height: 28px;
    border: none;
    background: #f1f3f5;
    color: #6c757d;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
}

.search-clear:hover {
    background: #e9ecef;
    color: #dc3545;
}

/* ПРИОРИТЕТ */
.priority-filters {
    display: flex;
    gap: 8px;
}

.priority-btn {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px;
    border: 2px solid;
    background: #ffffff;
    border-radius: 10px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.2s;
}

.priority-btn.priority-high {
    border-color: #fecaca;
    color: #dc2626;
}

.priority-btn.priority-high.active,
.priority-btn.priority-high:hover {
    background: #fee2e2;
    border-color: #dc2626;
}

.priority-btn.priority-medium {
    border-color: #fde68a;
    color: #d97706;
}

.priority-btn.priority-medium.active,
.priority-btn.priority-medium:hover {
    background: #fef3c7;
    border-color: #d97706;
}

.priority-btn.priority-low {
    border-color: #dee2e6;
    color: #6c757d;
}

.priority-btn.priority-low.active,
.priority-btn.priority-low:hover {
    background: #e9ecef;
    border-color: #6c757d;
}

/* ДЕДЛАЙН */
.due-filters {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 8px;
}

.due-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 10px 8px;
    border: 2px solid #e9ecef;
    background: #ffffff;
    color: #495057;
    border-radius: 10px;
    cursor: pointer;
    font-size: 11px;
    font-weight: 600;
    transition: all 0.2s;
}

.due-btn i {
    font-size: 14px;
}

.due-btn:hover {
    border-color: #667eea;
    color: #667eea;
}

.due-btn.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: transparent;
    color: white;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

/* ТЕГИ */
.tags-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.tag-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border: 2px solid;
    background: transparent;
    border-radius: 8px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.2s;
}

.tag-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.tag-btn.active {
    font-weight: 700;
}

.tag-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

/* КАТЕГОРИИ */
.labels-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.label-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border: 2px solid #e9ecef;
    background: #ffffff;
    color: #495057;
    border-radius: 8px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.2s;
}

.label-btn:hover {
    border-color: #667eea;
    color: #667eea;
}

.label-btn.active {
    background: #e7f1ff;
    border-color: #0d6efd;
    color: #0d6efd;
}

/* БЫСТРЫЕ ФИЛЬТРЫ */
.quick-filters {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.quick-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    border: 2px solid #e9ecef;
    background: #ffffff;
    color: #495057;
    border-radius: 8px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 600;
    transition: all 0.2s;
}

.quick-btn:hover {
    border-color: #667eea;
    color: #667eea;
}

.quick-btn.active {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-color: transparent;
    color: white;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

/* ДИАПАЗОНЫ */
.cost-range,
.date-range {
    display: flex;
    align-items: flex-end;
    gap: 10px;
}

.range-input {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.range-input label {
    font-size: 11px;
    font-weight: 600;
    color: #6c757d;
}

.range-field {
    padding: 10px 12px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 13px;
    outline: none;
    transition: all 0.2s;
    background: #ffffff;
}

.range-field:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.range-separator {
    color: #adb5bd;
    padding-bottom: 10px;
    font-weight: 600;
}

/* FOOTER */
.modal-footer-custom {
    padding: 20px 28px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
    flex-shrink: 0;
}

.btn-footer {
    padding: 10px 24px;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-reset {
    background: #ffffff;
    color: #dc3545;
    border: 1px solid #dee2e6;
}

.btn-reset:hover {
    background: #fff5f5;
    border-color: #fecaca;
}

.footer-actions {
    display: flex;
    gap: 12px;
}

.btn-cancel {
    background: #ffffff;
    color: #6c757d;
    border: 1px solid #dee2e6;
}

.btn-cancel:hover {
    background: #f8f9fa;
    color: #495057;
    border-color: #adb5bd;
}

.btn-apply {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
}

.btn-apply:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

/* === АНИМАЦИИ === */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

.filter-icons-enter-active,
.filter-icons-leave-active {
    transition: all 0.2s ease;
}

.filter-icons-enter-from,
.filter-icons-leave-to {
    opacity: 0;
    transform: scale(0.8);
}

/* === СКРОЛЛБАР === */
.modal-body-custom::-webkit-scrollbar {
    width: 8px;
}

.modal-body-custom::-webkit-scrollbar-track {
    background: #f1f3f5;
    border-radius: 4px;
}

.modal-body-custom::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 4px;
}

.modal-body-custom::-webkit-scrollbar-thumb:hover {
    background: #adb5bd;
}

/* === АДАПТИВ === */
@media (max-width: 768px) {
    .filter-modal {
        width: 100%;
        max-height: 95vh;
        border-radius: 16px;
    }

    .modal-header-custom {
        padding: 10px;
    }

    .header-icon {
        width: 48px;
        height: 48px;
        font-size: 20px;
    }

    .modal-title-text {
        font-size: 17px;
    }

    .modal-body-custom {
        padding: 10px;
    }

    .filters-grid {
        grid-template-columns: 1fr;
    }

    .due-filters {
        grid-template-columns: repeat(2, 1fr);
    }

    .cost-range,
    .date-range {
        flex-direction: column;
        align-items: stretch;
    }

    .range-separator {
        display: none;
    }

    .modal-footer-custom {
        padding: 16px 10px;
        flex-direction: column;
        gap: 12px;
    }

    .footer-actions {
        width: 100%;
        flex-direction: column-reverse;
    }

    .btn-footer {
        width: 100%;
    }

    .quick-filter-icon {
        width: 28px;
        height: 28px;
        font-size: 11px;
    }
}

/* === ЦВЕТА НОВЫХ ИКОНОК === */

/* Поиск */
.quick-filter-icon.icon-search i {
    color: #0d6efd;
}

/* Стоимость */
.quick-filter-icon.icon-cost {
    background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
    border-color: #10b981;
}

.quick-filter-icon.icon-cost i {
    color: #059669;
    font-weight: 700;
}

/* Дата создания */
.quick-filter-icon.icon-created {
    background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);
    border-color: #3b82f6;
}

.quick-filter-icon.icon-created i {
    color: #2563eb;
}

/* Категории */
.quick-filter-icon.icon-label {
    background: #f0f9ff;
    border-color: #0ea5e9;
}

.quick-filter-icon.icon-label i {
    color: #0284c7;
}

.quick-filter-icon.icon-label-more {
    background: #e0f2fe;
    color: #0284c7;
    border-color: #7dd3fc;
    font-size: 11px;
    font-weight: 700;
}

/* Типы задач */
.quick-filter-icon.icon-type-1 i { color: #0d6efd; } /* Задача */
.quick-filter-icon.icon-type-2 i { color: #7c3aed; } /* Клиент */
.quick-filter-icon.icon-type-3 i { color: #6c757d; } /* Текст */
.quick-filter-icon.icon-type-4 i { color: #f59e0b; } /* Финансы */
.quick-filter-icon.icon-type-5 i { color: #10b981; } /* Разработка */
.quick-filter-icon.icon-type-6 i { color: #ef4444; } /* Заказ */

/* Вложения (с/без) */
.quick-filter-icon.icon-attachments-yes {
    background: #fef3c7;
    border-color: #f59e0b;
}

.quick-filter-icon.icon-attachments-yes i {
    color: #d97706;
}

.quick-filter-icon.icon-attachments-no {
    background: #f1f5f9;
    border-color: #94a3b8;
    position: relative;
}

.quick-filter-icon.icon-attachments-no i {
    color: #64748b;
    opacity: 0.5;
}

/* Подзадачи (с/без) */
.quick-filter-icon.icon-subtasks-yes {
    background: #d1fae5;
    border-color: #10b981;
}

.quick-filter-icon.icon-subtasks-yes i {
    color: #059669;
}

.quick-filter-icon.icon-subtasks-no {
    background: #f1f5f9;
    border-color: #94a3b8;
    position: relative;
}

.quick-filter-icon.icon-subtasks-no i {
    color: #64748b;
    opacity: 0.5;
}

/* Перечёркивание для "без" */
.icon-slash {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%) rotate(-45deg);
    width: 70%;
    height: 2px;
    background: #dc3545;
    border-radius: 1px;
}

/* === FAB КНОПКА (мобильная) === */
.filter-fab {
    position: fixed;
    right: 20px;
    bottom: 24px;
    width: 56px;
    height: 56px;
    border-radius: 50%;
    border: none;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    font-size: 20px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow:
        0 4px 12px rgba(102, 126, 234, 0.4),
        0 8px 24px rgba(102, 126, 234, 0.3);
    z-index: 90;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    overflow: visible;
}

.filter-fab:hover {
    transform: scale(1.08);
    box-shadow:
        0 6px 16px rgba(102, 126, 234, 0.5),
        0 12px 32px rgba(102, 126, 234, 0.4);
}

.filter-fab:active {
    transform: scale(0.95);
}

.filter-fab.active {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    box-shadow:
        0 4px 12px rgba(16, 185, 129, 0.4),
        0 8px 24px rgba(16, 185, 129, 0.3);
}

/* === БЕЙДЖ С КОЛИЧЕСТВОМ === */
.fab-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    min-width: 22px;
    height: 22px;
    padding: 0 6px;
    background: #ef4444;
    color: white;
    border: 2px solid white;
    border-radius: 11px;
    font-size: 11px;
    font-weight: 700;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
    z-index: 2;
    animation: badgeBounce 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

@keyframes badgeBounce {
    0% { transform: scale(0); }
    60% { transform: scale(1.2); }
    100% { transform: scale(1); }
}

/* === ПУЛЬСАЦИЯ === */
.fab-pulse {
    position: absolute;
    inset: 0;
    border-radius: 50%;
    background: inherit;
    z-index: -1;
    animation: fabPulse 2s ease-out infinite;
}

@keyframes fabPulse {
    0% {
        transform: scale(1);
        opacity: 0.6;
    }
    100% {
        transform: scale(1.6);
        opacity: 0;
    }
}

/* === ПОДСКАЗКА === */
.fab-hint {
    position: fixed;
    right: 20px;
    bottom: 92px;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    background: #212529;
    color: white;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
    z-index: 89;
    cursor: pointer;
    max-width: calc(100vw - 40px);
    animation: hintSlideIn 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

@keyframes hintSlideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.fab-hint::after {
    content: '';
    position: absolute;
    bottom: -6px;
    right: 24px;
    width: 12px;
    height: 12px;
    background: #212529;
    transform: rotate(45deg);
}

.fab-hint i {
    color: #10b981;
}

.hint-close {
    width: 20px;
    height: 20px;
    border: none;
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border-radius: 50%;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 10px;
    margin-left: 4px;
    transition: all 0.2s;
}

.hint-close:hover {
    background: rgba(255, 255, 255, 0.25);
}

/* === СКРЫТИЕ НА ДЕСКТОПЕ === */
@media (min-width: 768px) {
    .filter-fab,
    .fab-hint {
        display: none !important;
    }
}

/* === СКРЫТИЕ НА МОБИЛКЕ === */
@media (max-width: 767px) {
    .filter-controls {
        display: none !important;
    }
}

/* === АДАПТИВ FAB ДЛЯ МАЛЕНЬКИХ ЭКРАНОВ === */
@media (max-width: 380px) {
    .filter-fab {
        width: 50px;
        height: 50px;
        right: 16px;
        bottom: 20px;
        font-size: 18px;
    }

    .fab-hint {
        right: 16px;
        bottom: 82px;
        font-size: 11px;
    }
}

/* === БЕЗОПАСНАЯ ЗОНА (для iPhone с чёлкой) === */
@supports (padding-bottom: env(safe-area-inset-bottom)) {
    .filter-fab {
        bottom: calc(24px + env(safe-area-inset-bottom));
    }

    .fab-hint {
        bottom: calc(92px + env(safe-area-inset-bottom));
    }
}
</style>

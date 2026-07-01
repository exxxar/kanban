<template>
    <div class="icon-picker" ref="pickerRef">
        <button class="icon-picker-trigger" @click.stop="toggle">
            <div class="selected-icon" :style="{ background: previewColor }">
                <i :class="selectedIcon || 'fa-solid fa-question'"></i>
            </div>
            <span class="trigger-text">{{ selectedIcon || 'Выбрать иконку' }}</span>
            <i class="fa-solid fa-chevron-down trigger-chevron" :class="{ 'rotated': isOpen }"></i>
        </button>

        <Transition name="dropdown-fade">
            <div v-if="isOpen" class="icon-picker-dropdown">
                <!-- Поиск -->
                <div class="icon-search-wrapper">
                    <i class="fa-solid fa-magnifying-glass search-icon"></i>
                    <input
                        v-model="searchQuery"
                        type="text"
                        class="icon-search-input"
                        placeholder="Поиск иконки..."
                        @click.stop
                    >
                </div>

                <!-- Категории -->
                <div class="icon-categories">
                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        class="category-btn"
                        :class="{ active: activeCategory === cat.id }"
                        @click.stop="activeCategory = cat.id"
                    >
                        {{ cat.label }}
                    </button>
                </div>

                <!-- Сетка иконок -->
                <div class="icons-grid">
                    <button
                        v-for="icon in filteredIcons"
                        :key="icon"
                        class="icon-item"
                        :class="{ selected: selectedIcon === icon }"
                        @click.stop="selectIcon(icon)"
                        :title="icon"
                    >
                        <i :class="icon"></i>
                    </button>

                    <div v-if="filteredIcons.length === 0" class="no-icons">
                        <i class="fa-regular fa-face-frown"></i>
                        <p>Иконки не найдены</p>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script>
export default {
    props: {
        modelValue: { type: String, default: '' },
        previewColor: { type: String, default: '#0d6efd' }
    },
    emits: ['update:modelValue'],

    data() {
        return {
            isOpen: false,
            searchQuery: '',
            activeCategory: 'all',

            categories: [
                { id: 'all', label: 'Все' },
                { id: 'business', label: 'Бизнес' },
                { id: 'communication', label: 'Общение' },
                { id: 'finance', label: 'Финансы' },
                { id: 'users', label: 'Люди' },
                { id: 'objects', label: 'Объекты' },
                { id: 'interface', label: 'Интерфейс' }
            ],

            icons: {
                business: [
                    'fa-solid fa-briefcase', 'fa-solid fa-building', 'fa-solid fa-chart-line',
                    'fa-solid fa-chart-pie', 'fa-solid fa-chart-bar', 'fa-solid fa-chart-simple',
                    'fa-solid fa-handshake', 'fa-solid fa-bullseye', 'fa-solid fa-trophy',
                    'fa-solid fa-award', 'fa-solid fa-medal', 'fa-solid fa-crown',
                    'fa-solid fa-flag', 'fa-solid fa-rocket', 'fa-solid fa-graduation-cap',
                    'fa-solid fa-industry', 'fa-solid fa-store', 'fa-solid fa-warehouse'
                ],
                communication: [
                    'fa-solid fa-envelope', 'fa-solid fa-phone', 'fa-solid fa-comments',
                    'fa-solid fa-comment', 'fa-solid fa-comment-dots', 'fa-solid fa-message',
                    'fa-solid fa-paper-plane', 'fa-solid fa-bell', 'fa-solid fa-megaphone',
                    'fa-solid fa-bullhorn', 'fa-solid fa-at', 'fa-solid fa-inbox',
                    'fa-solid fa-mail-bulk', 'fa-solid fa-address-book', 'fa-solid fa-address-card'
                ],
                finance: [
                    'fa-solid fa-money-bill', 'fa-solid fa-money-bill-wave', 'fa-solid fa-coins',
                    'fa-solid fa-ruble-sign', 'fa-solid fa-dollar-sign', 'fa-solid fa-euro-sign',
                    'fa-solid fa-wallet', 'fa-solid fa-credit-card', 'fa-solid fa-receipt',
                    'fa-solid fa-calculator', 'fa-solid fa-piggy-bank', 'fa-solid fa-sack-dollar',
                    'fa-solid fa-percent', 'fa-solid fa-arrow-trend-up', 'fa-solid fa-arrow-trend-down'
                ],
                users: [
                    'fa-solid fa-user', 'fa-solid fa-user-tie', 'fa-solid fa-user-group',
                    'fa-solid fa-users', 'fa-solid fa-user-plus', 'fa-solid fa-user-check',
                    'fa-solid fa-user-clock', 'fa-solid fa-user-gear', 'fa-solid fa-user-shield',
                    'fa-solid fa-user-secret', 'fa-solid fa-user-graduate', 'fa-solid fa-user-ninja',
                    'fa-solid fa-person', 'fa-solid fa-people-group', 'fa-solid fa-child'
                ],
                objects: [
                    'fa-solid fa-box', 'fa-solid fa-boxes-stacked', 'fa-solid fa-gift',
                    'fa-solid fa-tag', 'fa-solid fa-tags', 'fa-solid fa-book',
                    'fa-solid fa-newspaper', 'fa-solid fa-file', 'fa-solid fa-file-lines',
                    'fa-solid fa-folder', 'fa-solid fa-folder-open', 'fa-solid fa-clipboard',
                    'fa-solid fa-clipboard-list', 'fa-solid fa-clipboard-check', 'fa-solid fa-note-sticky'
                ],
                interface: [
                    'fa-solid fa-gear', 'fa-solid fa-gears', 'fa-solid fa-sliders',
                    'fa-solid fa-filter', 'fa-solid fa-sort', 'fa-solid fa-magnifying-glass',
                    'fa-solid fa-plus', 'fa-solid fa-minus', 'fa-solid fa-check',
                    'fa-solid fa-xmark', 'fa-solid fa-pen', 'fa-solid fa-pen-to-square',
                    'fa-solid fa-trash-can', 'fa-solid fa-download', 'fa-solid fa-upload'
                ]
            }
        }
    },

    computed: {
        selectedIcon() {
            return this.modelValue
        },

        filteredIcons() {
            let icons = []

            if (this.activeCategory === 'all') {
                icons = Object.values(this.icons).flat()
            } else {
                icons = this.icons[this.activeCategory] || []
            }

            if (this.searchQuery.trim()) {
                const query = this.searchQuery.toLowerCase()
                icons = icons.filter(icon => icon.toLowerCase().includes(query))
            }

            return icons
        }
    },

    mounted() {
        document.addEventListener('click', this.handleClickOutside)
    },

    beforeUnmount() {
        document.removeEventListener('click', this.handleClickOutside)
    },

    methods: {
        toggle() {
            this.isOpen = !this.isOpen
        },

        close() {
            this.isOpen = false
        },

        selectIcon(icon) {
            this.$emit('update:modelValue', icon)
            this.close()
        },

        handleClickOutside(event) {
            if (this.$refs.pickerRef && !this.$refs.pickerRef.contains(event.target)) {
                this.close()
            }
        }
    }
}
</script>

<style scoped>
.icon-picker {
    position: relative;
    display: inline-block;
}

.icon-picker-trigger {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 12px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    background: #ffffff;
    cursor: pointer;
    transition: all 0.2s;
}

.icon-picker-trigger:hover {
    border-color: #0d6efd;
}

.selected-icon {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
    flex-shrink: 0;
}

.trigger-text {
    font-size: 13px;
    font-weight: 500;
    color: #495057;
    max-width: 150px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.trigger-chevron {
    font-size: 10px;
    color: #adb5bd;
    transition: transform 0.2s;
}

.trigger-chevron.rotated {
    transform: rotate(180deg);
}

/* === DROPDOWN === */
.icon-picker-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    left: 0;
    width: 400px;
    max-width: 90vw;
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    border: 1px solid #e9ecef;
    z-index: 1000;
    overflow: hidden;
}

/* Поиск */
.icon-search-wrapper {
    position: relative;
    padding: 12px;
    border-bottom: 1px solid #e9ecef;
}

.search-icon {
    position: absolute;
    left: 24px;
    top: 50%;
    transform: translateY(-50%);
    color: #adb5bd;
    font-size: 13px;
}

.icon-search-input {
    width: 100%;
    padding: 8px 12px 8px 36px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 13px;
    outline: none;
    transition: all 0.2s;
}

.icon-search-input:focus {
    border-color: #0d6efd;
}

/* Категории */
.icon-categories {
    display: flex;
    gap: 4px;
    padding: 10px 12px;
    border-bottom: 1px solid #e9ecef;
    overflow-x: auto;
    scrollbar-width: none;
}

.icon-categories::-webkit-scrollbar {
    display: none;
}

.category-btn {
    padding: 6px 12px;
    border: none;
    background: #f8f9fa;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 600;
    color: #6c757d;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
}

.category-btn:hover {
    background: #e9ecef;
    color: #495057;
}

.category-btn.active {
    background: #0d6efd;
    color: white;
}

/* Сетка иконок */
.icons-grid {
    display: grid;
    grid-template-columns: repeat(8, 1fr);
    gap: 4px;
    padding: 12px;
    max-height: 280px;
    overflow-y: auto;
}

.icon-item {
    width: 100%;
    aspect-ratio: 1;
    border: 2px solid transparent;
    background: transparent;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.15s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: #495057;
}

.icon-item:hover {
    background: #f8f9fa;
    border-color: #dee2e6;
    color: #0d6efd;
    transform: scale(1.1);
}

.icon-item.selected {
    background: #e7f1ff;
    border-color: #0d6efd;
    color: #0d6efd;
}

.no-icons {
    grid-column: 1 / -1;
    text-align: center;
    padding: 32px 20px;
    color: #adb5bd;
}

.no-icons i {
    font-size: 32px;
    margin-bottom: 8px;
    opacity: 0.5;
}

.no-icons p {
    font-size: 13px;
    margin: 0;
}

/* Скроллбар */
.icons-grid::-webkit-scrollbar {
    width: 6px;
}

.icons-grid::-webkit-scrollbar-track {
    background: #f1f3f5;
}

.icons-grid::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 3px;
}

/* Анимация */
.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
    transition: all 0.2s ease;
}

.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}
</style>

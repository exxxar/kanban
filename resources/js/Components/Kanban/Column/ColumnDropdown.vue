<template>
    <div class="dropdown-wrapper" ref="dropdownRef">
        <button
            class="btn-menu"
            type="button"
            @click.stop="toggle"
        >
            <i class="fa-solid fa-ellipsis-vertical"></i>
        </button>

        <Transition name="dropdown-fade">
            <ul v-if="isOpen" class="dropdown-menu-custom">
                <li>
                    <button class="dropdown-item-custom" @click.stop="handleAction('add-task')">
                        <i class="fa-solid fa-folder-plus me-2 text-primary"></i>
                        Добавить задачу
                    </button>
                </li>
                <li>
                    <button class="dropdown-item-custom" @click.stop="handleAction('open-sort')">
                        <i class="fa-solid fa-folder-tree me-2 text-info"></i>
                        Перенести колонку
                    </button>
                </li>
                <li>
                    <button class="dropdown-item-custom" @click.stop="handleAction('open-notification')">
                        <i class="fa-solid fa-bell me-2 text-warning"></i>
                        Настройка оповещений
                    </button>
                </li>
                <template v-if="column.can_remove">
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <button class="dropdown-item-custom text-danger" @click.stop="handleAction('delete-column')">
                            <i class="fa-solid fa-trash-can me-2"></i>
                            Удалить колонку
                        </button>
                    </li>
                </template>
            </ul>
        </Transition>
    </div>
</template>

<script>
export default {
    props: {
        column: Object
    },
    emits: ['add-task', 'open-sort', 'open-notification', 'delete-column'],
    data() {
        return {
            isOpen: false
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
        handleClickOutside(event) {
            if (this.$refs.dropdownRef && !this.$refs.dropdownRef.contains(event.target)) {
                this.close()
            }
        },
        handleAction(action) {
            this.close()
            this.$emit(action)
        }
    }
}
</script>

<style scoped>
.dropdown-wrapper {
    position: relative;
    flex-shrink: 0;
}

.btn-menu {
    width: 32px;
    height: 32px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 8px;
    background: transparent;
    border: 1px solid transparent;
    color: #6c757d;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-menu:hover {
    background: #e9ecef;
    color: #495057;
    border-color: #dee2e6;
}

.btn-menu:active {
    transform: scale(0.95);
}

.dropdown-menu-custom {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 8px;
    background: #ffffff;
    border-radius: 12px;
    padding: 6px;
    min-width: 220px;
    list-style: none;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    border: 1px solid #e9ecef;
    z-index: 1002;
}

.dropdown-item-custom {
    display: flex;
    align-items: center;
    width: 100%;
    padding: 10px 14px;
    border: none;
    background: none;
    border-radius: 8px;
    font-size: 13px;
    color: #212529;
    cursor: pointer;
    transition: all 0.2s;
    text-align: left;
}

.dropdown-item-custom:hover {
    background: #f8f9fa;
    color: #212529;
}

.dropdown-item-custom:active {
    background: #e9ecef;
}

.dropdown-divider {
    margin: 6px 0;
    border-color: #e9ecef;
}

/* Анимация */
.dropdown-fade-enter-active,
.dropdown-fade-leave-active {
    transition: all 0.2s ease;
}

.dropdown-fade-enter-from,
.dropdown-fade-leave-to {
    opacity: 0;
    transform: translateY(-10px) scale(0.95);
}
</style>

<template>
    <div class="add-menu-wrapper" ref="menuRef">
        <button class="add-menu-trigger" @click.stop="toggle">
            <div class="trigger-icon">
                <i class="fa-solid fa-plus"></i>
            </div>
            <div class="trigger-text">
                <span class="trigger-title">Добавить</span>
                <span class="trigger-hint">задачу или клиента</span>
            </div>
            <i class="fa-solid fa-chevron-down trigger-chevron" :class="{ 'rotated': isOpen }"></i>
        </button>

        <Transition name="menu-fade">
            <div v-if="isOpen" class="add-menu-dropdown">
                <button class="menu-item" @click.stop="selectTask">
                    <div class="menu-item-icon task-icon">
                        <i class="fa-solid fa-check-square"></i>
                    </div>
                    <div class="menu-item-content">
                        <div class="menu-item-title">Задачу</div>
                        <div class="menu-item-desc">Обычная карточка с задачей</div>
                    </div>
                    <i class="fa-solid fa-chevron-right menu-item-arrow"></i>
                </button>

                <button class="menu-item client-item" @click.stop="selectClient">
                    <div class="menu-item-icon client-icon">
                        <i class="fa-solid fa-building"></i>
                    </div>
                    <div class="menu-item-content">
                        <div class="menu-item-title">Клиента</div>
                        <div class="menu-item-desc">CRM-карточка с данными клиента</div>
                    </div>
                    <i class="fa-solid fa-chevron-right menu-item-arrow"></i>
                </button>
            </div>
        </Transition>
    </div>
</template>

<script>
export default {
    props: {
        columnId: { type: [Number, String], required: true }
    },
    emits: ['add-task', 'add-client'],
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
            if (this.$refs.menuRef && !this.$refs.menuRef.contains(event.target)) {
                this.close()
            }
        },
        selectTask() {
            this.close()
            this.$emit('add-task')
        },
        selectClient() {
            this.close()
            this.$emit('add-client')
        }
    }
}
</script>

<style scoped>
.add-menu-wrapper {
    position: relative;
    width: 100%;
}

/* Триггер - компактная кнопка */
.add-menu-trigger {
    width: 100%;
    padding: 10px 14px;
    background: #ffffff;
    border: 2px dashed #dee2e6;
    border-radius: 10px;
    color: #495057;
    font-size: 13px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 10px;
    text-align: left;
}

.add-menu-trigger:hover {
    background: #f8f9fa;
    border-color: #0d6efd;
    color: #0d6efd;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.1);
}

.trigger-icon {
    width: 28px;
    height: 28px;
    border-radius: 8px;
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(13, 110, 253, 0.3);
}

.trigger-text {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    gap: 1px;
    min-width: 0;
    line-height: 1.2;
}

.trigger-title {
    font-size: 13px;
    font-weight: 600;
    color: inherit;
}

.trigger-hint {
    font-size: 10px;
    color: #6c757d;
    font-weight: 400;
}

.trigger-chevron {
    font-size: 10px;
    color: #adb5bd;
    transition: transform 0.2s;
    flex-shrink: 0;
}

.trigger-chevron.rotated {
    transform: rotate(180deg);
}

/* === DROPDOWN (ОТКРЫВАЕТСЯ ВНИЗ) === */
.add-menu-dropdown {
    position: absolute;
    top: calc(100% + 6px); /* Сверху кнопки + отступ */
    left: 0;
    right: 0;
    background: #ffffff;
    border-radius: 12px;
    padding: 6px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    border: 1px solid #e9ecef;
    z-index: 100;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 10px 12px;
    border: none;
    background: transparent;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
    text-align: left;
}

.menu-item:hover {
    background: #f8f9fa;
    transform: translateX(2px);
}

.menu-item-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}

.task-icon {
    background: linear-gradient(135deg, #e7f1ff 0%, #cfe2ff 100%);
    color: #0d6efd;
}

.client-icon {
    background: linear-gradient(135deg, #d1e7dd 0%, #a3cfbb 100%);
    color: #0f5132;
}

.menu-item-content {
    flex-grow: 1;
    min-width: 0;
}

.menu-item-title {
    font-size: 13px;
    font-weight: 600;
    color: #212529;
    margin-bottom: 1px;
}

.menu-item-desc {
    font-size: 10px;
    color: #6c757d;
    line-height: 1.3;
}

.menu-item-arrow {
    font-size: 10px;
    color: #adb5bd;
    opacity: 0;
    transition: all 0.2s;
    flex-shrink: 0;
}

.menu-item:hover .menu-item-arrow {
    opacity: 1;
    transform: translateX(2px);
    color: #0d6efd;
}

/* Анимация появления СНИЗУ ВВЕРХ (но сам dropdown сверху) */
.menu-fade-enter-active,
.menu-fade-leave-active {
    transition: all 0.2s ease;
}

.menu-fade-enter-from,
.menu-fade-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}
</style>

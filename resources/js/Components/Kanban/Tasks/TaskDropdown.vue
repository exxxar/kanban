<template>
    <div class="dropdown-wrapper m-2"  ref="dropdownRef">
        <button
            class="btn-icon"
            type="button"
            data-bs-toggle="dropdown"
            data-bs-boundary="viewport"
            data-bs-auto-close="true"
            aria-expanded="false"
        >
            <i class="fa-solid fa-ellipsis-vertical"></i>
        </button>

        <ul class="dropdown-menu dropdown-menu-end shadow-lg">
            <li>
                <button class="dropdown-item" @click.stop="handleAction('edit')">
                    <i class="fa-solid fa-pen-to-square me-2 text-primary"></i>
                    Редактировать
                </button>
            </li>
            <li>
                <button class="dropdown-item" @click.stop="handleAction('duplicate')">
                    <i class="fa-solid fa-copy me-2 text-info"></i>
                    Дублировать
                </button>
            </li>
            <li>
                <button class="dropdown-item" @click.stop="handleAction('chat')">
                    <i class="fa-solid fa-comments me-2 text-success"></i>
                    Чат
                    <span v-if="task.messages?.length" class="chat-badge">
                        {{ task.messages.length }}
                    </span>
                </button>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <button class="dropdown-item text-danger" @click.stop="handleAction('delete')">
                    <i class="fa-solid fa-trash-can me-2"></i>
                    Удалить
                </button>
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    props: {
        task: Object
    },
    emits: ['edit', 'duplicate', 'delete', 'chat'],

    methods: {
        handleAction(action) {
            this.$emit(action, this.task)
        }
    }
}
</script>

<style scoped>
.dropdown-wrapper {
    /* Не трогаем — Bootstrap сам позиционирует */
}

.btn-icon {
    width: 28px;
    height: 28px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    background: rgba(255, 255, 255, 0.95);
    border: 1px solid #e9ecef;
    color: #6c757d;
    transition: background 0.15s ease, color 0.15s ease, border-color 0.15s ease;
    backdrop-filter: blur(10px);
}

.btn-icon:hover {
    background: #ffffff;
    color: #495057;
    border-color: #dee2e6;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.btn-icon:focus {
    box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
    outline: none;
}

/* Убираем стрелочку Bootstrap */
.btn-icon::after {
    display: none !important;
}

/* Стили меню */
.dropdown-menu {
    border-radius: 10px;
    padding: 6px;
    min-width: 200px;
    border: 1px solid #e9ecef;
}

.dropdown-item {
    display: flex;
    align-items: center;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 13px;
    color: #212529;
    cursor: pointer;
    transition: background 0.15s ease;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
    position: relative;
}

.dropdown-item:hover {
    background: #f8f9fa;
    color: #212529;
}

.dropdown-item:active {
    background: #e9ecef;
}

.dropdown-divider {
    margin: 6px 0;
}

/* Бейдж количества сообщений */
.chat-badge {
    margin-left: auto;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 700;
    min-width: 20px;
    text-align: center;
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
}
</style>

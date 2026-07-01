<template>
    <div class="task-actions">
        <!-- КЛИЕНТ: Редактировать клиента (первая) -->
        <button
            v-if="isClient"
            class="action-btn edit-client-btn"
            @click.stop="handleAction('edit-client')"
            title="Редактировать клиента"
        >
            <i class="fa-solid fa-user-tie"></i>
        </button>

        <!-- КЛИЕНТ: Редактировать задачу (вторая) -->
        <button
            v-if="isClient"
            class="action-btn edit-task-btn"
            @click.stop="handleAction('edit-task')"
            title="Редактировать задачу"
        >
            <i class="fa-solid fa-list-check"></i>
        </button>

        <!-- ОБЫЧНАЯ ЗАДАЧА: одна кнопка редактирования -->
        <button
            v-if="!isClient"
            class="action-btn edit-btn"
            @click.stop="handleAction('edit-task')"
            title="Редактировать"
        >
            <i class="fa-solid fa-pen-to-square"></i>
        </button>

        <button
            class="action-btn duplicate-btn"
            @click.stop="handleAction('duplicate')"
            title="Дублировать"
        >
            <i class="fa-solid fa-copy"></i>
        </button>

        <button
            class="action-btn chat-btn"
            @click.stop="handleAction('chat')"
            title="Чат"
        >
            <i class="fa-solid fa-comments"></i>
            <span v-if="task.messages?.length" class="chat-badge">
                {{ task.messages.length }}
            </span>
        </button>

        <button
            class="action-btn delete-btn"
            @click.stop="handleAction('delete')"
            title="Удалить"
        >
            <i class="fa-solid fa-trash-can"></i>
        </button>
    </div>
</template>

<script>
export default {
    props: {
        task: Object
    },
    emits: ['edit-client', 'edit-task', 'duplicate', 'delete', 'chat'],

    computed: {
        isClient() {
            return this.task.type === 2 || !!this.task.client
        }
    },

    methods: {
        handleAction(action) {
            this.$emit(action, this.task)
        }
    }
}
</script>

<style scoped>
.task-actions {
    display: flex;
    gap: 4px;
    flex-shrink: 0;
}

.action-btn {
    width: 28px;
    height: 28px;
    padding: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 6px;
    border: 1px solid #e9ecef;
    background: rgba(255, 255, 255, 0.95);
    color: #6c757d;
    cursor: pointer;
    transition: all 0.15s ease;
    position: relative;
    backdrop-filter: blur(10px);
}

.action-btn i {
    font-size: 12px;
}

/* === ЦВЕТА КНОПОК ПРИ HOVER === */

/* Редактировать клиента (фиолетовый) */
.edit-client-btn:hover {
    background: #f3f0ff;
    border-color: #7c3aed;
    color: #7c3aed;
    box-shadow: 0 2px 8px rgba(124, 58, 237, 0.2);
}

/* Редактировать задачу (синий) */
.edit-task-btn:hover {
    background: #e7f1ff;
    border-color: #0d6efd;
    color: #0d6efd;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.2);
}

/* Редактировать обычную задачу (синий) */
.edit-btn:hover {
    background: #e7f1ff;
    border-color: #0d6efd;
    color: #0d6efd;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.2);
}

/* Дублировать */
.duplicate-btn:hover {
    background: #e7f5ff;
    border-color: #0dcaf0;
    color: #0dcaf0;
    box-shadow: 0 2px 8px rgba(13, 202, 240, 0.2);
}

/* Чат */
.chat-btn:hover {
    background: #d1e7dd;
    border-color: #10b981;
    color: #10b981;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.2);
}

/* Удалить */
.delete-btn:hover {
    background: #fff5f5;
    border-color: #dc3545;
    color: #dc3545;
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.2);
}

/* Активное состояние */
.action-btn:active {
    transform: scale(0.92);
}

/* Бейдж количества сообщений */
.chat-badge {
    position: absolute;
    top: -4px;
    right: -4px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 1px 5px;
    border-radius: 8px;
    font-size: 9px;
    font-weight: 700;
    min-width: 16px;
    text-align: center;
    box-shadow: 0 2px 6px rgba(16, 185, 129, 0.3);
    border: 1px solid white;
}

/* === TOOLTIP СНИЗУ === */
.action-btn[title]:hover::before {
    content: attr(title);
    position: absolute;
    top: calc(100% + 6px);
    left: 50%;
    transform: translateX(-50%);
    background: #212529;
    color: white;
    padding: 4px 8px;
    border-radius: 6px;
    font-size: 11px;
    white-space: nowrap;
    pointer-events: none;
    z-index: 1002;
    animation: tooltipFadeIn 0.15s ease;
}

/* Стрелочка сверху (указывает на кнопку) */
.action-btn[title]:hover::after {
    content: '';
    position: absolute;
    top: calc(100% - 2px);
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

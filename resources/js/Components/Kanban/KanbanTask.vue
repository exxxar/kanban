<template>
    <div
        :data-card-id="task.id"
        draggable="true"
        @dragstart="$emit('dragstart', $event, task)"
        @dragover.prevent
        @drop.stop="$emit('drop', $event, task)"
        :class="cardClasses"
        class="kanban-task mb-3 rounded-3"
        @dblclick="$emit('edit', task)">


        <div v-if="isClient" class="card-accent"></div>

        <!-- ШАПКА КАРТОЧКИ -->
        <div class="card-header-custom">
            <!-- Drag handle -->
            <div class="drag-handle" title="Перетащите карточку">
                <i class="fa-solid fa-grip-vertical"></i>
            </div>

            <!-- Номер задачи -->
            <div class="task-number">
                #{{ task.id }}
            </div>

            <!-- Dropdown (справа) -->
            <div class="task-dropdown-wrapper" @dragstart.stop @mousedown.stop>
                <TaskActions
                    :task="task"
                    @edit-task="$emit('edit-task', task)"
                    @edit-client="$emit('edit-client', task)"
                    @chat="$emit('chat', task)"
                    @duplicate="$emit('duplicate', task)"
                    @delete="$emit('delete', task)"
                />
            </div>
        </div>

        <!-- ТЕЛО КАРТОЧКИ -->
        <div class="card-body-custom">
            <!-- Рендерим нужный тип карточки -->
            <component
                :is="cardComponent"
                :task="task"
                @update:showSubtasks="showSubtasks = $event"
            />

            <TaskCounters
                v-if="hasCounters"
                :task="task"
                :showSubtasks="showSubtasks"
                @toggleSubtasks="showSubtasks = !showSubtasks"
            />

            <SubtasksList
                v-if="showSubtasks && task.subtasks?.length"
                :subtasks="task.subtasks"
            />
        </div>
    </div>
</template>

<script>
import TaskDropdown from '@/Components/Kanban/Tasks/TaskDropdown.vue';
import TaskActions from '@/Components/Kanban/Tasks/TaskActions.vue';
import ClientCard from '@/Components/Kanban/Clients/ClientCard.vue';
import TaskCard from '@/Components/Kanban/Tasks/TaskCard.vue';
import TaskCounters from '@/Components/Kanban/Tasks/TaskCounters.vue';
import SubtasksList from '@/Components/Kanban/Tasks/SubtasksList.vue';

export default {
    components: {
        TaskDropdown,
        TaskActions,
        ClientCard,
        TaskCard,
        TaskCounters,
        SubtasksList
    },
    props: {
        task: Object
    },
    emits: ['dragstart', 'drop', 'edit-client','edit-task','duplicate', 'delete', 'chat'], // ← ДОБАВИТЬ
    data() {
        return {
            showSubtasks: false
        }
    },
    computed: {
        isClient() {
            return this.task.type === 2 || !!this.task.client;
        },
        cardComponent() {
            return this.isClient ? 'ClientCard' : 'TaskCard';
        },
        cardClasses() {
            let classes = ['modern-card'];

            if (this.isClient) {
                classes.push('client-card');
            } else if (!this.task.last_viewed_at) {
                classes.push('unviewed-card');
            }

            return classes;
        },
        hasCounters() {
            return (this.task.subtasks?.length > 0)
                || (this.task.comments_count > 0)
                || (this.task.messages?.length > 0)
                || (this.task.attachments?.length > 0);
        }
    }
}
</script>

<style scoped>
/* === БАЗОВЫЕ СТИЛИ КАРТОЧКИ === */
.kanban-task {
    background: #ffffff;
    border: 1px solid #e9ecef;
    cursor: grab;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: visible;
}

.kanban-task:hover {
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    border-color: #dee2e6;
}

.kanban-task:active {
    cursor: grabbing;
}

/* === ШАПКА КАРТОЧКИ === */
.card-header-custom {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 16px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    border-radius: 0.375rem 0.375rem 0 0;
}

/* Drag handle */
.drag-handle {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    color: #adb5bd;
    cursor: grab;
    transition: all 0.2s;
    border-radius: 4px;
    flex-shrink: 0;
}

.drag-handle:hover {
    background: #e9ecef;
    color: #495057;
}

.drag-handle:active {
    cursor: grabbing;
    background: #dee2e6;
}

.drag-handle i {
    font-size: 14px;
}

/* Номер задачи */
.task-number {
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
    flex-grow: 1;
}

/* Dropdown wrapper */
.task-dropdown-wrapper {
    flex-shrink: 0;
}

/* === ТЕЛО КАРТОЧКИ === */
.card-body-custom {
    padding: 16px;
}

/* === АКЦЕНТ ДЛЯ КЛИЕНТА === */
.card-accent {

    left: 0;
    top: 0;
    bottom: 0;
    width: 4px;
    background: linear-gradient(180deg, #0d6efd 0%, #0dcaf0 100%);
    border-radius: 0.375rem 0 0 0.375rem;
}

.client-card {
    background: linear-gradient(135deg, #f8f9ff 0%, #ffffff 100%);
}

.client-card:hover {
    background: linear-gradient(135deg, #f0f4ff 0%, #ffffff 100%);
}

.client-card .card-header-custom {
    background: linear-gradient(135deg, #e7f1ff 0%, #f0f4ff 100%);
}

/* === НЕПРОСМОТРЕННАЯ КАРТОЧКА === */
.unviewed-card {
    background: linear-gradient(135deg, #fff5f5 0%, #ffffff 100%);
    border-color: #fecaca;
}

.unviewed-card:hover {
    background: linear-gradient(135deg, #fee2e2 0%, #ffffff 100%);
}

.unviewed-card .card-header-custom {
    background: linear-gradient(135deg, #ffe5e5 0%, #fff5f5 100%);
}
</style>

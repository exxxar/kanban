
<template>
    <div
        :data-column-id="column.id"
        class="kanban-column p-3 bg-light rounded "
         @dragover.prevent
         @drop="onDrop">

        <slot name="head"></slot>
        <div class="d-flex justify-content-between align-items-center mb-2">
            <!-- Название / редактирование -->
            <div class="flex-grow-1 me-2">


                <!-- Просмотр -->
                <h4 v-if="!editing"
                    @dblclick="startEditing"
                    class="column-title"
                >
                    <span class="bg-primary small badge me-2">#{{ column.thread }} </span>{{ localTitle }}
                </h4>

                <!-- Редактирование -->
                <div v-else class="d-flex align-items-center gap-2">
                    <input
                        v-model="localTitle"
                        class="form-control form-control-sm"
                        @keyup.enter="saveTitle"
                        @keyup.esc="cancelEditing"
                        autofocus
                    >
                    <button class="btn btn-sm btn-primary" @click="saveTitle">
                        ✔
                    </button>
                </div>

            </div>

            <!-- Кнопки действий -->
            <div class="d-flex gap-1">
                <button class="btn btn-sm btn-primary" @click="addTask">+</button>

                <template v-if="column.can_remove">
                    <button class="btn btn-sm btn-danger"
                            @click="showDeleteModal = true"
                            title="Удалить колонку">
                        🗑️
                    </button>
                </template>
            </div>
        </div>

        <div class="kanban-tasks">
            <KanbanTask
                v-for="task in column.tasks"
                :key="task.id"
                :task="task"
                draggable="true"
                @dragstart="onDragStart(task)"
                @edit="editTask"

                @drop="onTaskDrop(task)"
                @duplicate="duplicateTask"
                @delete="deleteTask"
            />

            <button
                v-if="canLoadMore"
                class="btn btn-sm btn-secondary w-100 mt-2"
                @click="loadMore"
            >
                Загрузить ещё
            </button>
        </div>

        <ConfirmModal
            v-model:show="showDeleteModal"
            title="Удалить колонку?"
            description="Это действие удалит колонку и все карточки в данной колонке."
            @accept="deleteColumn"
            @reject="showDeleteModal = false"
        />


    </div>


</template>

<script>
import {useKanbanStore} from '@/stores/useKanbanStore'
import KanbanTask from './KanbanTask.vue'
import ConfirmModal from "@/Components/Kanban/ConfirmModal.vue";
export default {
    components: {KanbanTask, ConfirmModal},

    props: {
        column: Object
    },

    data() {
        return {
            showDeleteModal:false,
            dragTask: null,
            editing: false,
            localTitle: this.column.title
        }
    },

    setup() {
        const store = useKanbanStore()
        return {store}
    },
    computed: {
        canLoadMore() {
            const info = this.store.taskPagination[this.column.id] || null
            if (!info)
                return false;

            return info && info.page < info.lastPage
        }
    },


    methods: {
        onTaskDrop(targetTask) {
            if (!this.dragTask || this.dragTask.id === targetTask.id) return

            this.store.reorderTask(
                this.dragTask.id,
                targetTask.id,
                this.column.id
            )

            this.dragTask = null
        },
        loadMore() {
            this.store.loadTasks(this.column.id)
        },
        addTask() {
            this.$emit('add-task', this.column.id)
        },

        editTask(task) {
            this.$emit('add-task', this.column.id, task)
        },

        deleteTask(task) {
            this.store.deleteTask(task.id)
        },

        deleteColumn() {
            this.store.deleteColumn(this.column.id)
        },

        onDragStart(task) {
            this.dragTask = task
            event.dataTransfer.setData('taskId', task.id)
        },
        duplicateTask(task) {
            this.store.duplicateTask(task)
        },
        onDrop() {
            const taskId = Number(event.dataTransfer.getData('taskId'))
            this.store.moveTask(taskId, this.column.id)
        },

        // --- Редактирование названия ---
        startEditing() {
            this.localTitle = this.column.title
            this.editing = true
        },

        async saveTitle() {
            if (!this.localTitle.trim()) return

            await this.store.renameColumn(this.column.id, this.localTitle)
            this.editing = false
        },

        cancelEditing() {
            this.localTitle = this.column.title
            this.editing = false
        }
    }
}
</script>

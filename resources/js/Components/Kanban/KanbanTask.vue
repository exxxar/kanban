<template>
    <div
        :data-card-id="task.id"
        draggable="true"
        @dragstart="$emit('dragstart', task)"
        @dragover.prevent
        @drop.stop="$emit('drop', task)"
        :class="{ 'bg-warning bg-opacity-25': !task.last_viewed_at }"
        class="kanban-task p-2 mb-2 bg-white rounded shadow-sm"
        @dblclick="$emit('edit', task)">

        <div class="d-flex justify-content-between align-items-start mb-1">

            <strong style="line-height: 100%;">{{ task.title }}</strong>

            <!-- Dropdown -->
            <div class="dropdown">
                <button
                    class="btn btn-sm btn-light dropdown-toggle"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                ></button>

                <ul class="dropdown-menu dropdown-menu-end">

                    <li>
                        <button
                            class="dropdown-item"
                            @click.stop="$emit('edit', task)"
                        >
                            ✏️ Редактировать
                        </button>
                    </li>

                    <li>
                        <button
                            class="dropdown-item"
                            @click.stop="$emit('duplicate', task)"
                        >
                            📄 Дублировать
                        </button>
                    </li>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <li>
                        <button
                            class="dropdown-item text-danger"
                            @click.stop="$emit('delete', task)"
                        >
                            🗑️ Удалить
                        </button>
                    </li>

                </ul>
            </div>
        </div>


        <div
            :class="[
        task.priority==='low'?'bg-secondary':'',
        task.priority==='medium'?'bg-warning':'',
        task.priority==='high'?'bg-success':'',

]"
            class="small badge text-white">{{ priority[task.priority] || '-' }}
        </div>

        <span
            v-for="label in task.labels"
            :key="label"
            class="badge bg-secondary me-2"
        >
    {{ label }}
</span>

        <div class="d-flex flex-wrap mt-2">
            <span
                v-for="tag in task.tags"
                :key="tag.id"
                class="bg-info badge"
                :style="{ background: tag.color }"
            >
                #{{ tag.name }}
            </span>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        task: Object
    },
    data() {
        return {
            priority: {
                low: 'Низкий',
                medium: 'Средний',
                high: 'Высокий',
            }
        }
    }
}
</script>

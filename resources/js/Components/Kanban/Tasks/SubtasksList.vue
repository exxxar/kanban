<template>
    <div class="subtasks-panel mt-3 pt-3">
        <div v-for="sub in subtasks" :key="sub.id" class="subtask-mini-item">
            <div class="subtask-checkbox" :class="{ 'checked': sub.done }">
                <i v-if="sub.done" class="fa-solid fa-check"></i>
            </div>
            <span class="subtask-mini-text" :class="{ 'completed': sub.done }" :title="sub.text">
                {{ sub.text }}
            </span>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        subtasks: Array
    }
}
</script>

<style scoped>
.subtasks-panel {
    max-height: 180px;
    overflow-y: auto;
    border-top: 1px solid #e9ecef;
}

.subtask-mini-item {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 8px;
    padding: 6px;
    border-radius: 6px;
    transition: background 0.2s;
}

.subtask-mini-item:hover {
    background: #f8f9fa;
}

.subtask-checkbox {
    width: 18px;
    height: 18px;
    border: 2px solid #dee2e6;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.2s;
}

.subtask-checkbox.checked {
    background: #10b981;
    border-color: #10b981;
    color: white;
}

.subtask-checkbox i {
    font-size: 10px;
}

.subtask-mini-text {
    font-size: 12px;
    color: #495057;
    line-height: 1.4;
    flex-grow: 1;
}

.subtask-mini-text.completed {
    text-decoration: line-through;
    color: #adb5bd;
}

.subtasks-panel::-webkit-scrollbar {
    width: 6px;
}

.subtasks-panel::-webkit-scrollbar-track {
    background: #f1f3f5;
    border-radius: 3px;
}

.subtasks-panel::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 3px;
}

.subtasks-panel::-webkit-scrollbar-thumb:hover {
    background: #adb5bd;
}
</style>

<template>
    <div class="task-counters mt-3 pt-3">
        <!-- Подзадачи -->
        <span
            v-if="task.subtasks && task.subtasks.length"
            class="counter-item"
            title="Показать/скрыть подзадачи"
            @click.stop="$emit('toggleSubtasks')"
        >
            <i class="fa-solid fa-list-check"></i>
            <span :class="{ 'text-success': subtasksDone === task.subtasks.length && task.subtasks.length > 0 }">
                {{ subtasksDone }}/{{ task.subtasks.length }}
            </span>
            <i :class="showSubtasks ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down'" class="chevron-icon"></i>
        </span>

        <!-- Сообщения -->
        <span v-if="task.messages?.length > 0" class="counter-item" title="Сообщения">
            <i class="fa-regular fa-comment-dots"></i>
            <span>{{ task.messages.length }}</span>
        </span>

        <!-- Комментарии -->
        <span v-if="task.comments_count" class="counter-item" title="Комментарии">
            <i class="fa-regular fa-comment"></i>
            <span>{{ task.comments_count }}</span>
        </span>

        <!-- Теги -->
        <span v-if="task.tags && task.tags.length > 0" class="counter-item" :title="`Теги: ${task.tags.map(t => '#' + t.name).join(', ')}`">
            <i class="fa-solid fa-tags text-purple"></i>
            <span>{{ task.tags.length }}</span>
        </span>

        <!-- Метки -->
        <span v-if="task.labels && task.labels.length > 0" class="counter-item" :title="`Метки: ${task.labels.join(', ')}`">
            <i class="fa-solid fa-tag text-primary"></i>
            <span>{{ task.labels.length }}</span>
        </span>

        <!-- Дедлайн -->
        <span v-if="task.due_date" class="counter-item" :class="deadlineClass" :title="`Дедлайн: ${formatDate(task.due_date)}`">
            <i class="fa-regular fa-calendar-check"></i>
            <span>{{ deadlineText }}</span>
        </span>

        <!-- Вложения (по типам) -->
        <template v-for="(stat, idx) in attachmentStats" :key="idx">
            <span class="counter-item" :title="stat.title">
                <i :class="[stat.icon, stat.class]"></i>
                <span>{{ stat.count }}</span>
            </span>
        </template>
    </div>
</template>

<script>
export default {
    props: {
        task: Object,
        showSubtasks: Boolean
    },
    emits: ['toggleSubtasks'],
    computed: {
        subtasksDone() {
            return this.task.subtasks?.filter(s => s.done).length ?? 0;
        },
        deadlineClass() {
            if (!this.task.due_date) return '';
            const due = new Date(this.task.due_date);
            const now = new Date();
            const diff = due - now;
            const days = diff / (1000 * 60 * 60 * 24);

            if (days < 0) return 'deadline-overdue';
            if (days < 1) return 'deadline-urgent';
            if (days < 3) return 'deadline-warning';
            return 'deadline-normal';
        },
        deadlineText() {
            if (!this.task.due_date) return '';
            const due = new Date(this.task.due_date);
            const now = new Date();
            const diff = due - now;
            const days = Math.floor(diff / (1000 * 60 * 60 * 24));

            if (days < 0) return `Просрочено`;
            if (days === 0) return `Сегодня`;
            if (days === 1) return `Завтра`;
            if (days < 7) return `${days} дн.`;
            return this.formatDate(this.task.due_date);
        },
        attachmentStats() {
            if (!this.task.attachments || !this.task.attachments.length) return [];

            const stats = {
                image: { count: 0, icon: 'fa-regular fa-image', class: 'text-primary', title: 'Изображения' },
                pdf: { count: 0, icon: 'fa-regular fa-file-pdf', class: 'text-danger', title: 'PDF документы' },
                word: { count: 0, icon: 'fa-regular fa-file-word', class: 'text-primary', title: 'Документы Word' },
                excel: { count: 0, icon: 'fa-regular fa-file-excel', class: 'text-success', title: 'Таблицы Excel' },
                video: { count: 0, icon: 'fa-regular fa-file-video', class: 'text-info', title: 'Видео' },
                other: { count: 0, icon: 'fa-solid fa-paperclip', class: 'text-secondary', title: 'Другие файлы' }
            };

            this.task.attachments.forEach(file => {
                const mime = file.mime || '';
                const name = (file.name || '').toLowerCase();

                if (mime.startsWith('image/')) stats.image.count++;
                else if (mime === 'application/pdf' || name.endsWith('.pdf')) stats.pdf.count++;
                else if (mime.includes('word') || name.endsWith('.doc') || name.endsWith('.docx')) stats.word.count++;
                else if (mime.includes('sheet') || mime.includes('excel') || name.endsWith('.xls') || name.endsWith('.xlsx')) stats.excel.count++;
                else if (mime.startsWith('video/')) stats.video.count++;
                else stats.other.count++;
            });

            return Object.values(stats).filter(s => s.count > 0);
        }
    },
    methods: {
        formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('ru-RU', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric'
            });
        }
    }
}
</script>

<style scoped>
.task-counters {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
    border-top: 1px solid #e9ecef;
}

.counter-item {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 12px;
    color: #6c757d;
    font-weight: 500;
    cursor: pointer;
    padding: 4px 8px;
    border-radius: 6px;
    transition: all 0.2s;
}

.counter-item:hover {
    background: #f8f9fa;
    color: #495057;
}

.counter-item i {
    font-size: 13px;
}

.chevron-icon {
    font-size: 9px;
    margin-left: 2px;
}

/* Цвета для новых иконок */
.text-purple {
    color: #7c3aed;
}

.text-primary {
    color: #0d6efd;
}

/* Дедлайн */
.deadline-normal {
    color: #6c757d;
}

.deadline-warning {
    color: #f59e0b;
}

.deadline-urgent {
    color: #dc3545;
    font-weight: 600;
}

.deadline-overdue {
    color: #dc3545;
    background: #fff5f5;
    font-weight: 600;
}

.deadline-overdue:hover {
    background: #fee2e2;
}
</style>

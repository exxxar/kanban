<template>
    <div class="task-card-compact">
        <!-- Теги вверху карточки -->
        <div v-if="task.tags && task.tags.length > 0" class="task-tags-row">
            <span
                v-for="tag in visibleTags"
                :key="tag.id"
                class="tag-pill"
                :style="{
                    background: tag.color + '18',
                    color: tag.color,
                    borderColor: tag.color + '40'
                }"
            >
                {{ tag.name }}
            </span>
            <span
                v-if="hiddenTagsCount > 0"
                class="tag-pill tag-more"
                :title="hiddenTagsNames"
            >
                +{{ hiddenTagsCount }}
            </span>
        </div>

        <!-- Изображение (если есть) -->
        <div v-if="firstImage" class="task-image-preview">
            <img :src="`/storage/${firstImage.path}`" :alt="firstImage.name" class="task-preview-img" />
        </div>

        <!-- Основная информация -->
        <div class="task-main">
            <!-- Название -->
            <div class="task-title">
                {{ task.title }}
            </div>

            <!-- Приоритет + дата -->
            <div class="task-meta">
                <span
                    v-if="task.priority"
                    :class="priorityClass"
                    class="priority-badge"
                >
                    <i :class="priorityIcon"></i>
                    {{ priority[task.priority] }}
                </span>
                <span class="task-date">
                    <i class="fa-regular fa-clock"></i>
                    {{ fromNow(task.created_at) }}
                </span>
            </div>
        </div>
    </div>
</template>

<script>
import { fromNow } from '@/stores/utils/time.js';

export default {
    props: {
        task: Object
    },
    data() {
        return {
            maxVisibleTags: 3,
            priority: {
                low: 'Низкий',
                medium: 'Средний',
                high: 'Высокий',
            }
        }
    },
    computed: {
        firstImage() {
            if (!this.task.attachments || !this.task.attachments.length) return null;
            return this.task.attachments.find(f => f.mime && f.mime.startsWith('image/')) || null;
        },
        priorityClass() {
            const map = {
                low: 'priority-low',
                medium: 'priority-medium',
                high: 'priority-high'
            };
            return map[this.task.priority] || '';
        },
        priorityIcon() {
            const map = {
                low: 'fa-solid fa-arrow-down',
                medium: 'fa-solid fa-minus',
                high: 'fa-solid fa-arrow-up'
            };
            return map[this.task.priority] || '';
        },
        visibleTags() {
            if (!this.task.tags) return [];
            return this.task.tags.slice(0, this.maxVisibleTags);
        },
        hiddenTagsCount() {
            if (!this.task.tags) return 0;
            return Math.max(0, this.task.tags.length - this.maxVisibleTags);
        },
        hiddenTagsNames() {
            if (!this.task.tags) return '';
            return this.task.tags
                .slice(this.maxVisibleTags)
                .map(t => '#' + t.name)
                .join(', ');
        }
    },
    methods: {
        fromNow
    }
}
</script>

<style scoped>
.task-card-compact {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

/* === ТЕГИ ВВЕРХУ === */
.task-tags-row {
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
    margin-bottom: -2px;
}

.tag-pill {
    display: inline-flex;
    align-items: center;
    padding: 2px 8px;
    border: 1px solid;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 600;
    line-height: 1.4;
    white-space: nowrap;
    max-width: 100px;
    overflow: hidden;
    text-overflow: ellipsis;
    transition: all 0.15s ease;
    cursor: default;
}

.tag-pill:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}

.tag-more {
    background: #f1f3f5 !important;
    color: #6c757d !important;
    border-color: #dee2e6 !important;
    font-size: 9px;
    padding: 2px 6px;
    cursor: help;
}

.tag-more:hover {
    background: #e9ecef !important;
    color: #495057 !important;
}

/* === ИЗОБРАЖЕНИЕ === */
.task-image-preview {
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: -2px;
}

.task-preview-img {
    width: 100%;
    max-height: 100px;
    object-fit: cover;
    display: block;
    transition: transform 0.3s;
}

.kanban-task:hover .task-preview-img {
    transform: scale(1.02);
}

/* === ОСНОВНАЯ ИНФОРМАЦИЯ === */
.task-main {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.task-title {
    font-size: 13px;
    font-weight: 600;
    color: #212529;
    line-height: 1.3;
    overflow: hidden;
    text-overflow: ellipsis;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.task-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.priority-badge {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    padding: 3px 8px;
    border-radius: 5px;
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

.priority-badge i {
    font-size: 9px;
}

.priority-low {
    background: #e9ecef;
    color: #6c757d;
}

.priority-medium {
    background: #fff3cd;
    color: #856404;
}

.priority-high {
    background: #d1e7dd;
    color: #0f5132;
}

.task-date {
    font-size: 10px;
    color: #6c757d;
    display: flex;
    align-items: center;
    gap: 3px;
}

.task-date i {
    font-size: 9px;
    opacity: 0.7;
}
</style>

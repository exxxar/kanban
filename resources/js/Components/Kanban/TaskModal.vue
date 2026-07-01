<!-- TaskModal.vue -->
<template>
    <Transition name="modal-fade">
        <div v-if="isVisible" class="modal-overlay" @click.self="handleOverlayClick">
            <div class="modal-window">
                <!-- HEADER -->
                <div class="modal-header-custom">
                    <div class="header-content">
                        <div class="header-icon">
                            <i :class="task ? 'fa-solid fa-pen-to-square' : 'fa-solid fa-plus'"></i>
                        </div>
                        <div class="header-text">
                            <h3 class="modal-title-text">
                                {{ task ? 'Редактировать задачу' : 'Новая задача' }}
                            </h3>
                            <p class="modal-subtitle">
                                {{ task ? 'Измените параметры задачи' : 'Заполните данные для создания задачи' }}
                            </p>
                        </div>
                    </div>
                    <button class="close-btn" @click="close" title="Закрыть">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- TABS (если редактируем) -->
                <div v-if="task" class="modal-tabs">
                    <button
                        class="tab-btn"
                        :class="{ active: tab === 'task' }"
                        @click="tab = 'task'"
                    >
                        <i class="fa-solid fa-list-check me-2"></i>
                        Задача
                    </button>
                    <button
                        class="tab-btn"
                        :class="{ active: tab === 'chat' }"
                        @click="tab = 'chat'"
                    >
                        <i class="fa-solid fa-comments me-2"></i>
                        Чат
                    </button>
                </div>

                <!-- BODY -->
                <div class="modal-body-custom">
                    <!-- TAB: ЗАДАЧА -->
                    <form v-show="tab === 'task'" @submit.prevent="submit" class="task-form">

                        <!-- СЕКЦИЯ: Основная информация -->
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon main-icon">
                                    <i class="fa-solid fa-info-circle"></i>
                                </div>
                                <div>
                                    <h4 class="section-title">Основная информация</h4>
                                    <p class="section-desc">Название и описание задачи</p>
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label class="form-label-custom">
                                        Название <span class="required">*</span>
                                    </label>
                                    <div class="input-wrapper">
                                        <i class="fa-solid fa-heading input-icon"></i>
                                        <input
                                            type="text"
                                            class="custom-input"
                                            placeholder="Введите название задачи"
                                            v-model="local.title"
                                            required
                                        >
                                    </div>
                                </div>

                                <div class="form-group full-width">
                                    <label class="form-label-custom">Описание</label>
                                    <div class="textarea-wrapper">
                                        <i class="fa-solid fa-align-left textarea-icon"></i>
                                        <textarea
                                            class="custom-textarea"
                                            placeholder="Опишите задачу подробнее..."
                                            v-model="local.description"
                                            rows="4"
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- СЕКЦИЯ: Параметры -->
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon params-icon">
                                    <i class="fa-solid fa-sliders"></i>
                                </div>
                                <div>
                                    <h4 class="section-title">Параметры</h4>
                                    <p class="section-desc">Приоритет, сроки и метки</p>
                                </div>
                            </div>

                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label-custom">Приоритет</label>
                                    <div class="custom-select-wrapper">
                                        <select v-model="local.priority" class="custom-select">
                                            <option value="low">🟢 Низкий</option>
                                            <option value="medium">🟡 Средний</option>
                                            <option value="high">🔴 Высокий</option>
                                        </select>
                                        <i class="fa-solid fa-chevron-down select-icon"></i>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label-custom">Срок выполнения</label>
                                    <div class="input-wrapper">
                                        <i class="fa-solid fa-calendar-days input-icon"></i>
                                        <input
                                            type="date"
                                            class="custom-input"
                                            v-model="local.due_date"
                                        >
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- СЕКЦИЯ: Теги и категории -->
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon tags-icon">
                                    <i class="fa-solid fa-tags"></i>
                                </div>
                                <div>
                                    <h4 class="section-title">Теги и категории</h4>
                                    <p class="section-desc">Организуйте задачу с помощью меток</p>
                                </div>
                            </div>

                            <!-- Теги -->
                            <div class="form-group mb-3">
                                <label class="form-label-custom">Теги</label>
                                <div class="tags-grid">
                                    <div
                                        v-for="tag in [...store.tags, ...task.tags]"
                                        :key="tag.id"
                                        class="tag-checkbox-wrapper"
                                    >
                                        <label
                                            class="tag-checkbox"
                                            :class="{ selected: local.tag_ids.includes(tag.id) }"
                                            :style="{
                                                borderColor: tag.color,
                                                background: local.tag_ids.includes(tag.id) ? tag.color + '20' : 'transparent'
                                            }"
                                        >
                                            <input
                                                type="checkbox"
                                                :value="tag.id"
                                                v-model="local.tag_ids"
                                                class="hidden-checkbox"
                                            >
                                            <span class="tag-dot" :style="{ background: tag.color }"></span>
                                            <span class="tag-name">{{ tag.name }}</span>
                                            <i v-if="local.tag_ids.includes(tag.id)" class="fa-solid fa-check tag-check"></i>

                                            <button
                                                type="button"
                                                class="btn-delete-tag"
                                                @click.stop="deleteTag(tag)"
                                                title="Удалить тег"
                                            >
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </label>

                                    </div>
                                </div>
                            </div>

                            <!-- Новый тег -->
                            <div class="form-group mb-3">
                                <label class="form-label-custom">Создать новый тег</label>
                                <div class="new-tag-row">
                                    <div class="input-wrapper flex-grow">
                                        <i class="fa-solid fa-plus input-icon"></i>
                                        <input
                                            class="custom-input"
                                            v-model="newTagName"
                                            placeholder="Название тега"
                                        >
                                    </div>
                                    <div class="color-picker-wrapper">
                                        <input
                                            v-model="newTagColor"
                                            type="color"
                                            class="color-picker"
                                        >
                                    </div>
                                    <button
                                        type="button"
                                        class="btn-add-tag"
                                        @click="addTag"
                                        :disabled="!newTagName.trim()"
                                    >
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Категории с группировкой и сворачиванием -->
                            <div class="form-group">
                                <label class="form-label-custom">Категории</label>

                                <!-- Сводка выбранных категорий -->
                                <div v-if="local.labels.length > 0" class="selected-categories-summary">
                                    <div class="summary-label">
                                        <i class="fa-solid fa-check-circle"></i>
                                        Выбрано: {{ local.labels.length }}
                                    </div>
                                    <div class="summary-tags">
            <span
                v-for="label in local.labels"
                :key="label"
                class="summary-tag"
            >
                <i :class="getCategoryIcon(label)"></i>
                {{ getCategoryLabel(label) }}
                <button
                    type="button"
                    class="remove-tag-btn"
                    @click="removeLabel(label)"
                    title="Убрать"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </span>
                                    </div>
                                </div>

                                <div class="categories-groups">
                                    <div
                                        v-for="(labels, groupName) in labelGroups"
                                        :key="groupName"
                                        class="category-group"
                                        :class="{ collapsed: isGroupCollapsed(groupName) }"
                                    >
                                        <!-- Заголовок группы (кликабельный) -->
                                        <button
                                            type="button"
                                            class="group-header"
                                            @click="toggleGroup(groupName)"
                                        >
                                            <div class="group-header-left">
                                                <i
                                                    class="fa-solid fa-chevron-down group-chevron"
                                                    :class="{ rotated: isGroupCollapsed(groupName) }"
                                                ></i>
                                                <span class="group-title">{{ groupName }}</span>
                                                <span class="group-count">
                        {{ getSelectedCount(labels) }}/{{ labels.length }}
                    </span>
                                            </div>
                                            <div class="group-header-right">
                    <span v-if="getSelectedCount(labels) > 0" class="group-selected-badge">
                        {{ getSelectedCount(labels) }} выбрано
                    </span>
                                                <button
                                                    type="button"
                                                    class="btn-select-all"
                                                    @click.stop="toggleSelectAll(labels)"
                                                    :title="isAllSelected(labels) ? 'Снять все' : 'Выбрать все'"
                                                >
                                                    <i :class="isAllSelected(labels) ? 'fa-solid fa-circle-check' : 'fa-regular fa-circle'"></i>
                                                </button>
                                            </div>
                                        </button>

                                        <!-- Содержимое группы (сворачиваемое) -->
                                        <Transition name="group-collapse">
                                            <div v-show="!isGroupCollapsed(groupName)" class="group-content">
                                                <div class="categories-grid">
                                                    <label
                                                        v-for="label in labels"
                                                        :key="label"
                                                        class="category-badge"
                                                        :class="{ selected: local.labels.includes(label) }"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            class="hidden-checkbox"
                                                            :value="label"
                                                            v-model="local.labels"
                                                        >
                                                        <i :class="getCategoryIcon(label)" class="category-icon"></i>
                                                        <span class="category-name">{{ getCategoryLabel(label) }}</span>
                                                        <i v-if="local.labels.includes(label)" class="fa-solid fa-check category-check"></i>
                                                    </label>
                                                </div>
                                            </div>
                                        </Transition>
                                    </div>

                                    <!-- Кастомные категории (если есть) -->
                                    <div
                                        v-if="customCategories.length > 0"
                                        class="category-group"
                                        :class="{ collapsed: isGroupCollapsed('custom') }"
                                    >
                                        <button
                                            type="button"
                                            class="group-header"
                                            @click="toggleGroup('custom')"
                                        >
                                            <div class="group-header-left">
                                                <i
                                                    class="fa-solid fa-chevron-down group-chevron"
                                                    :class="{ rotated: isGroupCollapsed('custom') }"
                                                ></i>
                                                <span class="group-title">
                        <i class="fa-solid fa-puzzle-piece"></i>
                        Ваши категории
                    </span>
                                                <span class="group-count">
                        {{ getSelectedCount(customCategories.map(c => c.key)) }}/{{ customCategories.length }}
                    </span>
                                            </div>
                                            <div class="group-header-right">
                    <span v-if="getSelectedCount(customCategories.map(c => c.key)) > 0" class="group-selected-badge">
                        {{ getSelectedCount(customCategories.map(c => c.key)) }} выбрано
                    </span>
                                            </div>
                                        </button>

                                        <Transition name="group-collapse">
                                            <div v-show="!isGroupCollapsed('custom')" class="group-content">
                                                <div class="categories-grid">
                                                    <label
                                                        v-for="cat in customCategories"
                                                        :key="cat.id"
                                                        class="category-badge"
                                                        :class="{ selected: local.labels.includes(cat.key) }"
                                                    >
                                                        <input
                                                            type="checkbox"
                                                            class="hidden-checkbox"
                                                            :value="cat.key"
                                                            v-model="local.labels"
                                                        >
                                                        <i :class="cat.icon" class="category-icon"></i>
                                                        <span class="category-name">{{ cat.name }}</span>
                                                        <i v-if="local.labels.includes(cat.key)" class="fa-solid fa-check category-check"></i>
                                                    </label>
                                                </div>
                                            </div>
                                        </Transition>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Кастомные поля для задачи -->
                        <CustomFieldsRenderer
                            v-if="taskSections.length > 0"
                            :sections="taskSections"
                            target="task"
                            v-model="local.custom_data"
                        />

                        <!-- СЕКЦИЯ: Подзадачи -->
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon subtasks-icon">
                                    <i class="fa-solid fa-list-check"></i>
                                </div>
                                <div>
                                    <h4 class="section-title">
                                        Подзадачи
                                        <span class="subtasks-counter">
                                            {{ completedSubtasks }} / {{ local.subtasks.length }}
                                        </span>
                                    </h4>
                                    <p class="section-desc">Разбейте задачу на мелкие шаги</p>
                                </div>
                            </div>

                            <!-- Добавление подзадачи -->
                            <div class="add-subtask-row">
                                <div class="input-wrapper flex-grow">
                                    <i class="fa-solid fa-plus input-icon"></i>
                                    <input
                                        type="text"
                                        class="custom-input"
                                        placeholder="Новая подзадача"
                                        v-model="newSubtask"
                                        @keyup.enter="addSubtask"
                                    >
                                </div>
                                <button
                                    type="button"
                                    class="btn-add-subtask"
                                    @click="addSubtask"
                                    :disabled="!newSubtask.trim()"
                                >
                                    <i class="fa-solid fa-plus"></i>
                                    <span>Добавить</span>
                                </button>
                            </div>

                            <!-- Список подзадач -->
                            <div v-if="local.subtasks.length > 0" class="subtasks-list" ref="subtasksList">
                                <div
                                    v-for="(sub, index) in local.subtasks"
                                    :key="sub.id"
                                    class="subtask-item"
                                    :class="{ completed: sub.done }"
                                >
                                    <span class="drag-handle" title="Перетащите для изменения порядка">
                                        <i class="fa-solid fa-grip-vertical"></i>
                                    </span>
                                    <label class="subtask-checkbox-wrapper">
                                        <input
                                            type="checkbox"
                                            class="subtask-checkbox"
                                            v-model="sub.done"
                                        >
                                        <span class="custom-checkmark">
                                            <i v-if="sub.done" class="fa-solid fa-check"></i>
                                        </span>
                                    </label>
                                    <span class="subtask-text">{{ sub.text }}</span>
                                    <button
                                        type="button"
                                        class="btn-remove-subtask"
                                        @click="removeSubtask(index)"
                                        title="Удалить подзадачу"
                                    >
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </div>
                            <div v-else class="empty-subtasks">
                                <i class="fa-regular fa-clipboard"></i>
                                <p>Пока нет подзадач</p>
                            </div>
                        </div>

                        <!-- СЕКЦИЯ: Вложения и комментарии (только для существующей задачи) -->
                        <template v-if="local.id">
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-icon attachments-icon">
                                        <i class="fa-solid fa-paperclip"></i>
                                    </div>
                                    <div>
                                        <h4 class="section-title">Вложения</h4>
                                        <p class="section-desc">Прикрепите файлы к задаче</p>
                                    </div>
                                </div>
                                <TaskAttachmentsUpload
                                    :taskId="local.id"
                                    @uploaded="(attachments) => local.attachments = attachments"
                                />
                                <TaskAttachmentsList :taskId="local.id" :showDelete="true"/>
                            </div>

                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-icon comments-icon">
                                        <i class="fa-solid fa-comments"></i>
                                    </div>
                                    <div>
                                        <h4 class="section-title">Комментарии</h4>
                                        <p class="section-desc">Обсудите задачу с командой</p>
                                    </div>
                                </div>
                                <CommentAddForm :taskId="local.id"/>
                                <CommentsList :taskId="local.id"/>
                            </div>
                        </template>

                        <!-- Карточки типов (если редактируем) -->
                        <template v-if="task">
                            <CardUser v-if="task.type === 1" :card="task"/>
                            <CardOrder v-if="task.type === 2" :card="task"/>
                            <CardText v-if="task.type === 3" :card="task"/>
                            <CardFinance v-if="task.type === 4" :card="task"/>
                            <CardDevelopment v-if="task.type === 5" :card="task"/>
                        </template>

                    </form>

                    <!-- TAB: ЧАТ -->
                    <template v-if="task">
                        <CardChat
                            :task-id="task.id"
                            v-show="tab === 'chat'"
                        ></CardChat>
                    </template>
                </div>

                <!-- FOOTER -->
                <div class="modal-footer-custom">
                    <button class="btn-footer btn-cancel" @click="close">
                        <i class="fa-solid fa-xmark me-2"></i>
                        Отмена
                    </button>
                    <button
                        class="btn-footer btn-submit"
                        :disabled="store.loading"
                        @click="submit"
                    >
                        <span v-if="store.loading" class="spinner"></span>
                        <template v-else>
                            <i class="fa-solid fa-check me-2"></i>
                            {{ task ? 'Сохранить изменения' : 'Создать задачу' }}
                        </template>
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script>
import { useKanbanStore } from '@/stores/useKanbanStore'
import Sortable from 'sortablejs'
import CardUser from './Cards/CardUser.vue'
import CardOrder from './Cards/CardOrder.vue'
import CardText from './Cards/CardText.vue'
import CardFinance from './Cards/CardFinance.vue'
import CardDevelopment from './Cards/CardDevelopment.vue'
import CommentsList from "@/Components/Kanban/Comments/CommentsList.vue"
import CommentAddForm from "@/Components/Kanban/Comments/CommentAddForm.vue"
import TaskAttachmentsList from "@/Components/Kanban/Tasks/TaskAttachmentsList.vue"
import TaskAttachmentsUpload from "@/Components/Kanban/Tasks/TaskAttachmentsUpload.vue"
import CardChat from "@/Components/Kanban/Cards/CardChat.vue"
import CustomFieldsRenderer from '@/Components/Kanban/Support/CustomFieldsRenderer.vue'

export default {
    components: {
        CardUser,
        CardOrder,
        CardText,
        CardFinance,
        CustomFieldsRenderer,
        CardDevelopment,
        CommentsList,
        CommentAddForm,
        TaskAttachmentsList,
        TaskAttachmentsUpload,
        CardChat
    },
    props: {
        task: Object,
        columnId: Number
    },
    emits: ['close', 'add-task', 'edit-task'],

    data() {
        const store = useKanbanStore()

        return {
            store,
            isVisible: false,
            tab: 'task',
            collapsedGroups: {}, // Состояние свёрнутости групп
            labelGroups: {
                'Разработка': [
                    'development', 'bug', 'enhancement',
                    'refactor', 'hotfix', 'release'
                ],
                'Качество': ['testing', 'review'],
                'Дизайн': ['design', 'ui', 'ux'],
                'Документация': ['documentation', 'research'],
                'Бизнес': [
                    'client', 'finance', 'marketing',
                    'sales', 'analytics'
                ],
                'Поддержка': ['support', 'urgent'],
                'Инфраструктура': [
                    'devops', 'security', 'infrastructure'
                ],
                'Процессы': ['planning', 'meeting', 'training'],
                'Другое': ['legal', 'hr', 'logistics', 'production']
            },
            local: this.task
                ? {
                    ...this.task,
                    tag_ids: this.task.tags?.map(t => t.id) ?? [],
                    subtasks: this.task.subtasks ?? [],
                    custom_data: this.task.custom_data || {}
                }
                : {
                    title: '',
                    description: '',
                    priority: 'low',
                    due_date: '',
                    tag_ids: [],
                    labels: ['development'],
                    column_id: this.columnId,
                    subtasks: [],
                    custom_data: {}
                },
            newSubtask: '',
            newTagName: '',
            newTagColor: '#999999'
        }
    },

    computed: {
        allCategories() {
            // Базовые категории + кастомные из конфига доски
            const baseCategories = Object.values(this.labelGroups).flat()
            const customCategories = (this.store.board?.config?.custom_categories || [])
                .map(c => c.key)
            return [...new Set([...baseCategories, ...customCategories])]
        },
        customCategories() {
            return this.store.board?.config?.custom_categories || []
        },
        completedSubtasks() {
            return this.local.subtasks.filter(s => s.done).length
        },
        taskSections() {
            const customFields = this.store.board?.config?.custom_fields || []
            return customFields.filter(section => section.target === 'task')
        }
    },

    mounted() {
        this.$nextTick(() => {
            this.isVisible = true
            this.initSortable()
            this.initCollapsedGroups() // ← Инициализация свёрнутости
        })
    },


    methods: {
        initCollapsedGroups() {
            // Сворачиваем все группы, кроме тех, где есть выбранные категории
            const groups = { ...this.labelGroups }

            Object.keys(groups).forEach(groupName => {
                const hasSelected = groups[groupName].some(label =>
                    this.local.labels.includes(label)
                )
                // Если есть выбранные — разворачиваем, иначе сворачиваем
                this.collapsedGroups[groupName] = !hasSelected
            })

            // Кастомные категории
            if (this.customCategories.length > 0) {
                const hasSelected = this.customCategories.some(cat =>
                    this.local.labels.includes(cat.key)
                )
                this.collapsedGroups['custom'] = !hasSelected
            }
        },

        isGroupCollapsed(groupName) {
            return this.collapsedGroups[groupName] ?? true
        },

        toggleGroup(groupName) {
            this.collapsedGroups[groupName] = !this.isGroupCollapsed(groupName)
        },


        toggleGroup(groupName) {
            this.collapsedGroups[groupName] = !this.isGroupCollapsed(groupName)
        },

        getSelectedCount(labels) {
            return labels.filter(label => this.local.labels.includes(label)).length
        },

        isAllSelected(labels) {
            return labels.every(label => this.local.labels.includes(label))
        },

        toggleSelectAll(labels) {
            if (this.isAllSelected(labels)) {
                // Снимаем все
                this.local.labels = this.local.labels.filter(l => !labels.includes(l))
            } else {
                // Выбираем все
                const newLabels = [...new Set([...this.local.labels, ...labels])]
                this.local.labels = newLabels
            }
        },

        removeLabel(label) {
            this.local.labels = this.local.labels.filter(l => l !== label)
        },
        close() {
            this.isVisible = false
            document.body.style.overflow = ''
            this.$emit('close')
        },

        handleOverlayClick() {
            this.close()
        },

        getCategoryIcon(label) {
            const icons = {
                // Разработка
                development: 'fa-solid fa-code',
                bug: 'fa-solid fa-bug',
                enhancement: 'fa-solid fa-wand-magic-sparkles',
                refactor: 'fa-solid fa-recycle',
                hotfix: 'fa-solid fa-fire-flame-curved',
                release: 'fa-solid fa-rocket',

                // Тестирование и качество
                testing: 'fa-solid fa-vial',
                review: 'fa-solid fa-magnifying-glass',

                // Дизайн
                design: 'fa-solid fa-palette',
                ui: 'fa-solid fa-display',
                ux: 'fa-solid fa-user-pen',

                // Документация
                documentation: 'fa-solid fa-book',
                research: 'fa-solid fa-microscope',

                // Бизнес
                client: 'fa-solid fa-user-tie',
                finance: 'fa-solid fa-money-bill',
                marketing: 'fa-solid fa-bullhorn',
                sales: 'fa-solid fa-chart-line',
                analytics: 'fa-solid fa-chart-pie',

                // Поддержка
                support: 'fa-solid fa-headset',
                urgent: 'fa-solid fa-fire',

                // Инфраструктура
                devops: 'fa-solid fa-server',
                security: 'fa-solid fa-shield-halved',
                infrastructure: 'fa-solid fa-network-wired',

                // Процессы
                planning: 'fa-solid fa-calendar-days',
                meeting: 'fa-solid fa-users-rectangle',
                training: 'fa-solid fa-graduation-cap',

                // Юридические и HR
                legal: 'fa-solid fa-gavel',
                hr: 'fa-solid fa-user-plus',

                // Логистика
                logistics: 'fa-solid fa-truck',
                production: 'fa-solid fa-industry'
            }
            return icons[label] || 'fa-solid fa-tag'
        },

        getCategoryLabel(label) {
            const labels = {
                // Разработка
                development: 'Разработка',
                bug: 'Баг',
                enhancement: 'Улучшение',
                refactor: 'Рефакторинг',
                hotfix: 'Хотфикс',
                release: 'Релиз',

                // Тестирование и качество
                testing: 'Тестирование',
                review: 'Ревью',

                // Дизайн
                design: 'Дизайн',
                ui: 'UI',
                ux: 'UX',

                // Документация
                documentation: 'Документация',
                research: 'Исследование',

                // Бизнес
                client: 'Клиент',
                finance: 'Финансы',
                marketing: 'Маркетинг',
                sales: 'Продажи',
                analytics: 'Аналитика',

                // Поддержка
                support: 'Поддержка',
                urgent: 'Срочно',

                // Инфраструктура
                devops: 'DevOps',
                security: 'Безопасность',
                infrastructure: 'Инфраструктура',

                // Процессы
                planning: 'Планирование',
                meeting: 'Встреча',
                training: 'Обучение',

                // Юридические и HR
                legal: 'Юридическое',
                hr: 'Кадры',

                // Логистика
                logistics: 'Логистика',
                production: 'Производство'
            }
            return labels[label] || label
        },

        initSortable() {
            if (!this.$refs.subtasksList) return

            Sortable.create(this.$refs.subtasksList, {
                animation: 150,
                handle: '.drag-handle',
                onEnd: (evt) => {
                    const moved = this.local.subtasks.splice(evt.oldIndex, 1)[0]
                    this.local.subtasks.splice(evt.newIndex, 0, moved)
                }
            })
        },

        addSubtask() {
            if (!this.newSubtask.trim()) return

            this.local.subtasks.push({
                id: Date.now(),
                text: this.newSubtask.trim(),
                done: false
            })

            this.newSubtask = ''
        },

        removeSubtask(index) {
            this.local.subtasks.splice(index, 1)
        },

        async addTag() {
            if (!this.newTagName.trim()) return

            const tag = await this.store.createTag(
                this.store.board.uuid,
                this.newTagName,
                this.newTagColor
            )

            this.local.tag_ids.push(tag.id)
            this.newTagName = ''
            this.newTagColor = '#999999'
        },

        async deleteTag(tag) {
            const isUsedInCurrentTask = this.local.tag_ids.includes(tag.id)

            let confirmMessage = `Удалить тег "${tag.name}"?`
            if (isUsedInCurrentTask) {
                confirmMessage += `\n\nТег используется в текущей задаче и будет удалён из неё.`
            }

            if (!confirm(confirmMessage)) return

            try {
                if (isUsedInCurrentTask) {
                    this.local.tag_ids = this.local.tag_ids.filter(id => id !== tag.id)
                }

                await this.store.deleteTag(tag.id)

                this.showToast('Тег успешно удалён', 'success')
            } catch (error) {
                console.error('Ошибка удаления тега:', error)
                this.showToast('Не удалось удалить тег', 'error')
            }
        },

        showToast(message, type = 'success') {
            const toast = document.createElement('div')
            toast.className = `toast-notification toast-${type}`
            toast.textContent = message
            document.body.appendChild(toast)

            setTimeout(() => toast.classList.add('show'), 10)
            setTimeout(() => {
                toast.classList.remove('show')
                setTimeout(() => toast.remove(), 300)
            }, 3000)
        },

        async submit() {
            this.local.column_id = this.columnId
            this.local.tag_ids = [...this.local.tag_ids]

            if (!this.local.id) {
                await this.store.createTask(this.store.board.uuid, this.local)
                this.$emit('add-task', this.local)
            } else {
                await this.store.updateTask(this.local)
                this.$emit('edit-task', this.local)
            }

            this.close()
        }
    }
}
</script>

<style scoped>
/* === OVERLAY === */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    padding: 20px;
}

/* === МОДАЛЬНОЕ ОКНО === */
.modal-window {
    background: #ffffff;
    border-radius: 20px;
    width: 800px;
    max-width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
    animation: modalSlideUp 0.3s ease;
}

@keyframes modalSlideUp {
    from {
        opacity: 0;
        transform: translateY(30px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* === HEADER === */
.modal-header-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 24px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-radius: 20px 20px 0 0;
    color: white;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 16px;
}

.header-icon {
    width: 56px;
    height: 56px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    backdrop-filter: blur(10px);
}

.header-text {
    flex: 1;
}

.modal-title-text {
    font-size: 22px;
    font-weight: 700;
    margin: 0 0 4px 0;
    color: white;
}

.modal-subtitle {
    font-size: 13px;
    margin: 0;
    opacity: 0.9;
}

.close-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: white;
    font-size: 18px;
    transition: all 0.2s;
}

.close-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: rotate(90deg);
}

/* === TABS === */
.modal-tabs {
    display: flex;
    gap: 4px;
    padding: 16px 28px 0;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.tab-btn {
    padding: 10px 20px;
    border: none;
    background: transparent;
    border-radius: 10px 10px 0 0;
    font-size: 14px;
    font-weight: 600;
    color: #6c757d;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
}

.tab-btn:hover {
    background: #e9ecef;
    color: #495057;
}

.tab-btn.active {
    background: #ffffff;
    color: #0d6efd;
    box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05);
}

/* === BODY === */
.modal-body-custom {
    padding: 28px;
    overflow-y: auto;
    flex: 1;
}

.task-form {
    display: flex;
    flex-direction: column;
    gap: 0;
}

/* === СЕКЦИИ ФОРМЫ === */
.form-section {
    margin-bottom: 32px;
}

.form-section:last-child {
    margin-bottom: 0;
}

.section-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 2px solid #f1f3f5;
}

.section-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
    color: white;
}

.main-icon {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

.params-icon {
    background: linear-gradient(135deg, #ffc107 0%, #ff9800 100%);
    box-shadow: 0 4px 12px rgba(255, 193, 7, 0.3);
}

.tags-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.subtasks-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.attachments-icon {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
}

.comments-icon {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
}

.section-title {
    font-size: 16px;
    font-weight: 700;
    color: #212529;
    margin: 0 0 2px 0;
    display: flex;
    align-items: center;
    gap: 8px;
}

.subtasks-counter {
    background: #e9ecef;
    color: #495057;
    padding: 2px 10px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
}

.section-desc {
    font-size: 12px;
    color: #6c757d;
    margin: 0;
}

/* === ФОРМЫ === */
.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-label-custom {
    font-size: 13px;
    font-weight: 600;
    color: #495057;
}

.required {
    color: #dc3545;
    margin-left: 2px;
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.input-wrapper.flex-grow {
    flex: 1;
}

.input-icon {
    position: absolute;
    left: 14px;
    color: #adb5bd;
    font-size: 14px;
    pointer-events: none;
    z-index: 1;
}

.custom-input {
    width: 100%;
    padding: 10px 14px 10px 42px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 14px;
    outline: none;
    transition: all 0.2s;
    background: #ffffff;
}

.custom-input:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
}

.custom-input::placeholder {
    color: #adb5bd;
}

.textarea-wrapper {
    position: relative;
}

.textarea-icon {
    position: absolute;
    left: 14px;
    top: 14px;
    color: #adb5bd;
    font-size: 14px;
    pointer-events: none;
    z-index: 1;
}

.custom-textarea {
    width: 100%;
    padding: 12px 14px 12px 42px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 14px;
    outline: none;
    transition: all 0.2s;
    resize: vertical;
    font-family: inherit;
}

.custom-textarea:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
}

/* === SELECT === */
.custom-select-wrapper {
    position: relative;
}

.custom-select {
    width: 100%;
    padding: 10px 40px 10px 14px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 14px;
    outline: none;
    transition: all 0.2s;
    background: #ffffff;
    cursor: pointer;
    appearance: none;
}

.custom-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
}

.select-icon {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: #adb5bd;
    font-size: 12px;
    pointer-events: none;
}

/* === ТЕГИ === */
.tags-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.tag-checkbox-wrapper {
    position: relative;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}

.tag-checkbox {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 6px 12px;
    border: 2px solid;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 13px;
    position: relative;
}

.tag-checkbox:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.tag-checkbox.selected {
    font-weight: 600;
}

.hidden-checkbox {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.tag-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.tag-name {
    color: #212529;
}

.tag-check {
    font-size: 10px;
    color: #10b981;
}

/* === КНОПКА УДАЛЕНИЯ ТЕГА === */
.btn-delete-tag {
    width: 28px;
    height: 28px;
    border: none;
    background: #fff5f5;
    color: #dc3545;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 11px;
    opacity: 0.2;
    flex-shrink: 0;
}

.tag-checkbox-wrapper:hover .btn-delete-tag {
    opacity: 1;
}

.btn-delete-tag:hover {
    background: #dc3545;
    color: white;
    transform: scale(1.1);
}

.btn-delete-tag:active {
    transform: scale(0.95);
}

/* === НОВЫЙ ТЕГ === */
.new-tag-row {
    display: flex;
    gap: 8px;
    align-items: center;
}

.color-picker-wrapper {
    width: 44px;
    height: 44px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    overflow: hidden;
    cursor: pointer;
}

.color-picker {
    width: 100%;
    height: 100%;
    border: none;
    cursor: pointer;
}

.btn-add-tag {
    width: 44px;
    height: 44px;
    border: none;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-add-tag:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.btn-add-tag:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* === КАТЕГОРИИ === */
.categories-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.category-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
    background: #ffffff;
    position: relative;
}

.category-badge:hover {
    border-color: #0d6efd;
    background: #f8f9fa;
}

.category-badge.selected {
    border-color: #0d6efd;
    background: #e7f1ff;
    color: #0d6efd;
    font-weight: 600;
}

.category-icon {
    font-size: 14px;
}

.category-name {
    font-size: 13px;
}

.category-check {
    font-size: 10px;
    color: #0d6efd;
}

/* === ПОДЗАДАЧИ === */
.add-subtask-row {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
}

.btn-add-subtask {
    padding: 10px 20px;
    border: none;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 13px;
    font-weight: 600;
    white-space: nowrap;
}

.btn-add-subtask:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

.btn-add-subtask:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.subtasks-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.subtask-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    transition: all 0.2s;
}

.subtask-item:hover {
    background: #ffffff;
    border-color: #dee2e6;
}

.subtask-item.completed {
    background: #d1e7dd;
    border-color: #a3cfbb;
}

.drag-handle {
    cursor: grab;
    color: #adb5bd;
    padding: 4px;
    transition: color 0.2s;
}

.drag-handle:hover {
    color: #495057;
}

.drag-handle:active {
    cursor: grabbing;
}

.subtask-checkbox-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.subtask-checkbox {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.custom-checkmark {
    width: 20px;
    height: 20px;
    border: 2px solid #dee2e6;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    background: #ffffff;
}

.subtask-checkbox:checked + .custom-checkmark {
    background: #10b981;
    border-color: #10b981;
    color: white;
}

.custom-checkmark i {
    font-size: 11px;
}

.subtask-text {
    flex: 1;
    font-size: 14px;
    color: #212529;
}

.subtask-item.completed .subtask-text {
    text-decoration: line-through;
    color: #6c757d;
}

.btn-remove-subtask {
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    color: #dc3545;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0.6;
}

.btn-remove-subtask:hover {
    background: #fff5f5;
    opacity: 1;
}

.empty-subtasks {
    text-align: center;
    padding: 32px 20px;
    color: #adb5bd;
}

.empty-subtasks i {
    font-size: 36px;
    margin-bottom: 8px;
    opacity: 0.5;
}

.empty-subtasks p {
    font-size: 13px;
    margin: 0;
}

/* === FOOTER === */
.modal-footer-custom {
    padding: 20px 28px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    background: #f8f9fa;
    border-radius: 0 0 20px 20px;
}

.btn-footer {
    padding: 10px 24px;
    border: none;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-cancel {
    background: #ffffff;
    color: #6c757d;
    border: 1px solid #dee2e6;
}

.btn-cancel:hover {
    background: #f8f9fa;
    color: #495057;
    border-color: #adb5bd;
}

.btn-submit {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
}

.btn-submit:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
}

.btn-submit:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

/* === SPINNER === */
.spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* === TOAST УВЕДОМЛЕНИЯ === */
:global(.toast-notification) {
    position: fixed;
    bottom: 24px;
    right: 24px;
    padding: 14px 20px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 500;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    display: flex;
    align-items: center;
    gap: 10px;
    z-index: 9999;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s ease;
}

:global(.toast-notification.show) {
    opacity: 1;
    transform: translateY(0);
}

:global(.toast-success) {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
}

:global(.toast-error) {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
}

/* === АНИМАЦИИ === */
.modal-fade-enter-active,
.modal-fade-leave-active {
    transition: opacity 0.3s ease;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
    opacity: 0;
}

/* === АДАПТИВ === */
@media (max-width: 768px) {
    .modal-window {
        width: 100%;
        max-height: 95vh;
        border-radius: 16px;
    }

    .modal-header-custom {
        padding: 20px;
        border-radius: 16px 16px 0 0;
    }

    .header-icon {
        width: 48px;
        height: 48px;
        font-size: 20px;
    }

    .modal-title-text {
        font-size: 18px;
    }

    .modal-body-custom {
        padding: 20px;
    }

    .form-grid {
        grid-template-columns: 1fr;
    }

    .modal-footer-custom {
        padding: 16px 20px;
        border-radius: 0 0 16px 16px;
    }

    .btn-footer {
        flex: 1;
    }

    .new-tag-row {
        flex-wrap: wrap;
    }

    .add-subtask-row {
        flex-direction: column;
    }

    .btn-add-subtask {
        width: 100%;
        justify-content: center;
    }
}

/* === СКРОЛЛБАР === */
.modal-body-custom::-webkit-scrollbar {
    width: 8px;
}

.modal-body-custom::-webkit-scrollbar-track {
    background: #f1f3f5;
    border-radius: 4px;
}

.modal-body-custom::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 4px;
}

.modal-body-custom::-webkit-scrollbar-thumb:hover {
    background: #adb5bd;
}

/* === ГРУППЫ КАТЕГОРИЙ === */
.categories-groups {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.category-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.group-title {
    font-size: 11px;
    font-weight: 700;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding-left: 4px;
}

.categories-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.category-badge {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
    background: #ffffff;
    position: relative;
}

.category-badge:hover {
    border-color: #0d6efd;
    background: #f8f9fa;
}

.category-badge.selected {
    border-color: #0d6efd;
    background: #e7f1ff;
    color: #0d6efd;
    font-weight: 600;
}

.category-icon {
    font-size: 14px;
}

.category-name {
    font-size: 13px;
}

.category-check {
    font-size: 10px;
    color: #0d6efd;
}

/* === СВОДКА ВЫБРАННЫХ КАТЕГОРИЙ === */
.selected-categories-summary {
    background: #f0f7ff;
    border: 1px solid #cfe2ff;
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 12px;
}

.summary-label {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #0d6efd;
    margin-bottom: 8px;
}

.summary-label i {
    font-size: 11px;
}

.summary-tags {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.summary-tag {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 8px 4px 10px;
    background: #ffffff;
    border: 1px solid #cfe2ff;
    border-radius: 6px;
    font-size: 11px;
    font-weight: 500;
    color: #0d6efd;
}

.summary-tag i {
    font-size: 10px;
}

.remove-tag-btn {
    width: 16px;
    height: 16px;
    border: none;
    background: transparent;
    color: #6c757d;
    border-radius: 4px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 9px;
    transition: all 0.15s;
    margin-left: 2px;
}

.remove-tag-btn:hover {
    background: #dc3545;
    color: white;
}

/* === ГРУППЫ КАТЕГОРИЙ === */
.categories-groups {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.category-group {
    border: 2px solid #e9ecef;
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.2s;
}

.category-group:hover {
    border-color: #dee2e6;
}

.category-group.collapsed {
    background: #fafafa;
}

/* Заголовок группы */
.group-header {
    width: 100%;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 10px 14px;
    background: #f8f9fa;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
    text-align: left;
}

.group-header:hover {
    background: #f1f3f5;
}

.group-header-left {
    display: flex;
    align-items: center;
    gap: 8px;
    flex: 1;
    min-width: 0;
}

.group-chevron {
    font-size: 10px;
    color: #6c757d;
    transition: transform 0.25s ease;
    flex-shrink: 0;
}

.group-chevron.rotated {
    transform: rotate(-90deg);
}

.group-title {
    font-size: 13px;
    font-weight: 600;
    color: #212529;
    display: flex;
    align-items: center;
    gap: 6px;
}

.group-title i {
    font-size: 11px;
    color: #8b5cf6;
}

.group-count {
    font-size: 10px;
    font-weight: 600;
    color: #6c757d;
    background: #e9ecef;
    padding: 2px 7px;
    border-radius: 8px;
}

.group-header-right {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-shrink: 0;
}

.group-selected-badge {
    font-size: 10px;
    font-weight: 600;
    color: #0d6efd;
    background: #e7f1ff;
    padding: 2px 8px;
    border-radius: 8px;
}

.btn-select-all {
    width: 26px;
    height: 26px;
    border: none;
    background: transparent;
    color: #6c757d;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
}

.btn-select-all:hover {
    background: #e7f1ff;
    color: #0d6efd;
}

/* Содержимое группы */
.group-content {
    padding: 12px 14px;
    background: #ffffff;
    border-top: 1px solid #e9ecef;
}

.categories-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

/* === АНИМАЦИЯ СВОРАЧИВАНИЯ === */
.group-collapse-enter-active,
.group-collapse-leave-active {
    transition: all 0.25s ease;
    max-height: 500px;
    overflow: hidden;
}

.group-collapse-enter-from,
.group-collapse-leave-to {
    max-height: 0;
    opacity: 0;
    padding-top: 0;
    padding-bottom: 0;
}

/* === АДАПТИВ === */
@media (max-width: 768px) {
    .group-header {
        padding: 10px 12px;
    }

    .group-content {
        padding: 10px 12px;
    }

    .group-selected-badge {
        display: none;
    }

    .summary-tags {
        max-height: 80px;
        overflow-y: auto;
    }
}
</style>

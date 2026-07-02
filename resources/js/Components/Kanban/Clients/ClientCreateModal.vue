<template>
    <Transition name="modal-fade">
        <div v-if="isVisible" class="modal-overlay" @click.self="handleOverlayClick">
            <div class="modal-window">
                <!-- HEADER -->
                <div class="modal-header-custom">
                    <div class="header-content">
                        <div class="header-icon">
                            <i :class="isEdit ? 'fa-solid fa-pen-to-square' : 'fa-solid fa-user-tie'"></i>
                        </div>
                        <div class="header-text">
                            <h3 class="modal-title-text">
                                {{ isEdit ? 'Редактировать клиента' : 'Новый клиент' }}
                            </h3>
                            <p class="modal-subtitle">
                                {{ isEdit
                                ? (client?.company_name || 'Карточка клиента')
                                : 'Заполните данные для создания карточки клиента'
                                }}
                            </p>
                        </div>
                    </div>
                    <div class="header-actions">
                        <!-- Кнопка экспорта (только в режиме редактирования) -->
                        <button
                            v-if="isEdit && client"
                            class="export-btn"
                            @click="exportClient"
                            title="Экспорт в Excel"
                        >
                            <i class="fa-solid fa-file-excel"></i>
                            <span class="btn-text">Excel</span>
                        </button>
                        <button class="close-btn" @click="close" title="Закрыть">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </div>

                <!-- TABS (только в режиме редактирования) -->
                <div v-if="isEdit" class="modal-tabs">
                    <button
                        class="tab-btn"
                        :class="{ active: activeTab === 'form' }"
                        @click="activeTab = 'form'"
                    >
                        <i class="fa-solid fa-pen-to-square me-2"></i>
                        Данные клиента
                    </button>
                    <button
                        class="tab-btn"
                        :class="{ active: activeTab === 'history' }"
                        @click="activeTab = 'history'"
                    >
                        <i class="fa-solid fa-clock-rotate-left me-2"></i>
                        История
                        <span v-if="activitiesCount > 0" class="tab-badge">
                            {{ activitiesCount }}
                        </span>
                    </button>
                </div>

                <!-- BODY -->
                <div class="modal-body-custom">

                    <!-- TAB: ФОРМА -->
                    <div v-show="!isEdit || activeTab === 'form'">
                        <form @submit.prevent="submit">

                            <!-- СЕКЦИЯ: Этап воронки -->
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-icon stage-icon">
                                        <i class="fa-solid fa-layer-group"></i>
                                    </div>
                                    <div>
                                        <h4 class="section-title">Этап воронки</h4>
                                        <p class="section-desc">В какую колонку попадёт клиент</p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label-custom">
                                        Выберите этап <span class="required">*</span>
                                    </label>
                                    <div class="custom-select-wrapper">
                                        <select v-model="form.column_id" class="custom-select" required>
                                            <option :value="null" disabled>— Выберите этап —</option>
                                            <option
                                                v-for="col in columns"
                                                :key="col.id"
                                                :value="col.id"
                                            >
                                                {{ col.title }}
                                            </option>
                                        </select>
                                        <i class="fa-solid fa-chevron-down select-icon"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- СЕКЦИЯ: Основная информация -->
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-icon info-icon">
                                        <i class="fa-solid fa-building"></i>
                                    </div>
                                    <div>
                                        <h4 class="section-title">Основная информация</h4>
                                        <p class="section-desc">Данные о компании и контактах</p>
                                    </div>
                                </div>

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label-custom">Название предприятия</label>
                                        <div class="input-wrapper">
                                            <i class="fa-solid fa-building input-icon"></i>
                                            <input v-model="form.client.company_name" type="text" class="custom-input" placeholder="ООО Ромашка">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label-custom">Контактное лицо</label>
                                        <div class="input-wrapper">
                                            <i class="fa-solid fa-user input-icon"></i>
                                            <input v-model="form.client.contact_person" type="text" class="custom-input" placeholder="Иванов Иван">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label-custom">Телефон</label>
                                        <div class="input-wrapper">
                                            <i class="fa-solid fa-phone input-icon"></i>
                                            <input v-model="form.client.phone" type="text" class="custom-input" placeholder="+7 999 000-00-00">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label-custom">Источник лида</label>
                                        <div class="autocomplete-wrapper" ref="sourceAutocomplete">
                                            <div class="input-wrapper">
                                                <i class="fa-solid fa-bullseye input-icon"></i>
                                                <input
                                                    v-model="form.client.source"
                                                    type="text"
                                                    class="custom-input"
                                                    placeholder="Начните вводить или выберите"
                                                    @focus="showDropdown = true"
                                                    @input="onSourceInput"
                                                    @keydown.down="navigateDown"
                                                    @keydown.up="navigateUp"
                                                    @keydown.enter="selectHighlighted"
                                                    @keydown.esc="showDropdown = false"
                                                >
                                                <button v-if="form.client.source" type="button" class="clear-btn" @click="form.client.source = ''" title="Очистить">
                                                    <i class="fa-solid fa-xmark"></i>
                                                </button>
                                            </div>

                                            <Transition name="dropdown-fade">
                                                <div v-if="showDropdown && filteredSources.length > 0" class="autocomplete-dropdown">
                                                    <div
                                                        v-for="(source, index) in filteredSources"
                                                        :key="source"
                                                        class="autocomplete-item"
                                                        :class="{ highlighted: index === highlightedIndex }"
                                                        @click="selectSource(source)"
                                                        @mouseenter="highlightedIndex = index"
                                                    >
                                                        <i class="fa-solid fa-circle-dot item-icon"></i>
                                                        <span class="item-text">{{ source }}</span>
                                                        <i v-if="form.client.source === source" class="fa-solid fa-check item-check"></i>
                                                    </div>
                                                </div>
                                            </Transition>
                                        </div>
                                        <div class="input-hint">
                                            <i class="fa-solid fa-circle-info me-1"></i>
                                            Выберите из списка или введите новое значение
                                        </div>
                                    </div>

                                    <div class="form-group full-width">
                                        <label class="form-label-custom">Адрес</label>
                                        <div class="input-wrapper">
                                            <i class="fa-solid fa-location-dot input-icon"></i>
                                            <input v-model="form.client.address" type="text" class="custom-input" placeholder="г. Москва, ул. Примерная, д. 1">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- СЕКЦИЯ: Сделка -->
                            <!-- Замени секцию "Сделка" на эту: -->

                            <!-- СЕКЦИЯ: Сделка -->
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-icon deal-icon">
                                        <i class="fa-solid fa-handshake"></i>
                                    </div>
                                    <div>
                                        <h4 class="section-title">Сделка</h4>
                                        <p class="section-desc">Условия оказания услуг</p>
                                    </div>
                                </div>

                                <div class="form-grid">
                                    <div class="form-group">
                                        <label class="form-label-custom">Вид услуги</label>
                                        <div class="input-wrapper">
                                            <i class="fa-solid fa-box input-icon"></i>
                                            <input
                                                v-model="form.client.placement_type"
                                                type="text"
                                                class="custom-input"
                                                placeholder="Стандарт, Премиум..."
                                            >
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="form-label-custom">Стоимость услуги (₽)</label>
                                        <div class="input-wrapper">
                                            <i class="fa-solid fa-ruble-sign input-icon"></i>
                                            <input
                                                v-model="form.client.cost"
                                                type="number"
                                                step="0.01"
                                                class="custom-input"
                                                placeholder="0.00"
                                            >
                                        </div>
                                    </div>

                                    <div class="form-group full-width">
                                        <label class="form-label-custom">Комментарий к сделке</label>
                                        <textarea
                                            v-model="form.client.deal_comment"
                                            class="custom-textarea"
                                            rows="3"
                                            placeholder="Дополнительная информация о сделке..."
                                        ></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- СЕКЦИЯ: Ссылки (отдельная) -->
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-icon links-icon">
                                        <i class="fa-solid fa-link"></i>
                                    </div>
                                    <div>
                                        <h4 class="section-title">
                                            Ссылки
                                            <span v-if="links.length > 0" class="subtasks-counter">
                    {{ links.length }}
                </span>
                                        </h4>
                                        <p class="section-desc">Добавьте ссылки на ресурсы клиента</p>
                                    </div>
                                </div>

                                <!-- Добавление ссылки -->
                                <div class="add-link-row">
                                    <div class="input-wrapper flex-grow">
                                        <i class="fa-solid fa-link input-icon"></i>
                                        <input
                                            type="text"
                                            class="custom-input"
                                            placeholder="https://..."
                                            v-model="newLinkUrl"
                                            @keyup.enter="addLink"
                                        >
                                    </div>
                                    <div class="input-wrapper" style="width: 200px;">
                                        <i class="fa-solid fa-tag input-icon"></i>
                                        <input
                                            type="text"
                                            class="custom-input"
                                            placeholder="Название (необязательно)"
                                            v-model="newLinkTitle"
                                            @keyup.enter="addLink"
                                        >
                                    </div>
                                    <button
                                        type="button"
                                        class="btn-add-link"
                                        @click="addLink"
                                        :disabled="!newLinkUrl.trim()"
                                    >
                                        <i class="fa-solid fa-plus"></i>
                                        <span>Добавить</span>
                                    </button>
                                </div>

                                <!-- Список ссылок -->
                                <div v-if="links.length > 0" class="links-list">
                                    <div
                                        v-for="(link, index) in links"
                                        :key="index"
                                        class="link-item"
                                    >
                                        <i class="fa-solid fa-globe link-icon"></i>
                                        <div class="link-content">
                                            <div class="link-title">{{ link.title || link.url }}</div>
                                            <div class="link-url">{{ link.url }}</div>
                                        </div>
                                        <a
                                            v-if="link.url"
                                            :href="link.url"
                                            target="_blank"
                                            class="link-action-btn"
                                            title="Открыть"
                                        >
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </a>
                                        <button
                                            type="button"
                                            class="link-action-btn link-action-remove"
                                            @click="removeLink(index)"
                                            title="Удалить"
                                        >
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>
                                <div v-else class="empty-links">
                                    <i class="fa-solid fa-link"></i>
                                    <p>Пока нет ссылок. Добавьте первую!</p>
                                </div>
                            </div>

                            <!-- СЕКЦИЯ: Партнёр (отдельная, внизу) -->
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-icon partner-icon">
                                        <i class="fa-solid fa-user-group"></i>
                                    </div>
                                    <div>
                                        <h4 class="section-title">Партнёр / реферал</h4>
                                        <p class="section-desc">Информация о партнёре, приведшем клиента</p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="form-label-custom">Имя партнёра</label>
                                    <div class="input-wrapper">
                                        <i class="fa-solid fa-user-tie input-icon"></i>
                                        <input
                                            v-model="form.client.partner"
                                            type="text"
                                            class="custom-input"
                                            placeholder="Имя партнёра или реферала"
                                        >
                                    </div>
                                </div>
                            </div>

                            <!-- СЕКЦИЯ: Подзадачи -->
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-icon subtasks-icon">
                                        <i class="fa-solid fa-list-check"></i>
                                    </div>
                                    <div>
                                        <h4 class="section-title">
                                            Подзадачи
                                            <span v-if="form.subtasks.length > 0" class="subtasks-counter">
                                                {{ completedSubtasks }} / {{ form.subtasks.length }}
                                            </span>
                                        </h4>
                                        <p class="section-desc">Разбейте работу с клиентом на этапы</p>
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
                                            v-model="newSubtaskText"
                                            @keyup.enter="addSubtask"
                                        >
                                    </div>
                                    <button
                                        type="button"
                                        class="btn-add-subtask"
                                        @click="addSubtask"
                                        :disabled="!newSubtaskText.trim()"
                                    >
                                        <i class="fa-solid fa-plus"></i>
                                        <span>Добавить</span>
                                    </button>
                                </div>

                                <!-- Список подзадач -->
                                <div v-if="form.subtasks.length > 0" class="subtasks-list" ref="subtasksList">
                                    <div
                                        v-for="(sub, index) in form.subtasks"
                                        :key="sub.id || index"
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
                                    <p>Пока нет подзадач. Добавьте первую!</p>
                                </div>

                                <!-- Кастомные поля для клиента -->
                                <CustomFieldsRenderer
                                    v-if="clientSections.length > 0"
                                    :sections="clientSections"
                                    target="client"
                                    v-model="form.client.custom_data"
                                />
                            </div>

                        </form>
                    </div>

                    <!-- TAB: ИСТОРИЯ -->
                    <div v-if="isEdit && activeTab === 'history'">
                        <ClientTimeline
                            :client-id="client.id"
                            @loaded="onTimelineLoaded"
                        />
                    </div>

                </div>

                <!-- FOOTER -->
                <div class="modal-footer-custom">
                    <button class="btn-footer btn-cancel" @click="close">
                        <i class="fa-solid fa-xmark me-2"></i>
                        Отмена
                    </button>
                    <button
                        class="btn-footer btn-submit"
                        :disabled="!form.column_id || loading"
                        @click="submit"
                    >
                        <span v-if="loading" class="spinner"></span>
                        <template v-else>
                            <i :class="isEdit ? 'fa-solid fa-check' : 'fa-solid fa-plus'"></i>
                            <span class="ms-2">{{ isEdit ? 'Сохранить изменения' : 'Создать клиента' }}</span>
                        </template>
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script>
import { useKanbanStore } from '@/stores/kanban/useKanbanStore.js'
import ClientTimeline from '@/Components/Kanban/Clients/ClientTimeline.vue'
import CustomFieldsRenderer from '@/Components/Kanban/Support/CustomFieldsRenderer.vue'
import Sortable from 'sortablejs'

export default {
    components: {
        ClientTimeline,CustomFieldsRenderer
    },
    props: {
        boardUuid: { type: String, required: true },
        columns: { type: Array, required: true },
        prefilledColumnId: { type: Number, default: null },
        isEdit: { type: Boolean, default: false },
        client: { type: Object, default: null },
        task: { type: Object, default: null }
    },
    emits: ['close', 'created', 'updated'],

    setup() {
        return { store: useKanbanStore() }
    },

    data() {
        return {
            isVisible: false,
            loading: false,

            showDropdown: false,
            highlightedIndex: -1,
            activeTab: 'form',
            activitiesCount: 0,
            newSubtaskText: '',

            newLinkUrl: '',
            newLinkTitle: '',
            links: [], // ← Массив ссылок вместо строки
            linksString: '', // Оставляем для обратной совместимости

            form: {
                title: '',
                column_id: null,
                type: 2,
                priority: 'low',
                description: '',
                due_date: null,
                tag_ids: [],
                labels: [],
                subtasks: [],
                client: {
                    company_name: '',
                    contact_person: '',
                    phone: '',
                    source: '',
                    address: '',
                    placement_type: '', // Вид услуги
                    cost: null,         // Стоимость услуги
                    partner: '',
                    deal_comment: '',
                    links: [],
                    custom_data: {}
                }
            }
        }
    },

    computed: {
        filteredSources() {
            if (!this.form.client.source) {
                return this.store.leadSources || []
            }
            const query = this.form.client.source.toLowerCase()
            return (this.store.leadSources || []).filter(source =>
                source.toLowerCase().includes(query)
            )
        },
        completedSubtasks() {
            return this.form.subtasks.filter(s => s.done).length
        },
        clientSections() {
            const customFields = this.store.board?.config?.custom_fields || []
            return customFields.filter(section => section.target === 'client')
        }
    },

    watch: {
        prefilledColumnId: {
            immediate: true,
            handler(newVal) {
                if (newVal && !this.isEdit) {
                    this.form.column_id = newVal
                }
            }
        },
        client: {
            immediate: true,
            handler(newVal) {
                if (newVal && this.isEdit) {
                    this.fillFormFromClient(newVal)
                }
            }
        }
    },

    mounted() {
        this.$nextTick(() => {
            this.isVisible = true
            this.loadLeadSources()

            if (this.isEdit && this.client) {
                this.fillFormFromClient(this.client)
            }

            this.initSortable()
        })

        document.addEventListener('click', this.handleClickOutside)
    },

    beforeUnmount() {
        document.body.style.overflow = ''
        document.removeEventListener('click', this.handleClickOutside)
    },

    methods: {
        addLink() {
            if (!this.newLinkUrl.trim()) return

            // Добавляем https:// если нет протокола
            let url = this.newLinkUrl.trim()
            if (!url.match(/^https?:\/\//)) {
                url = 'https://' + url
            }

            this.links.push({
                url: url,
                title: this.newLinkTitle.trim() || ''
            })

            this.newLinkUrl = ''
            this.newLinkTitle = ''
        },

        removeLink(index) {
            this.links.splice(index, 1)
        },
        // === ПОДЗАДАЧИ ===
        addSubtask() {
            if (!this.newSubtaskText.trim()) return

            this.form.subtasks.push({
                id: Date.now() + Math.random(),
                text: this.newSubtaskText.trim(),
                done: false
            })
            this.newSubtaskText = ''
        },

        removeSubtask(index) {
            this.form.subtasks.splice(index, 1)
        },

        initSortable() {
            this.$nextTick(() => {
                if (!this.$refs.subtasksList) return

                Sortable.create(this.$refs.subtasksList, {
                    animation: 150,
                    handle: '.drag-handle',
                    onEnd: (evt) => {
                        const moved = this.form.subtasks.splice(evt.oldIndex, 1)[0]
                        this.form.subtasks.splice(evt.newIndex, 0, moved)
                    }
                })
            })
        },

        // === ЭКСПОРТ ===
        async exportClient() {
            if (!this.client) return

            try {
                window.open(`/api/clients/${this.client.id}/export`, '_blank')
            } catch (error) {
                console.error('Ошибка экспорта:', error)
                alert('Не удалось экспортировать данные')
            }
        },

        // === ЗАПОЛНЕНИЕ ФОРМЫ ===
        fillFormFromClient(client) {
            this.form.column_id = this.task?.column_id || this.prefilledColumnId || this.columns[0]?.id || null
            this.form.title = this.task?.title || client.company_name || ''
            this.form.type = 2

            if (this.task) {
                this.form.priority = this.task.priority || 'low'
                this.form.description = this.task.description || ''
                this.form.due_date = this.task.due_date || null
                this.form.tag_ids = this.task.tags?.map(t => t.id) || []
                this.form.labels = this.task.labels || []
                this.form.subtasks = this.task.subtasks || []
            }

            this.form.client = {
                company_name: client.company_name || '',
                contact_person: client.contact_person || '',
                phone: client.phone || '',
                source: client.source || '',
                address: client.address || '',
                placement_type: client.placement_type || '',
                cost: client.cost || null,
                partner: client.partner || '',
                deal_comment: client.deal_comment || '',
                links: client.links || []
            }

            this.linksString = Array.isArray(client.links)
                ? client.links.join(', ')
                : ''

            if (Array.isArray(client.links)) {
                this.links = client.links.map(link => {
                    // Поддержка старого формата (просто строки)
                    if (typeof link === 'string') {
                        return { url: link, title: '' }
                    }
                    return link
                })
            } else {
                this.links = []
            }

            this.form.client.custom_data = client.custom_data || {}
        },

        // === ИСТОЧНИКИ ЛИДОВ ===
        async loadLeadSources() {
            try {
                await this.store.fetchLeadSources()
            } catch (error) {
                console.error('Ошибка загрузки источников:', error)
            }
        },

        onSourceInput() {
            this.showDropdown = true
            this.highlightedIndex = -1
        },

        selectSource(source) {
            this.form.client.source = source
            this.showDropdown = false
            this.highlightedIndex = -1
        },

        navigateDown() {
            if (!this.showDropdown) this.showDropdown = true
            if (this.highlightedIndex < this.filteredSources.length - 1) {
                this.highlightedIndex++
            }
        },

        navigateUp() {
            if (this.highlightedIndex > 0) {
                this.highlightedIndex--
            }
        },

        selectHighlighted() {
            if (this.highlightedIndex >= 0 && this.highlightedIndex < this.filteredSources.length) {
                this.selectSource(this.filteredSources[this.highlightedIndex])
            }
        },

        handleClickOutside(event) {
            if (this.$refs.sourceAutocomplete && !this.$refs.sourceAutocomplete.contains(event.target)) {
                this.showDropdown = false
            }
        },

        // === ОТКРЫТИЕ/ЗАКРЫТИЕ ===
        show() {
            if (!this.isEdit) this.resetForm()
            this.isVisible = true
            document.body.style.overflow = 'hidden'
        },

        close() {
            this.isVisible = false
            document.body.style.overflow = ''
            this.$emit('close')
        },

        handleOverlayClick() {
            this.close()
        },

        resetForm() {
            this.form.column_id = this.prefilledColumnId || this.columns[0]?.id || null
            this.form.title = ''
            this.form.priority = 'low'
            this.form.description = ''
            this.form.due_date = null
            this.form.tag_ids = []
            this.form.labels = []
            this.form.subtasks = []
            this.form.client = {
                company_name: '',
                contact_person: '',
                phone: '',
                source: '',
                address: '',
                placement_type: '',
                cost: null,
                partner: '',
                deal_comment: '',
                links: [],
            }
            this.linksString = ''
            this.activeTab = 'form'

            this.links = []
            this.form.client.custom_data = {}
        },

        onTimelineLoaded(count) {
            this.activitiesCount = count
        },

        // === ОТПРАВКА ФОРМЫ ===
        async submit() {
            if (!this.form.column_id) {
                alert('Пожалуйста, выберите этап воронки')
                return
            }

            this.loading = true

            this.form.client.links = this.links.map(link => ({
                url: link.url,
                title: link.title
            }))


            if (!this.form.title.trim()) {
                this.form.title = this.form.client.company_name || 'Новый клиент'
            }

            try {
                let response

                if (this.isEdit && this.task) {
                    const payload = {
                        title: this.form.title,
                        column_id: this.form.column_id,
                        type: 2,
                        priority: this.form.priority || 'low',
                        description: this.form.description || '',
                        due_date: this.form.due_date || null,
                        labels: this.form.labels || [],
                        subtasks: this.form.subtasks || [],
                        tag_ids: this.form.tag_ids || [],
                        client: this.form.client,
                        custom_data: this.form.client.custom_data || {}
                    }

                    response = await axios.put(`/api/tasks/${this.task.id}`, payload)
                    this.$emit('updated', response.data)
                } else {
                    response = await axios.post(`/api/boards/${this.boardUuid}/tasks`, this.form)
                    this.$emit('created', response.data)
                }

                this.close()
            } catch (e) {
                console.error(e)
                alert(this.isEdit ? 'Ошибка обновления клиента' : 'Ошибка создания клиента')
            } finally {
                this.loading = false
            }
        }
    }
}
</script>

<style scoped>
/* === КНОПКА ЭКСПОРТА В HEADER === */
.header-actions {
    display: flex;
    align-items: center;
    gap: 8px;
}

.export-btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 14px;
    border: none;
    background: rgba(255, 255, 255, 0.15);
    color: white;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    backdrop-filter: blur(10px);
}

.export-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: translateY(-1px);
}

.export-btn i {
    font-size: 14px;
    color: #4ade80; /* Зелёный как Excel */
}

/* === СЕКЦИЯ ПОДЗАДАЧ === */
.subtasks-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.subtasks-counter {
    background: #e9ecef;
    color: #495057;
    padding: 2px 10px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
    margin-left: 8px;
}

.add-subtask-row {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
}

.flex-grow {
    flex: 1;
}

.btn-add-subtask {
    padding: 10px 20px;
    border: none;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
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
    border: 2px dashed #dee2e6;
    border-radius: 10px;
}

.empty-subtasks i {
    font-size: 36px;
    margin-bottom: 8px;
    opacity: 0.5;
}

.empty-subtasks p {
    font-size: 13px;
    margin: 0;
    color: #6c757d;
}

/* === ОСТАЛЬНЫЕ СТИЛИ (без изменений) === */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    padding: 20px;
}

.modal-window {
    background: #ffffff;
    border-radius: 20px;
    width: 700px;
    max-width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
    animation: modalSlideUp 0.3s ease;
}

@keyframes modalSlideUp {
    from { opacity: 0; transform: translateY(30px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

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

.header-text { flex: 1; }

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
    gap: 4px;
    position: relative;
}

.tab-btn:hover { background: #e9ecef; color: #495057; }

.tab-btn.active {
    background: #ffffff;
    color: #0d6efd;
    box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05);
}

.tab-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 700;
    min-width: 20px;
    text-align: center;
    margin-left: 4px;
}

.modal-body-custom {
    padding: 28px;
    overflow-y: auto;
    flex: 1;
}

.form-section { margin-bottom: 32px; }
.form-section:last-child { margin-bottom: 0; }

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

.stage-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.info-icon {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

.deal-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.section-title {
    font-size: 16px;
    font-weight: 700;
    color: #212529;
    margin: 0 0 2px 0;
    display: flex;
    align-items: center;
}

.section-desc { font-size: 12px; color: #6c757d; margin: 0; }

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
}

.form-group { display: flex; flex-direction: column; gap: 6px; }
.form-group.full-width { grid-column: 1 / -1; }

.form-label-custom {
    font-size: 13px;
    font-weight: 600;
    color: #495057;
}

.required { color: #dc3545; margin-left: 2px; }

.input-wrapper { position: relative; display: flex; align-items: center; }

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

.custom-input::placeholder { color: #adb5bd; }

.custom-textarea {
    width: 100%;
    padding: 12px 14px;
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

.input-hint { font-size: 11px; color: #6c757d; margin-top: 4px; }

.custom-select-wrapper { position: relative; }

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

.spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }

/* === AUTOCOMPLETE === */
.autocomplete-wrapper { position: relative; }

.clear-btn {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    width: 24px;
    height: 24px;
    border: none;
    background: #e9ecef;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: #6c757d;
    font-size: 11px;
    transition: all 0.2s;
    z-index: 2;
}

.clear-btn:hover { background: #dee2e6; color: #495057; }

.autocomplete-dropdown {
    position: absolute;
    top: calc(100% + 4px);
    left: 0;
    right: 0;
    background: #ffffff;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    max-height: 240px;
    overflow-y: auto;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    z-index: 100;
}

.autocomplete-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    cursor: pointer;
    transition: all 0.15s;
    border-bottom: 1px solid #f1f3f5;
}

.autocomplete-item:last-child { border-bottom: none; }
.autocomplete-item:hover, .autocomplete-item.highlighted { background: #f8f9fa; }

.item-icon { font-size: 10px; color: #adb5bd; }
.item-text { flex: 1; font-size: 14px; color: #212529; }
.item-check { font-size: 11px; color: #10b981; }

.dropdown-fade-enter-active, .dropdown-fade-leave-active { transition: all 0.2s ease; }
.dropdown-fade-enter-from, .dropdown-fade-leave-to { opacity: 0; transform: translateY(-8px); }

.autocomplete-dropdown::-webkit-scrollbar { width: 6px; }
.autocomplete-dropdown::-webkit-scrollbar-track { background: #f1f3f5; border-radius: 3px; }
.autocomplete-dropdown::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 3px; }
.autocomplete-dropdown::-webkit-scrollbar-thumb:hover { background: #adb5bd; }

@media (max-width: 768px) {
    .modal-window { width: 100%; max-height: 95vh; border-radius: 16px; }
    .modal-header-custom { padding: 20px; border-radius: 16px 16px 0 0; }
    .header-icon { width: 48px; height: 48px; font-size: 20px; }
    .modal-title-text { font-size: 18px; }
    .modal-body-custom { padding: 20px; }
    .form-grid { grid-template-columns: 1fr; }
    .modal-tabs { padding: 12px 20px 0; }
    .tab-btn { padding: 8px 16px; font-size: 13px; }
    .modal-footer-custom { padding: 16px 20px; border-radius: 0 0 16px 16px; }
    .btn-footer { flex: 1; }
    .add-subtask-row { flex-direction: column; }
    .btn-add-subtask { width: 100%; justify-content: center; }
    .export-btn .btn-text { display: none; }
}

.modal-body-custom::-webkit-scrollbar { width: 8px; }
.modal-body-custom::-webkit-scrollbar-track { background: #f1f3f5; border-radius: 4px; }
.modal-body-custom::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 4px; }
.modal-body-custom::-webkit-scrollbar-thumb:hover { background: #adb5bd; }

/* === СЕКЦИЯ ССЫЛОК === */
.links-icon {
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(6, 182, 212, 0.3);
}

.add-link-row {
    display: flex;
    gap: 8px;
    margin-bottom: 16px;
}

.btn-add-link {
    padding: 10px 20px;
    border: none;
    background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);
    color: white;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
    box-shadow: 0 2px 8px rgba(6, 182, 212, 0.3);
}

.btn-add-link:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(6, 182, 212, 0.4);
}

.btn-add-link:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.links-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.link-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 10px 14px;
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    transition: all 0.2s;
}

.link-item:hover {
    background: #ffffff;
    border-color: #06b6d4;
}

.link-icon {
    font-size: 16px;
    color: #06b6d4;
    flex-shrink: 0;
}

.link-content {
    flex: 1;
    min-width: 0;
}

.link-title {
    font-size: 13px;
    font-weight: 600;
    color: #212529;
    margin-bottom: 2px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.link-url {
    font-size: 11px;
    color: #6c757d;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.link-action-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    color: #06b6d4;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0.6;
    text-decoration: none;
}

.link-action-btn:hover {
    background: #e7f5ff;
    opacity: 1;
}

.link-action-remove {
    color: #dc3545;
}

.link-action-remove:hover {
    background: #fff5f5;
}

.empty-links {
    text-align: center;
    padding: 32px 20px;
    color: #adb5bd;
    border: 2px dashed #dee2e6;
    border-radius: 10px;
}

.empty-links i {
    font-size: 36px;
    margin-bottom: 8px;
    opacity: 0.5;
}

.empty-links p {
    font-size: 13px;
    margin: 0;
    color: #6c757d;
}

/* === СЕКЦИЯ ПАРТНЁРА === */
.partner-icon {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
}

@media (max-width: 768px) {
    .add-link-row {
        flex-direction: column;
    }

    .add-link-row .input-wrapper {
        width: 100% !important;
    }

    .btn-add-link {
        width: 100%;
        justify-content: center;
    }
}
</style>

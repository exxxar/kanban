<template>
    <div class="custom-fields-settings">
        <!-- Заголовок -->
        <div class="section-header">
            <div class="section-icon custom-icon">
                <i class="fa-solid fa-puzzle-piece"></i>
            </div>
            <div>
                <h4 class="section-title">Кастомные поля</h4>
                <p class="section-desc">Создавайте собственные секции и поля для задач и клиентов</p>
            </div>
        </div>

        <!-- Список секций -->
        <div v-if="sections.length > 0" class="sections-list">
            <div
                v-for="(section, sIndex) in sections"
                :key="section.id"
                class="section-card"
            >
                <!-- Заголовок секции -->
                <div class="section-card-header" :style="{ background: section.color + '15', borderColor: section.color }">
                    <div class="section-card-icon" :style="{ background: section.color }">
                        <i :class="section.icon || 'fa-solid fa-puzzle-piece'"></i>
                    </div>
                    <div class="section-card-title">
                        <input
                            v-model="section.title"
                            type="text"
                            class="section-title-input"
                            placeholder="Название секции"
                        >
                        <div class="section-card-meta">
                            <span class="meta-badge" :class="section.target">
                                {{ section.target === 'task' ? 'Задача' : 'Клиент' }}
                            </span>
                            <span class="meta-fields">
                                {{ section.fields.length }} полей
                            </span>
                        </div>
                    </div>
                    <button
                        class="section-remove-btn"
                        @click="removeSection(sIndex)"
                        title="Удалить секцию"
                    >
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>

                <!-- Тело секции -->
                <div class="section-card-body">
                    <!-- Настройки секции -->
                    <div class="section-settings">
                        <div class="setting-row">
                            <label class="setting-label">Иконка</label>
                            <IconPicker
                                v-model="section.icon"
                                :preview-color="section.color"
                            />
                        </div>

                        <div class="setting-row">
                            <label class="setting-label">Цвет</label>
                            <div class="color-picker-wrapper">
                                <input
                                    v-model="section.color"
                                    type="color"
                                    class="color-picker"
                                >
                            </div>
                        </div>

                        <div class="setting-row">
                            <label class="setting-label">Отображать в</label>
                            <div class="target-switch">
                                <button
                                    class="target-btn"
                                    :class="{ active: section.target === 'task' }"
                                    @click="section.target = 'task'"
                                >
                                    <i class="fa-solid fa-list-check me-1"></i>
                                    Задаче
                                </button>
                                <button
                                    class="target-btn"
                                    :class="{ active: section.target === 'client' }"
                                    @click="section.target = 'client'"
                                >
                                    <i class="fa-solid fa-user-tie me-1"></i>
                                    Клиенте
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Поля секции -->
                    <div class="fields-section">
                        <div class="fields-header">
                            <span class="fields-title">Поля секции</span>
                        </div>

                        <div v-if="section.fields.length > 0" class="fields-list">
                            <div
                                v-for="(field, fIndex) in section.fields"
                                :key="fIndex"
                                class="field-item"
                            >
                                <div class="field-row">
                                    <div class="field-input-group">
                                        <label class="field-label">Название</label>
                                        <input
                                            v-model="field.label"
                                            type="text"
                                            class="field-input"
                                            placeholder="Например: Бюджет"
                                        >
                                    </div>
                                    <div class="field-input-group">
                                        <label class="field-label">Имя (name)</label>
                                        <input
                                            v-model="field.name"
                                            type="text"
                                            class="field-input"
                                            placeholder="budget"
                                            @input="field.name = slugify(field.name)"
                                        >
                                    </div>
                                    <div class="field-input-group">
                                        <label class="field-label">Тип</label>
                                        <select v-model="field.type" class="field-select">
                                            <option value="text">Текст</option>
                                            <option value="number">Число</option>
                                            <option value="date">Дата</option>
                                            <option value="email">Email</option>
                                            <option value="url">URL</option>
                                            <option value="textarea">Текстовое поле</option>
                                        </select>
                                    </div>
                                    <button
                                        class="field-remove-btn"
                                        @click="removeField(sIndex, fIndex)"
                                        title="Удалить поле"
                                    >
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-else class="empty-fields">
                            <p>Нет полей. Добавьте первое.</p>
                        </div>

                        <!-- Добавление поля -->
                        <button
                            class="btn-add-field"
                            @click="addField(sIndex)"
                        >
                            <i class="fa-solid fa-plus"></i>
                            <span>Добавить поле</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Пустое состояние -->
        <div v-else class="empty-sections">
            <i class="fa-solid fa-puzzle-piece"></i>
            <p>Нет кастомных секций</p>
            <p class="empty-hint">Создайте первую секцию для дополнительных полей</p>
        </div>

        <!-- Добавление секции -->
        <button class="btn-add-section" @click="addSection">
            <i class="fa-solid fa-plus"></i>
            <span>Добавить секцию</span>
        </button>
    </div>
</template>

<script>
import IconPicker from './IconPicker.vue'

export default {
    components: {
        IconPicker
    },
    props: {
        modelValue: { type: Array, default: () => [] }
    },
    emits: ['update:modelValue'],

    data() {
        return {
            sections: []
        }
    },

    watch: {
        modelValue: {
            immediate: true,
            deep: true,
            handler(newVal) {
                this.sections = JSON.parse(JSON.stringify(newVal || []))
            }
        },
        sections: {
            deep: true,
            handler(newVal) {
                this.$emit('update:modelValue', JSON.parse(JSON.stringify(newVal)))
            }
        }
    },

    methods: {
        addSection() {
            this.sections.push({
                id: Date.now(),
                title: 'Новая секция',
                icon: 'fa-solid fa-puzzle-piece',
                color: '#667eea',
                target: 'task',
                fields: []
            })
        },

        removeSection(index) {
            if (confirm('Удалить секцию и все её поля?')) {
                this.sections.splice(index, 1)
            }
        },

        addField(sectionIndex) {
            this.sections[sectionIndex].fields.push({
                label: '',
                name: '',
                type: 'text'
            })
        },

        removeField(sectionIndex, fieldIndex) {
            this.sections[sectionIndex].fields.splice(fieldIndex, 1)
        },

        slugify(text) {
            return text
                .toLowerCase()
                .replace(/[^a-z0-9_]/g, '_')
                .replace(/_+/g, '_')
                .replace(/^_|_$/g, '')
        }
    }
}
</script>

<style scoped>
.custom-fields-settings {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.custom-icon {
    background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
    box-shadow: 0 4px 12px rgba(236, 72, 153, 0.3);
}

.section-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 16px;
    padding-bottom: 12px;
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
    color: white;
    flex-shrink: 0;
}

.section-title {
    font-size: 16px;
    font-weight: 700;
    color: #212529;
    margin: 0 0 2px 0;
}

.section-desc {
    font-size: 12px;
    color: #6c757d;
    margin: 0;
}

/* === КАРТОЧКА СЕКЦИИ === */
.sections-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.section-card {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.2s;
}

.section-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
}

.section-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    border-bottom: 1px solid #e9ecef;
    border-left: 4px solid;
}

.section-card-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
    flex-shrink: 0;
}

.section-card-title {
    flex: 1;
    min-width: 0;
}

.section-title-input {
    width: 100%;
    padding: 4px 8px;
    border: 1px solid transparent;
    border-radius: 6px;
    font-size: 15px;
    font-weight: 600;
    color: #212529;
    background: transparent;
    transition: all 0.2s;
}

.section-title-input:hover {
    border-color: #dee2e6;
    background: #ffffff;
}

.section-title-input:focus {
    border-color: #0d6efd;
    background: #ffffff;
    outline: none;
}

.section-card-meta {
    display: flex;
    gap: 6px;
    margin-top: 4px;
    padding-left: 8px;
}

.meta-badge {
    font-size: 10px;
    font-weight: 700;
    padding: 2px 8px;
    border-radius: 6px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.meta-badge.task {
    background: #e7f1ff;
    color: #0d6efd;
}

.meta-badge.client {
    background: #d1e7dd;
    color: #0f5132;
}

.meta-fields {
    font-size: 11px;
    color: #6c757d;
}

.section-remove-btn {
    width: 34px;
    height: 34px;
    border: none;
    background: transparent;
    color: #dc3545;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.section-remove-btn:hover {
    background: #fff5f5;
}

/* === ТЕЛО СЕКЦИИ === */
.section-card-body {
    padding: 16px;
    background: #ffffff;
}

.section-settings {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    margin-bottom: 16px;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 10px;
}

.setting-row {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.setting-label {
    font-size: 11px;
    font-weight: 600;
    color: #6c757d;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.color-picker-wrapper {
    width: 100%;
    height: 40px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    overflow: hidden;
    cursor: pointer;
}

.color-picker {
    width: 100%;
    height: 100%;
    border: none;
    cursor: pointer;
}

/* === TARGET SWITCH === */
.target-switch {
    display: flex;
    gap: 4px;
    background: #e9ecef;
    padding: 4px;
    border-radius: 8px;
}

.target-btn {
    flex: 1;
    padding: 8px 12px;
    border: none;
    background: transparent;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.target-btn.active {
    background: #ffffff;
    color: #0d6efd;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
}

/* === ПОЛЯ === */
.fields-section {
    margin-top: 12px;
}

.fields-header {
    margin-bottom: 10px;
}

.fields-title {
    font-size: 13px;
    font-weight: 600;
    color: #495057;
}

.fields-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 12px;
}

.field-item {
    padding: 10px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 8px;
}

.field-row {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr auto;
    gap: 8px;
    align-items: end;
}

.field-input-group {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.field-label {
    font-size: 11px;
    font-weight: 600;
    color: #6c757d;
}

.field-input,
.field-select {
    padding: 8px 10px;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    font-size: 13px;
    outline: none;
    transition: all 0.2s;
    background: #ffffff;
}

.field-input:focus,
.field-select:focus {
    border-color: #0d6efd;
}

.field-remove-btn {
    width: 32px;
    height: 32px;
    border: none;
    background: transparent;
    color: #dc3545;
    border-radius: 6px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.field-remove-btn:hover {
    background: #fff5f5;
}

.empty-fields {
    text-align: center;
    padding: 20px;
    color: #adb5bd;
    font-size: 12px;
    border: 1px dashed #dee2e6;
    border-radius: 8px;
    margin-bottom: 12px;
}

.empty-fields p {
    margin: 0;
}

.btn-add-field {
    width: 100%;
    padding: 8px 16px;
    border: 1px dashed #dee2e6;
    background: transparent;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    color: #6c757d;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.btn-add-field:hover {
    border-color: #0d6efd;
    color: #0d6efd;
    background: #f8f9fa;
}

/* === ПУСТОЕ СОСТОЯНИЕ === */
.empty-sections {
    text-align: center;
    padding: 48px 20px;
    color: #adb5bd;
    border: 2px dashed #dee2e6;
    border-radius: 12px;
}

.empty-sections i {
    font-size: 48px;
    margin-bottom: 12px;
    opacity: 0.5;
}

.empty-sections p {
    font-size: 14px;
    margin: 0 0 4px 0;
    color: #6c757d;
}

.empty-hint {
    font-size: 12px !important;
    color: #adb5bd !important;
}

.btn-add-section {
    width: 100%;
    padding: 12px 20px;
    border: 2px dashed #ec4899;
    background: transparent;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    color: #ec4899;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-add-section:hover {
    background: #fdf2f8;
    border-style: solid;
}

@media (max-width: 768px) {
    .section-settings {
        grid-template-columns: 1fr;
    }

    .field-row {
        grid-template-columns: 1fr;
    }
}
</style>

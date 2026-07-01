<template>
    <div v-if="sections.length > 0" class="custom-fields-renderer mt-3">
        <div
            v-for="section in sections"
            :key="section.id"
            class="custom-section"
        >
            <!-- Заголовок секции -->
            <div class="custom-section-header" :style="{ borderColor: section.color }">
                <div class="custom-section-icon" :style="{ background: section.color }">
                    <i :class="section.icon || 'fa-solid fa-puzzle-piece'"></i>
                </div>
                <div class="custom-section-title">
                    <h4>{{ section.title }}</h4>
                    <p class="custom-section-desc">Кастомные поля</p>
                </div>
            </div>

            <!-- Поля секции -->
            <div class="custom-fields-grid">
                <div
                    v-for="field in section.fields"
                    :key="field.name"
                    class="custom-field-group"
                    :class="{ 'full-width': field.type === 'textarea' }"
                >
                    <label class="custom-field-label">
                        {{ field.label }}
                    </label>

                    <!-- Text -->
                    <div v-if="field.type === 'text'" class="input-wrapper">
                        <i class="fa-solid fa-font input-icon"></i>
                        <input
                            :value="getFieldValue(field.name)"
                            @input="setFieldValue(field.name, $event.target.value)"
                            type="text"
                            class="custom-input"
                            :placeholder="field.label"
                        >
                    </div>

                    <!-- Number -->
                    <div v-else-if="field.type === 'number'" class="input-wrapper">
                        <i class="fa-solid fa-hashtag input-icon"></i>
                        <input
                            :value="getFieldValue(field.name)"
                            @input="setFieldValue(field.name, $event.target.value)"
                            type="number"
                            step="0.01"
                            class="custom-input"
                            :placeholder="field.label"
                        >
                    </div>

                    <!-- Date -->
                    <div v-else-if="field.type === 'date'" class="input-wrapper">
                        <i class="fa-solid fa-calendar input-icon"></i>
                        <input
                            :value="getFieldValue(field.name)"
                            @input="setFieldValue(field.name, $event.target.value)"
                            type="date"
                            class="custom-input"
                        >
                    </div>

                    <!-- Email -->
                    <div v-else-if="field.type === 'email'" class="input-wrapper">
                        <i class="fa-solid fa-envelope input-icon"></i>
                        <input
                            :value="getFieldValue(field.name)"
                            @input="setFieldValue(field.name, $event.target.value)"
                            type="email"
                            class="custom-input"
                            :placeholder="field.label"
                        >
                    </div>

                    <!-- URL -->
                    <div v-else-if="field.type === 'url'" class="input-wrapper">
                        <i class="fa-solid fa-link input-icon"></i>
                        <input
                            :value="getFieldValue(field.name)"
                            @input="setFieldValue(field.name, $event.target.value)"
                            type="url"
                            class="custom-input"
                            placeholder="https://..."
                        >
                    </div>

                    <!-- Textarea -->
                    <div v-else-if="field.type === 'textarea'">
                        <textarea
                            :value="getFieldValue(field.name)"
                            @input="setFieldValue(field.name, $event.target.value)"
                            class="custom-textarea"
                            rows="3"
                            :placeholder="field.label"
                        ></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        sections: { type: Array, default: () => [] },
        target: { type: String, required: true }, // 'task' или 'client'
        modelValue: { type: Object, default: () => ({}) }
    },
    emits: ['update:modelValue'],

    methods: {
        getFieldValue(fieldName) {
            return this.modelValue[fieldName] ?? ''
        },

        setFieldValue(fieldName, value) {
            const newData = { ...this.modelValue, [fieldName]: value }
            this.$emit('update:modelValue', newData)
        }
    }
}
</script>

<style scoped>
.custom-fields-renderer {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.custom-section {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
}

.custom-section-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 16px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    border-left: 4px solid;
}

.custom-section-icon {
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

.custom-section-title {
    flex: 1;
}

.custom-section-title h4 {
    font-size: 15px;
    font-weight: 600;
    color: #212529;
    margin: 0 0 2px 0;
}

.custom-section-desc {
    font-size: 11px;
    color: #6c757d;
    margin: 0;
}

.custom-fields-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 16px;
    padding: 16px;
}

.custom-field-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.custom-field-group.full-width {
    grid-column: 1 / -1;
}

.custom-field-label {
    font-size: 13px;
    font-weight: 600;
    color: #495057;
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
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

@media (max-width: 768px) {
    .custom-fields-grid {
        grid-template-columns: 1fr;
    }
}
</style>

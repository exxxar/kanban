<template>
    <div class="custom-categories">
        <div class="section-header">
            <div class="section-icon categories-icon">
                <i class="fa-solid fa-tags"></i>
            </div>
            <div>
                <h4 class="section-title">Кастомные категории</h4>
                <p class="section-desc">Создавайте свои категории задач</p>
            </div>
        </div>

        <!-- Список категорий -->
        <div v-if="categories.length > 0" class="categories-list">
            <div
                v-for="(cat, index) in categories"
                :key="cat.id"
                class="category-item"
            >
                <div class="category-preview">
                    <i :class="cat.icon"></i>
                    <span>{{ cat.name }}</span>
                </div>
                <div class="category-actions">
                    <button
                        class="btn-edit-category"
                        @click="editCategory(index)"
                    >
                        <i class="fa-solid fa-pen"></i>
                    </button>
                    <button
                        class="btn-delete-category"
                        @click="removeCategory(index)"
                    >
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Форма добавления/редактирования -->
        <div class="add-category-form">
            <div class="form-row">
                <div class="input-wrapper flex-grow">
                    <i class="fa-solid fa-tag input-icon"></i>
                    <input
                        v-model="newCategory.name"
                        type="text"
                        class="custom-input"
                        placeholder="Название категории"
                    >
                </div>
                <div class="input-wrapper" style="width: 200px;">
                    <i class="fa-solid fa-code input-icon"></i>
                    <input
                        v-model="newCategory.key"
                        type="text"
                        class="custom-input"
                        placeholder="key (латиницей)"
                        @input="newCategory.key = slugify(newCategory.key)"
                    >
                </div>
                <IconPicker
                    v-model="newCategory.icon"
                    preview-color="#0d6efd"
                />
                <button
                    class="btn-add-category"
                    @click="addCategory"
                    :disabled="!newCategory.name.trim() || !newCategory.key.trim()"
                >
                    <i class="fa-solid fa-plus"></i>
                    <span>{{ editingIndex !== null ? 'Обновить' : 'Добавить' }}</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import IconPicker from './IconPicker.vue'

export default {
    components: { IconPicker },
    props: {
        modelValue: { type: Array, default: () => [] }
    },
    emits: ['update:modelValue'],

    data() {
        return {
            categories: [],
            editingIndex: null,
            newCategory: {
                name: '',
                key: '',
                icon: 'fa-solid fa-tag'
            },
            isUpdating: false // ← Флаг для предотвращения цикла
        }
    },

    watch: {
        modelValue: {
            immediate: true,
            handler(newVal) {
                // Обновляем только если данные действительно изменились
                if (!this.isUpdating && !this.isEqual(this.categories, newVal)) {
                    this.categories = JSON.parse(JSON.stringify(newVal || []))
                }
            }
        },
        categories: {
            deep: true,
            handler(newVal) {
                // Emit только если данные действительно изменились
                if (!this.isEqual(this.modelValue, newVal)) {
                    this.isUpdating = true
                    this.$emit('update:modelValue', JSON.parse(JSON.stringify(newVal)))
                    this.$nextTick(() => {
                        this.isUpdating = false
                    })
                }
            }
        }
    },

    methods: {
        addCategory() {
            if (!this.newCategory.name.trim() || !this.newCategory.key.trim()) return

            if (this.editingIndex !== null) {
                this.categories[this.editingIndex] = { ...this.newCategory }
                this.editingIndex = null
            } else {
                this.categories.push({
                    id: Date.now(),
                    ...this.newCategory
                })
            }

            this.resetForm()
        },

        editCategory(index) {
            this.editingIndex = index
            this.newCategory = { ...this.categories[index] }
        },

        removeCategory(index) {
            if (confirm('Удалить эту категорию?')) {
                this.categories.splice(index, 1)
            }
        },

        resetForm() {
            this.newCategory = {
                name: '',
                key: '',
                icon: 'fa-solid fa-tag'
            }
            this.editingIndex = null
        },

        slugify(text) {
            return text
                .toLowerCase()
                .replace(/[^a-z0-9_]/g, '_')
                .replace(/_+/g, '_')
                .replace(/^_|_$/g, '')
        },

        // Глубокое сравнение массивов
        isEqual(arr1, arr2) {
            if (!arr1 && !arr2) return true
            if (!arr1 || !arr2) return false
            if (arr1.length !== arr2.length) return false

            return JSON.stringify(arr1) === JSON.stringify(arr2)
        }
    }
}
</script>
<style scoped>
.custom-categories {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

/* === ЗАГОЛОВОК СЕКЦИИ === */
.section-header {
    display: flex;
    align-items: center;
    gap: 14px;
    margin-bottom: 4px;
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

.categories-icon {
    background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
    box-shadow: 0 4px 12px rgba(236, 72, 153, 0.3);
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

/* === СПИСОК КАТЕГОРИЙ === */
.categories-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.category-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 12px 16px;
    background: #ffffff;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    transition: all 0.2s;
}

.category-item:hover {
    border-color: #dee2e6;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.category-preview {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 0;
}

.category-preview i {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    background: linear-gradient(135deg, #f3f0ff 0%, #ede9fe 100%);
    color: #7c3aed;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
}

.category-preview span {
    font-size: 14px;
    font-weight: 600;
    color: #212529;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.category-actions {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}

.btn-edit-category,
.btn-delete-category {
    width: 32px;
    height: 32px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
}

.btn-edit-category {
    background: #e7f1ff;
    color: #0d6efd;
}

.btn-edit-category:hover {
    background: #0d6efd;
    color: white;
    transform: translateY(-1px);
}

.btn-delete-category {
    background: #fff5f5;
    color: #dc3545;
}

.btn-delete-category:hover {
    background: #dc3545;
    color: white;
    transform: translateY(-1px);
}

/* === ФОРМА ДОБАВЛЕНИЯ === */
.add-category-form {
    padding: 16px;
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    transition: all 0.2s;
}

.add-category-form:focus-within {
    border-color: #ec4899;
    background: #fdf2f8;
}

.form-row {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.input-wrapper.flex-grow {
    flex: 1;
    min-width: 180px;
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
    border-color: #ec4899;
    box-shadow: 0 0 0 4px rgba(236, 72, 153, 0.1);
}

.custom-input::placeholder {
    color: #adb5bd;
}

.btn-add-category {
    padding: 10px 20px;
    border: none;
    background: linear-gradient(135deg, #ec4899 0%, #db2777 100%);
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
    box-shadow: 0 2px 8px rgba(236, 72, 153, 0.3);
}

.btn-add-category:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(236, 72, 153, 0.4);
}

.btn-add-category:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* === АДАПТИВ === */
@media (max-width: 768px) {
    .form-row {
        flex-direction: column;
    }

    .input-wrapper {
        width: 100%;
    }

    .btn-add-category {
        width: 100%;
        justify-content: center;
    }

    .category-item {
        padding: 10px 12px;
    }
}
</style>

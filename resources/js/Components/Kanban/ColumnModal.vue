<template>
    <Transition name="modal-fade">
        <div v-if="isVisible" class="modal-overlay" @click.self="close" @keydown.esc="close">
            <div class="modal-window">
                <!-- HEADER -->
                <div class="modal-header-custom">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fa-solid fa-layer-group"></i>
                        </div>
                        <div class="header-text">
                            <h3 class="modal-title-text">Новая колонка</h3>
                            <p class="modal-subtitle">Создайте колонку для организации задач</p>
                        </div>
                    </div>
                    <button class="close-btn" @click="close" title="Закрыть">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- BODY -->
                <div class="modal-body-custom">
                    <form @submit.prevent="save">
                        <div class="form-group">
                            <label class="form-label-custom">
                                Название колонки <span class="required">*</span>
                            </label>
                            <div class="input-wrapper">
                                <i class="fa-solid fa-heading input-icon"></i>
                                <input
                                    ref="titleInput"
                                    v-model="title"
                                    type="text"
                                    class="custom-input"
                                    placeholder="Например: В работе, Готово, Архив"
                                    required
                                    @keydown.enter="save"
                                >
                            </div>
                            <div class="input-hint">
                                <i class="fa-solid fa-circle-info me-1"></i>
                                Название можно будет изменить позже двойным кликом
                            </div>
                        </div>
                    </form>
                </div>

                <!-- FOOTER -->
                <div class="modal-footer-custom">
                    <button class="btn-footer btn-cancel" @click="close">
                        <i class="fa-solid fa-xmark me-2"></i>
                        Отмена
                    </button>
                    <button
                        class="btn-footer btn-save"
                        :disabled="!title.trim()"
                        @click="save"
                    >
                        <i class="fa-solid fa-plus me-2"></i>
                        Добавить колонку
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script>
export default {
    emits: ['save', 'close'],

    data() {
        return {
            isVisible: false,
            title: ''
        }
    },

    mounted() {
        this.$nextTick(() => {
            this.isVisible = true
            this.$refs.titleInput?.focus()
        })
        document.body.style.overflow = 'hidden'
    },

    beforeUnmount() {
        document.body.style.overflow = ''
    },

    methods: {
        close() {
            this.isVisible = false
            document.body.style.overflow = ''
            this.$emit('close')
        },

        save() {
            if (!this.title.trim()) return
            this.$emit('save', this.title.trim())
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
    width: 480px;
    max-width: 100%;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
    animation: modalSlideUp 0.3s ease;
    overflow: hidden;
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
    color: white;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 16px;
    min-width: 0;
    flex: 1;
}

.header-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    backdrop-filter: blur(10px);
    flex-shrink: 0;
}

.header-text {
    flex: 1;
    min-width: 0;
}

.modal-title-text {
    font-size: 20px;
    font-weight: 700;
    margin: 0 0 2px 0;
    color: white;
}

.modal-subtitle {
    font-size: 12px;
    margin: 0;
    opacity: 0.9;
}

.close-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: rgba(255, 255, 255, 0.15);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    color: white;
    font-size: 16px;
    transition: all 0.2s;
    flex-shrink: 0;
    margin-left: 12px;
}

.close-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: rotate(90deg);
}

/* === BODY === */
.modal-body-custom {
    padding: 24px 28px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
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
    padding: 12px 14px 12px 42px;
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

.input-hint {
    font-size: 11px;
    color: #6c757d;
    margin-top: 4px;
}

/* === FOOTER === */
.modal-footer-custom {
    padding: 20px 28px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    background: #f8f9fa;
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

.btn-save {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
}

.btn-save:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
}

.btn-save:disabled {
    opacity: 0.6;
    cursor: not-allowed;
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
@media (max-width: 576px) {
    .modal-window {
        width: 100%;
        border-radius: 16px;
    }

    .modal-header-custom {
        padding: 20px;
    }

    .header-icon {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }

    .modal-title-text {
        font-size: 17px;
    }

    .modal-body-custom {
        padding: 20px;
    }

    .modal-footer-custom {
        padding: 16px 20px;
        flex-direction: column-reverse;
    }

    .btn-footer {
        width: 100%;
    }
}
</style>

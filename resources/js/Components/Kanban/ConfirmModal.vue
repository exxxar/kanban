<template>
    <Transition name="modal-fade">
        <div v-if="show" class="modal-overlay" @click.self="reject" @keydown.esc="reject">
            <div class="modal-window">
                <!-- HEADER -->
                <div class="modal-header-custom">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                        <div class="header-text">
                            <h3 class="modal-title-text">{{ title }}</h3>
                            <p class="modal-subtitle">Требуется ваше подтверждение</p>
                        </div>
                    </div>
                    <button class="close-btn" @click="reject" title="Закрыть">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- BODY -->
                <div class="modal-body-custom">
                    <div class="alert-icon-wrapper">
                        <i class="fa-solid fa-circle-question"></i>
                    </div>
                    <p class="modal-description">{{ description }}</p>
                </div>

                <!-- FOOTER -->
                <div class="modal-footer-custom">
                    <slot name="buttons">
                        <button class="btn-footer btn-cancel" @click="reject">
                            <i class="fa-solid fa-xmark me-2"></i>
                            Отмена
                        </button>
                        <button class="btn-footer btn-confirm" @click="accept">
                            <i class="fa-solid fa-check me-2"></i>
                            Подтвердить
                        </button>
                    </slot>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script>
export default {
    name: "ConfirmModal",

    props: {
        show: {
            type: Boolean,
            required: true
        },
        title: {
            type: String,
            default: "Подтверждение"
        },
        description: {
            type: String,
            default: ""
        }
    },

    emits: ["update:show", "accept", "reject"],

    watch: {
        show(newVal) {
            if (newVal) {
                document.body.style.overflow = 'hidden'
                // Добавляем listener для Esc
                document.addEventListener('keydown', this.handleEsc)
            } else {
                document.body.style.overflow = ''
                document.removeEventListener('keydown', this.handleEsc)
            }
        }
    },

    beforeUnmount() {
        document.body.style.overflow = ''
        document.removeEventListener('keydown', this.handleEsc)
    },

    methods: {
        handleEsc(event) {
            if (event.key === 'Escape') {
                this.reject()
            }
        },
        accept() {
            this.$emit("accept")
            this.$emit("update:show", false)
        },
        reject() {
            this.$emit("reject")
            this.$emit("update:show", false)
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
    width: 56px;
    height: 56px;
    border-radius: 14px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
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
    margin: 0 0 4px 0;
    color: white;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.modal-subtitle {
    font-size: 12px;
    margin: 0;
    opacity: 0.85;
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
    flex-shrink: 0;
    margin-left: 12px;
}

.close-btn:hover {
    background: rgba(255, 255, 255, 0.25);
    transform: rotate(90deg);
}

/* === BODY === */
.modal-body-custom {
    padding: 28px;
    text-align: center;
}

.alert-icon-wrapper {
    width: 72px;
    height: 72px;
    margin: 0 auto 20px;
    border-radius: 50%;
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    color: #d97706;
    font-size: 32px;
    box-shadow: 0 8px 24px rgba(217, 119, 6, 0.2);
}

.modal-description {
    font-size: 15px;
    color: #495057;
    line-height: 1.6;
    margin: 0;
    word-wrap: break-word;
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

.btn-confirm {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
}

.btn-confirm:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
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
        width: 48px;
        height: 48px;
        font-size: 20px;
    }

    .modal-title-text {
        font-size: 17px;
    }

    .modal-body-custom {
        padding: 24px 20px;
    }

    .alert-icon-wrapper {
        width: 60px;
        height: 60px;
        font-size: 26px;
    }

    .modal-description {
        font-size: 14px;
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

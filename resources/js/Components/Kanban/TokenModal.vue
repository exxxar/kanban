<template>
    <Transition name="modal-fade">
        <div v-if="isVisible" class="modal-overlay" @click.self="close" @keydown.esc="close">
            <div class="modal-window">
                <!-- HEADER -->
                <div class="modal-header-custom">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fa-solid fa-key"></i>
                        </div>
                        <div class="header-text">
                            <h3 class="modal-title-text">API Токен</h3>
                            <p class="modal-subtitle">Используйте для интеграции с внешними системами</p>
                        </div>
                    </div>
                    <button class="close-btn" @click="close" title="Закрыть">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- BODY -->
                <div class="modal-body-custom">
                    <!-- Загрузка -->
                    <div v-if="store.loading" class="loading-state">
                        <div class="loading-spinner">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                        </div>
                        <p class="loading-text">Загрузка токена...</p>
                    </div>

                    <!-- Токен -->
                    <div v-else class="token-section">
                        <div class="section-header">
                            <div class="section-icon token-icon">
                                <i class="fa-solid fa-shield-halved"></i>
                            </div>
                            <div>
                                <h4 class="section-title">Ваш токен</h4>
                                <p class="section-desc">Храните его в безопасности</p>
                            </div>
                        </div>

                        <div class="token-display">
                            <div class="token-value-wrapper">
                                <i class="fa-solid fa-fingerprint token-icon-left"></i>
                                <code class="token-value">{{ store.token }}</code>
                            </div>
                            <button class="copy-btn" @click="copyToken" title="Скопировать токен">
                                <i class="fa-solid fa-copy"></i>
                                <span>Копировать</span>
                            </button>
                        </div>

                        <div class="docs-link">
                            <i class="fa-solid fa-book me-2"></i>
                            Документация к API:
                            <a href="https://packagist.org/packages/exxxar/kanban-laravel" target="_blank" class="docs-anchor">
                                packagist.org
                                <i class="fa-solid fa-arrow-up-right-from-square ms-1" style="font-size: 10px;"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Тестирование API -->
                    <div class="test-section">
                        <div class="section-header">
                            <div class="section-icon test-icon">
                                <i class="fa-solid fa-flask"></i>
                            </div>
                            <div>
                                <h4 class="section-title">Тестирование API</h4>
                                <p class="section-desc">Проверьте работу интеграции</p>
                            </div>
                        </div>
                        <TestPanel />
                    </div>
                </div>

                <!-- FOOTER -->
                <div class="modal-footer-custom">
                    <button class="btn-footer btn-close-footer" @click="close">
                        <i class="fa-solid fa-xmark me-2"></i>
                        Закрыть
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script>
import { useAuthTokenStore } from '@/stores/useAuthTokenStore.js'
import { useKanbanStore } from "@/stores/kanban/useKanbanStore.js"
import TestPanel from "@/Components/Kanban/Testing/TestPanel.vue"

export default {
    components: { TestPanel },

    data() {
        return {
            store: useAuthTokenStore(),
            kanbanStore: useKanbanStore(),
            isVisible: false
        }
    },

    mounted() {
        this.$nextTick(() => {
            this.isVisible = true
            this.store.fetchToken(this.kanbanStore.board.uuid)
        })
        document.body.style.overflow = 'hidden'
        document.addEventListener('keydown', this.handleEsc)
    },

    beforeUnmount() {
        document.body.style.overflow = ''
        document.removeEventListener('keydown', this.handleEsc)
    },

    methods: {
        handleEsc(event) {
            if (event.key === 'Escape') {
                this.close()
            }
        },

        close() {
            this.isVisible = false
            document.body.style.overflow = ''
            this.$emit('close')
        },

        copyToken() {
            navigator.clipboard.writeText(this.store.token)
            this.showToast('Токен скопирован в буфер обмена')
        },

        showToast(message) {
            const toast = document.createElement('div')
            toast.className = 'token-toast'
            toast.innerHTML = `<i class="fa-solid fa-check-circle me-2"></i>${message}`
            document.body.appendChild(toast)
            setTimeout(() => toast.classList.add('show'), 10)
            setTimeout(() => {
                toast.classList.remove('show')
                setTimeout(() => toast.remove(), 300)
            }, 2000)
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
    width: 600px;
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

/* === BODY === */
.modal-body-custom {
    padding: 28px;
    overflow-y: auto;
    flex: 1;
}

/* === ЗАГРУЗКА === */
.loading-state {
    text-align: center;
    padding: 48px 20px;
}

.loading-spinner {
    width: 64px;
    height: 64px;
    margin: 0 auto 16px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e7f1ff 0%, #cfe2ff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 28px;
    color: #0d6efd;
}

.loading-text {
    font-size: 15px;
    color: #6c757d;
    margin: 0;
}

/* === СЕКЦИИ === */
.token-section,
.test-section {
    margin-bottom: 28px;
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
    flex-shrink: 0;
    color: white;
}

.token-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.test-icon {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
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

/* === ТОКЕН === */
.token-display {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border: 2px solid #dee2e6;
    border-radius: 12px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
}

.token-value-wrapper {
    flex: 1;
    display: flex;
    align-items: center;
    gap: 12px;
    min-width: 0;
}

.token-icon-left {
    font-size: 20px;
    color: #667eea;
    flex-shrink: 0;
}

.token-value {
    font-family: 'Courier New', monospace;
    font-size: 13px;
    color: #212529;
    background: #ffffff;
    padding: 8px 12px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
    word-break: break-all;
    flex: 1;
    min-width: 0;
}

.copy-btn {
    padding: 10px 20px;
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: white;
    border: none;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    gap: 8px;
    white-space: nowrap;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
}

.copy-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
}

.docs-link {
    font-size: 13px;
    color: #6c757d;
    padding: 12px 16px;
    background: #f8f9fa;
    border-radius: 10px;
    border-left: 3px solid #667eea;
}

.docs-anchor {
    color: #0d6efd;
    text-decoration: none;
    font-weight: 600;
    transition: color 0.2s;
}

.docs-anchor:hover {
    color: #0b5ed7;
    text-decoration: underline;
}

/* === FOOTER === */
.modal-footer-custom {
    padding: 20px 28px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: flex-end;
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

.btn-close-footer {
    background: #ffffff;
    color: #6c757d;
    border: 1px solid #dee2e6;
}

.btn-close-footer:hover {
    background: #f8f9fa;
    color: #495057;
    border-color: #adb5bd;
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

    .token-display {
        flex-direction: column;
        align-items: stretch;
    }

    .copy-btn {
        width: 100%;
        justify-content: center;
    }

    .modal-footer-custom {
        padding: 16px 20px;
        border-radius: 0 0 16px 16px;
    }

    .btn-footer {
        width: 100%;
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

/* === TOAST === */
:global(.token-toast) {
    position: fixed;
    bottom: 24px;
    right: 24px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    padding: 14px 20px;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 500;
    box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s ease;
    z-index: 9999;
    display: flex;
    align-items: center;
}

:global(.token-toast.show) {
    opacity: 1;
    transform: translateY(0);
}
</style>

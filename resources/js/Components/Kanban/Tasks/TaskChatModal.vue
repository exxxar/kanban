<template>
    <Transition name="modal-fade">
        <div v-if="isVisible" class="modal-overlay" @click.self="close" @keydown.esc="close">
            <div class="modal-window">

                <!-- BODY -->
                <div class="modal-body-custom">
                    <CardChat
                        :task-id="task.id">
                        <template #close>
                            <button class="close-btn" @click="close" title="Закрыть">
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </template>
                    </CardChat>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script>
import CardChat from '@/Components/Kanban/Cards/CardChat.vue'

export default {
    components: {
        CardChat
    },
    props: {
        task: Object
    },
    emits: ['close'],
    data() {
        return {
            isVisible: false
        }
    },
    mounted() {
        this.$nextTick(() => {
            this.isVisible = true
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
    padding: 20px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: white;
    flex-shrink: 0;
}

.header-content {
    display: flex;
    align-items: center;
    gap: 14px;
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
    font-size: 18px;
    font-weight: 700;
    margin: 0 0 2px 0;
    color: white;
}

.modal-subtitle {
    font-size: 12px;
    margin: 0;
    opacity: 0.9;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
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
    flex: 1;
    overflow: hidden;
    display: flex;
    flex-direction: column;
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
        padding: 16px 20px;
    }

    .header-icon {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }

    .modal-title-text {
        font-size: 16px;
    }
}
</style>

<template>
    <div class="card-chat">
        <!-- Header чата -->
        <div class="chat-header">
            <div class="chat-header-icon">
                <i class="fa-solid fa-comments"></i>
            </div>
            <div class="chat-header-text">
                <h4 class="chat-title">Чат по задаче</h4>
                <p class="chat-subtitle">
                    {{ messages.length > 0 ? `${messages.length} сообщений` : 'Начните общение' }}
                </p>

            </div>
            <slot name="close"/>
        </div>

        <!-- Область сообщений -->
        <div class="chat-area">
            <!-- Загрузка -->
            <div v-if="chatStore.loading" class="chat-loading">
                <div class="loading-spinner">
                    <i class="fa-solid fa-spinner fa-spin"></i>
                </div>
                <p>Загрузка сообщений...</p>
            </div>

            <!-- История сообщений -->
            <div
                v-else
                ref="chatHistory"
                class="chat-history"
            >
                <template v-if="messages.length > 0">
                    <TransitionGroup name="message-list">
                        <div
                            v-for="msg in messages"
                            :key="msg.id"
                            class="chat-message"
                            :class="bubbleClass(msg.sender_type)"
                        >
                            <!-- Аватар для входящих -->
                            <div v-if="msg.sender_type === 'client'" class="message-avatar">
                                <i class="fa-solid fa-user"></i>
                            </div>

                            <div class="message-content">
                                <!-- Имя отправителя -->
                                <div v-if="msg.sender_label" class="sender-name">
                                    {{ msg.sender_label }}
                                </div>

                                <!-- Bubble -->
                                <div class="bubble">
                                    <div class="message-text" v-html="msg.message"></div>
                                    <div class="message-meta">
                                        <span class="message-time">{{ formatDate(msg.created_at) }}</span>
                                        <i v-if="msg.sender_type === 'manager'" class="fa-solid fa-check-double message-status"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </TransitionGroup>
                </template>

                <!-- Пустое состояние -->
                <div v-else class="empty-chat">
                    <div class="empty-icon">
                        <i class="fa-regular fa-comment-dots"></i>
                    </div>
                    <h5>Сообщений ещё нет</h5>
                    <p>Начните общение по этой задаче</p>
                </div>
            </div>
        </div>

        <!-- Форма отправки -->
        <form @submit.prevent="sendMessage" class="chat-form">
            <div class="input-wrapper">
                <i class="fa-solid fa-paper-plane input-icon"></i>
                <input
                    v-model="newMessage"
                    type="text"
                    class="chat-input"
                    placeholder="Введите сообщение..."
                    :disabled="chatStore.loading"
                />
                <button
                    class="send-btn"
                    type="submit"
                    :disabled="!newMessage.trim() || chatStore.loading"
                    title="Отправить"
                >
                    <span v-if="chatStore.loading" class="spinner-small"></span>
                    <i v-else class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</template>

<script>
import { useChatStore } from '@/stores/chat'

export default {
    props: {
        taskId: Number
    },

    data() {
        return {
            chatStore: useChatStore(),
            newMessage: ''
        }
    },

    computed: {
        messages() {
            return this.chatStore.messages || []
        }
    },

    mounted() {
        this.chatStore.loadMessages(this.taskId)
    },

    methods: {
        formatDate(dateString) {
            const date = new Date(dateString)
            const now = new Date()
            const diff = now - date
            const hours = diff / (1000 * 60 * 60)

            // Если сообщение было сегодня — показываем только время
            if (hours < 24 && date.getDate() === now.getDate()) {
                return new Intl.DateTimeFormat('ru-RU', {
                    hour: '2-digit',
                    minute: '2-digit'
                }).format(date)
            }

            // Иначе — полную дату
            return new Intl.DateTimeFormat('ru-RU', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            }).format(date)
        },

        bubbleClass(type) {
            switch (type) {
                case 'manager':
                    return 'from-me'
                case 'system':
                    return 'system-msg'
                case 'client':
                    return 'from-them'
                default:
                    return 'from-them'
            }
        },

        scrollToBottom() {
            const container = this.$refs.chatHistory
            if (container) {
                container.scrollTo({
                    top: container.scrollHeight,
                    behavior: 'smooth'
                })
            }
        },

        async sendMessage() {
            if (!this.newMessage.trim()) return

            await this.chatStore.sendMessage(this.newMessage)
            this.newMessage = ''
            await this.$nextTick(() => this.scrollToBottom())
        }
    },

    updated() {
        this.scrollToBottom()
    }
}
</script>

<style scoped>
.card-chat {
    display: flex;
    flex-direction: column;
    height: 100%;
    min-height: 400px;
}

/* === HEADER === */
.chat-header {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 16px 20px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 12px 12px 0 0;
    color: white;
    justify-content: space-between;
}

.chat-header-icon {
    width: 44px;
    height: 44px;
    border-radius: 12px;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    backdrop-filter: blur(10px);
}

.chat-title {
    font-size: 16px;
    font-weight: 700;
    margin: 0 0 2px 0;
    color: white;
}

.chat-subtitle {
    font-size: 12px;
    margin: 0;
    opacity: 0.9;
}

/* === ОБЛАСТЬ СООБЩЕНИЙ === */
.chat-area {
    flex: 1;
    display: flex;
    flex-direction: column;
    background: #f8f9fa;
    min-height: 300px;
}

.chat-history {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

/* === ЗАГРУЗКА === */
.chat-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 48px 20px;
    color: #6c757d;
}

.loading-spinner {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e7f1ff 0%, #cfe2ff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    color: #0d6efd;
    margin-bottom: 12px;
}

.chat-loading p {
    font-size: 14px;
    margin: 0;
}

/* === СООБЩЕНИЯ === */
.chat-message {
    display: flex;
    gap: 10px;
    animation: messageSlideIn 0.3s ease;
}

@keyframes messageSlideIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.chat-message.from-me {
    justify-content: flex-end;
}

.chat-message.from-them {
    justify-content: flex-start;
}

.chat-message.system-msg {
    justify-content: center;
}

/* Аватар */
.message-avatar {
    width: 36px;
    height: 36px;
    border-radius: 50%;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

/* Контент сообщения */
.message-content {
    max-width: 70%;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.from-me .message-content {
    align-items: flex-end;
}

.from-them .message-content {
    align-items: flex-start;
}

.system-msg .message-content {
    align-items: center;
    max-width: 90%;
}

/* Имя отправителя */
.sender-name {
    font-size: 11px;
    font-weight: 600;
    color: #6c757d;
    padding: 0 8px;
}

/* Bubble */
.bubble {
    padding: 10px 14px;
    border-radius: 16px;
    position: relative;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.from-me .bubble {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: white;
    border-bottom-right-radius: 4px;
}

.from-them .bubble {
    background: #ffffff;
    color: #212529;
    border: 1px solid #e9ecef;
    border-bottom-left-radius: 4px;
}

.system-msg .bubble {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 500;
}

.message-text {
    font-size: 14px;
    line-height: 1.5;
    word-break: break-word;
}

.message-meta {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 4px;
}

.message-time {
    font-size: 10px;
    opacity: 0.7;
}

.message-status {
    font-size: 10px;
    opacity: 0.8;
}

/* === ПУСТОЕ СОСТОЯНИЕ === */
.empty-chat {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 48px 20px;
    text-align: center;
    color: #adb5bd;
}

.empty-icon {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    background: linear-gradient(135deg, #e7f1ff 0%, #cfe2ff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: #0d6efd;
    margin-bottom: 16px;
    opacity: 0.6;
}

.empty-chat h5 {
    font-size: 16px;
    font-weight: 600;
    color: #495057;
    margin: 0 0 4px 0;
}

.empty-chat p {
    font-size: 13px;
    margin: 0;
    color: #6c757d;
}

/* === ФОРМА ОТПРАВКИ === */
.chat-form {
    padding: 16px 20px;
    background: #ffffff;
    border-top: 1px solid #e9ecef;
    border-radius: 0 0 12px 12px;
}

.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.2s;
}

.input-wrapper:focus-within {
    border-color: #0d6efd;
    background: #ffffff;
    box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
}

.input-icon {
    position: absolute;
    left: 14px;
    color: #adb5bd;
    font-size: 14px;
    pointer-events: none;
}

.chat-input {
    flex: 1;
    padding: 12px 14px 12px 42px;
    border: none;
    background: transparent;
    font-size: 14px;
    outline: none;
    color: #212529;
}

.chat-input::placeholder {
    color: #adb5bd;
}

.chat-input:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.send-btn {
    width: 44px;
    height: 44px;
    margin: 2px;
    border: none;
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: white;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
}

.send-btn:hover:not(:disabled) {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
}

.send-btn:active:not(:disabled) {
    transform: scale(0.95);
}

.send-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* === SPINNER === */
.spinner-small {
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

/* === АНИМАЦИИ СООБЩЕНИЙ === */
.message-list-enter-active {
    transition: all 0.3s ease;
}

.message-list-leave-active {
    transition: all 0.2s ease;
}

.message-list-enter-from {
    opacity: 0;
    transform: translateY(20px);
}

.message-list-leave-to {
    opacity: 0;
    transform: translateX(-20px);
}

.message-list-move {
    transition: transform 0.3s ease;
}

/* === СКРОЛЛБАР === */
.chat-history::-webkit-scrollbar {
    width: 6px;
}

.chat-history::-webkit-scrollbar-track {
    background: transparent;
}

.chat-history::-webkit-scrollbar-thumb {
    background: #dee2e6;
    border-radius: 3px;
}

.chat-history::-webkit-scrollbar-thumb:hover {
    background: #adb5bd;
}

/* === АДАПТИВ === */
@media (max-width: 768px) {
    .chat-header {
        padding: 14px 16px;
    }

    .chat-header-icon {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }

    .chat-title {
        font-size: 15px;
    }

    .chat-history {
        padding: 16px;
    }

    .message-content {
        max-width: 85%;
    }

    .chat-form {
        padding: 12px 16px;
    }
}
</style>

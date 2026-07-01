<template>
    <Transition name="modal-fade">
        <div v-if="isVisible" class="modal-overlay" @click.self="close" @keydown.esc="close">
            <div class="modal-window">
                <!-- HEADER -->
                <div class="modal-header-custom">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fa-solid fa-bell"></i>
                        </div>
                        <div class="header-text">
                            <h3 class="modal-title-text">Уведомления колонки</h3>
                            <p class="modal-subtitle">{{ column?.title || 'Настройка уведомлений' }}</p>
                        </div>
                    </div>
                    <button class="close-btn" @click="close" title="Закрыть">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- BODY -->
                <div class="modal-body-custom">

                    <!-- СЕКЦИЯ: Общие настройки -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon main-icon">
                                <i class="fa-solid fa-toggle-on"></i>
                            </div>
                            <div>
                                <h4 class="section-title">Общие настройки</h4>
                                <p class="section-desc">Включение уведомлений для колонки</p>
                            </div>
                        </div>

                        <div class="switch-card">
                            <label class="custom-switch">
                                <input type="checkbox" v-model="local.enabled" class="switch-input">
                                <span class="switch-slider"></span>
                                <div class="switch-content">
                                    <span class="switch-label">Включить уведомления</span>
                                    <span class="switch-hint">Все уведомления будут отправляться по настроенным каналам</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- СЕКЦИЯ: Email -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon email-icon">
                                <i class="fa-solid fa-envelope"></i>
                            </div>
                            <div>
                                <h4 class="section-title">Email уведомления</h4>
                                <p class="section-desc">Отправка уведомлений на почту</p>
                            </div>
                        </div>

                        <div class="switch-card mb-3">
                            <label class="custom-switch">
                                <input type="checkbox" v-model="local.email.enabled" class="switch-input">
                                <span class="switch-slider"></span>
                                <span class="switch-label">Email уведомления</span>
                            </label>
                        </div>

                        <Transition name="expand">
                            <div v-if="local.email.enabled" class="channels-section">
                                <!-- Список email -->
                                <div class="channels-list">
                                    <div
                                        v-for="(email, index) in local.email.to"
                                        :key="'email-' + index"
                                        class="channel-item"
                                    >
                                        <div class="input-wrapper flex-grow">
                                            <i class="fa-solid fa-at input-icon"></i>
                                            <input
                                                type="email"
                                                class="custom-input"
                                                v-model="local.email.to[index]"
                                                placeholder="email@example.com"
                                            >
                                        </div>
                                        <button
                                            v-if="local.email.to.length > 1"
                                            class="btn-remove"
                                            @click="removeEmail(index)"
                                            title="Удалить"
                                        >
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Кнопки -->
                                <div class="channels-actions">
                                    <button class="btn-action btn-add" @click="addEmail">
                                        <i class="fa-solid fa-plus"></i>
                                        <span>Добавить получателя</span>
                                    </button>
                                    <button class="btn-action btn-test" @click="testEmail">
                                        <i class="fa-solid fa-envelope-circle-check"></i>
                                        <span>Проверить</span>
                                    </button>
                                </div>
                            </div>
                        </Transition>
                    </div>

                    <!-- СЕКЦИЯ: Webhook -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon webhook-icon">
                                <i class="fa-solid fa-plug"></i>
                            </div>
                            <div>
                                <h4 class="section-title">Webhook уведомления</h4>
                                <p class="section-desc">Отправка на внешние сервисы</p>
                            </div>
                        </div>

                        <div class="switch-card mb-3">
                            <label class="custom-switch">
                                <input type="checkbox" v-model="local.webhook.enabled" class="switch-input">
                                <span class="switch-slider"></span>
                                <span class="switch-label">Webhook уведомления</span>
                            </label>
                        </div>

                        <Transition name="expand">
                            <div v-if="local.webhook.enabled" class="channels-section">
                                <!-- Список webhook -->
                                <div class="channels-list">
                                    <div
                                        v-for="(url, index) in local.webhook.urls"
                                        :key="'wh-' + index"
                                        class="channel-item"
                                    >
                                        <div class="input-wrapper flex-grow">
                                            <i class="fa-solid fa-globe input-icon"></i>
                                            <input
                                                type="text"
                                                class="custom-input"
                                                v-model="local.webhook.urls[index]"
                                                placeholder="https://example.com/webhook"
                                            >
                                        </div>
                                        <button
                                            v-if="local.webhook.urls.length > 1"
                                            class="btn-remove"
                                            @click="removeWebhook(index)"
                                            title="Удалить"
                                        >
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Кнопки -->
                                <div class="channels-actions">
                                    <button class="btn-action btn-add" @click="addWebhook">
                                        <i class="fa-solid fa-plus"></i>
                                        <span>Добавить вебхук</span>
                                    </button>
                                    <button class="btn-action btn-test" @click="testWebhook">
                                        <i class="fa-solid fa-plug-circle-check"></i>
                                        <span>Проверить</span>
                                    </button>
                                </div>
                            </div>
                        </Transition>
                    </div>

                    <!-- СЕКЦИЯ: События -->
                    <div class="form-section">
                        <div class="section-header">
                            <div class="section-icon events-icon">
                                <i class="fa-solid fa-bolt"></i>
                            </div>
                            <div>
                                <h4 class="section-title">События для уведомлений</h4>
                                <p class="section-desc">На каких событиях срабатывают уведомления</p>
                            </div>
                        </div>

                        <div class="events-grid">
                            <label
                                v-for="(val, key) in local.events"
                                :key="key"
                                class="event-card"
                            >
                                <input
                                    type="checkbox"
                                    class="event-checkbox"
                                    v-model="local.events[key]"
                                >
                                <div class="event-icon" :class="getEventIconClass(key)">
                                    <i :class="getEventIcon(key)"></i>
                                </div>
                                <div class="event-content">
                                    <div class="event-title">{{ eventLabels[key] }}</div>
                                    <div class="event-desc">{{ getEventDescription(key) }}</div>
                                </div>
                                <div class="event-check">
                                    <i v-if="local.events[key]" class="fa-solid fa-check"></i>
                                </div>
                            </label>
                        </div>
                    </div>

                </div>

                <!-- FOOTER -->
                <div class="modal-footer-custom">
                    <button class="btn-footer btn-cancel" @click="close">
                        <i class="fa-solid fa-xmark me-2"></i>
                        Отмена
                    </button>
                    <button class="btn-footer btn-save" @click="save">
                        <i class="fa-solid fa-check me-2"></i>
                        Сохранить
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script>
export default {
    props: {
        show: Boolean,
        column: Object,
        store: Object
    },
    emits: ['close', 'save'],

    data() {
        return {
            isVisible: false,
            local: {
                enabled: false,
                email: { enabled: false, to: [''] },
                webhook: { enabled: false, urls: [''] },
                events: {
                    card_created: true,
                    card_updated: false,
                    card_moved: true,
                    new_message: true
                }
            },
            eventLabels: {
                card_created: "Создание карточки",
                card_updated: "Изменение карточки",
                card_moved: "Перемещение карточки",
                new_message: "Новое сообщение"
            }
        }
    },

    watch: {
        show: {
            immediate: true,
            handler(val) {
                if (val) {
                    this.isVisible = true
                    document.body.style.overflow = 'hidden'

                    this.$nextTick(() => {
                        if (this.column) {
                            this.initLocalFromColumn()
                        }
                    })
                } else {
                    this.isVisible = false
                    document.body.style.overflow = ''
                }
            }
        }
    },

    methods: {
        initLocalFromColumn() {
            const n = this.column.notifications || {}

            this.local = {
                enabled: n.enabled ?? false,
                email: {
                    enabled: n.email?.enabled ?? false,
                    to: Array.isArray(n.email?.to) && n.email.to.length
                        ? [...n.email.to]
                        : [""]
                },
                webhook: {
                    enabled: n.webhook?.enabled ?? false,
                    urls: Array.isArray(n.webhook?.urls) && n.webhook.urls.length
                        ? [...n.webhook.urls]
                        : [""]
                },
                events: {
                    card_created: n.events?.card_created ?? true,
                    card_updated: n.events?.card_updated ?? false,
                    card_moved: n.events?.card_moved ?? true,
                    new_message: n.events?.new_message ?? true
                }
            }
        },

        close() {
            this.isVisible = false
            document.body.style.overflow = ''
            this.$emit('close')
        },

        // === Email ===
        addEmail() {
            this.local.email.to.push("")
        },
        removeEmail(index) {
            this.local.email.to.splice(index, 1)
        },

        // === Webhook ===
        addWebhook() {
            this.local.webhook.urls.push("")
        },
        removeWebhook(index) {
            this.local.webhook.urls.splice(index, 1)
        },

        // === Тесты ===
        async testWebhook() {
            const urls = this.local.webhook.urls.filter(u => u.trim() !== "")
            if (urls.length === 0) {
                alert('Укажите хотя бы один webhook URL')
                return
            }
            try {
                await this.store.testWebhook({ urls })
                this.showToast('Webhook проверен успешно')
            } catch (e) {
                console.error('Webhook test error:', e)
            }
        },

        async testEmail() {
            const emails = this.local.email.to.filter(e => e.trim() !== "")
            if (emails.length === 0) {
                alert('Укажите хотя бы один email')
                return
            }
            try {
                await this.store.testEmail({ emails })
                this.showToast('Email проверен успешно')
            } catch (e) {
                console.error('Email test error:', e)
            }
        },

        // === Сохранение ===
        save() {
            this.local.email.to = this.local.email.to.filter(e => e.trim() !== "")
            this.local.webhook.urls = this.local.webhook.urls.filter(u => u.trim() !== "")

            if (this.local.email.to.length === 0) this.local.email.to = [""]
            if (this.local.webhook.urls.length === 0) this.local.webhook.urls = [""]

            this.$emit("save", JSON.parse(JSON.stringify(this.local)))
            this.close()
        },

        // === Вспомогательные ===
        getEventIcon(key) {
            const icons = {
                card_created: 'fa-solid fa-plus',
                card_updated: 'fa-solid fa-pen',
                card_moved: 'fa-solid fa-arrow-right-arrow-left',
                new_message: 'fa-solid fa-comment'
            }
            return icons[key] || 'fa-solid fa-circle-info'
        },

        getEventIconClass(key) {
            const classes = {
                card_created: 'icon-success',
                card_updated: 'icon-info',
                card_moved: 'icon-primary',
                new_message: 'icon-purple'
            }
            return classes[key] || 'icon-default'
        },

        getEventDescription(key) {
            const descs = {
                card_created: 'При создании новой карточки',
                card_updated: 'При изменении данных карточки',
                card_moved: 'При перемещении между колонками',
                new_message: 'При поступлении нового сообщения'
            }
            return descs[key] || ''
        },

        showToast(message) {
            const toast = document.createElement('div')
            toast.className = 'notif-toast'
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
    inset: 0;
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
    width: 640px;
    max-width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
    animation: modalSlideUp 0.3s ease;
    overflow: hidden;
}

@keyframes modalSlideUp {
    from { opacity: 0; transform: translateY(30px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

/* === HEADER === */
.modal-header-custom {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 24px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: white;
    flex-shrink: 0;
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

.header-text { flex: 1; min-width: 0; }

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
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
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
    overflow-y: auto;
    flex: 1;
}

/* === СЕКЦИИ === */
.form-section {
    margin-bottom: 28px;
}

.form-section:last-child {
    margin-bottom: 0;
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

.main-icon {
    background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
}

.email-icon {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

.webhook-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.events-icon {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
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

/* === SWITCH === */
.switch-card {
    padding: 14px 16px;
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    transition: all 0.2s;
}

.switch-card:hover {
    border-color: #dee2e6;
}

.custom-switch {
    display: flex;
    align-items: center;
    gap: 14px;
    cursor: pointer;
    width: 100%;
}

.switch-input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.switch-slider {
    position: relative;
    width: 44px;
    height: 24px;
    background: #dee2e6;
    border-radius: 12px;
    flex-shrink: 0;
    transition: all 0.2s;
}

.switch-slider::before {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 20px;
    height: 20px;
    background: white;
    border-radius: 50%;
    transition: all 0.2s;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}

.switch-input:checked + .switch-slider {
    background: #0d6efd;
}

.switch-input:checked + .switch-slider::before {
    left: 22px;
}

.switch-label {
    font-size: 14px;
    font-weight: 600;
    color: #212529;
}

.switch-hint {
    font-size: 11px;
    color: #6c757d;
    margin-top: 2px;
}

.switch-content {
    display: flex;
    flex-direction: column;
    flex: 1;
}

/* === CHANNELS === */
.channels-section {
    animation: expandIn 0.3s ease;
}

@keyframes expandIn {
    from { opacity: 0; max-height: 0; }
    to { opacity: 1; max-height: 1000px; }
}

.channels-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
    margin-bottom: 12px;
}

.channel-item {
    display: flex;
    gap: 8px;
    align-items: center;
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

.btn-remove {
    width: 40px;
    height: 40px;
    border: 1px solid #e9ecef;
    background: #ffffff;
    color: #dc3545;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.btn-remove:hover {
    background: #fff5f5;
    border-color: #fecaca;
}

.channels-actions {
    display: flex;
    gap: 8px;
}

.btn-action {
    flex: 1;
    padding: 10px 16px;
    border: none;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
}

.btn-add {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.2);
}

.btn-add:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

.btn-test {
    background: #f8f9fa;
    color: #495057;
    border: 1px solid #dee2e6;
}

.btn-test:hover {
    background: #e9ecef;
    border-color: #adb5bd;
}

/* === EVENTS === */
.events-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

.event-card {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 14px;
    background: #ffffff;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
}

.event-card:hover {
    border-color: #dee2e6;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
}

.event-checkbox {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.event-icon {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    color: white;
    flex-shrink: 0;
    transition: transform 0.2s;
}

.event-card:hover .event-icon {
    transform: scale(1.05);
}

.icon-success { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
.icon-info { background: linear-gradient(135deg, #0dcaf0 0%, #0aa2c0 100%); }
.icon-primary { background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%); }
.icon-purple { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }
.icon-default { background: linear-gradient(135deg, #6c757d 0%, #495057 100%); }

.event-content {
    flex: 1;
    min-width: 0;
}

.event-title {
    font-size: 13px;
    font-weight: 600;
    color: #212529;
    margin-bottom: 1px;
}

.event-desc {
    font-size: 10px;
    color: #6c757d;
    line-height: 1.3;
}

.event-check {
    width: 22px;
    height: 22px;
    border: 2px solid #dee2e6;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.2s;
    background: #ffffff;
}

.event-checkbox:checked ~ .event-check {
    background: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

.event-card:has(.event-checkbox:checked) {
    border-color: #0d6efd;
    background: #f8f9ff;
}

.event-check i {
    font-size: 10px;
}

/* === FOOTER === */
.modal-footer-custom {
    padding: 20px 28px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: flex-end;
    gap: 12px;
    background: #f8f9fa;
    flex-shrink: 0;
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

.btn-save:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
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

.expand-enter-active,
.expand-leave-active {
    transition: all 0.3s ease;
    overflow: hidden;
}

.expand-enter-from,
.expand-leave-to {
    opacity: 0;
    max-height: 0;
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
        padding: 20px;
    }

    .events-grid {
        grid-template-columns: 1fr;
    }

    .channels-actions {
        flex-direction: column;
    }

    .modal-footer-custom {
        padding: 16px 20px;
        flex-direction: column-reverse;
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
:global(.notif-toast) {
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

:global(.notif-toast.show) {
    opacity: 1;
    transform: translateY(0);
}

.mb-3 { margin-bottom: 12px; }
</style>

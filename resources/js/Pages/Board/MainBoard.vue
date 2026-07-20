<template>
    <!-- Прогресс-бар обновления -->
    <div class="refresh-progress" :style="{ width: progress + '%' }"></div>

    <div class="d-flex flex-column min-vh-100">
        <main class="flex-grow-1 board-main">
            <KanbanBoard :initial-board="board"/>
        </main>

        <!-- Footer -->
        <footer class="text-light py-4 mt-auto">
            <div class="container text-center">
                <div class="footer-tools-wrapper mb-5">
                    <button class="btn-tools-toggle" @click="showTools = !showTools">
                        <i class="fa-solid fa-screwdriver-wrench"></i>
                        <span>{{ showTools ? 'Скрыть инструменты' : 'Инструменты' }}</span>
                        <i class="fa-solid" :class="showTools ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>

                    <Transition name="fade-slide">
                        <div v-show="showTools" class="tools-panel">
                            <!-- Автообновление -->
                            <div class="tool-item">
                                <div class="form-check form-switch">
                                    <input
                                        v-model="need_request_updates"
                                        type="checkbox"
                                        class="form-check-input"
                                        id="needRequestUpdates"
                                    />
                                    <label class="form-check-label text-white" for="needRequestUpdates">
                                        Автообновление доски (раз в минуту)
                                    </label>
                                </div>
                            </div>

                            <!-- Тест Push -->
                            <div class="tool-item">
                                <button
                                    class="btn-test-push-inline"
                                    @click="testPushNotification"
                                    title="Отправить тестовое push-уведомление"
                                >
                                    <i class="fa-solid fa-bell"></i>
                                    <span>Тест Push-уведомлений</span>
                                </button>
                            </div>
                        </div>
                    </Transition>
                </div>


                <!-- Лого -->
                <h2 class="kanbancrm-logo mb-3">
                    <i class="fa-solid fa-layer-group me-2"></i>
                    KanbanCRM
                </h2>

                <p class="mb-1">© 2026 KanbanCRM. Все права защищены.</p>
                <p class="small text-white mb-2">
                    Сделано с <i class="fa-solid fa-heart text-danger"></i> в мире АйТи
                </p>

                <!-- Онлайн + Установка PWA -->
                <div class="footer-actions-row">
                    <OnlineUsers :board-uuid="board.uuid" />

                    <button
                        v-if="canInstallPwa"
                        class="btn-install-pwa"
                        @click="showPwaModal = true"
                        title="Установить приложение"
                    >
                        <i class="fa-solid fa-download"></i>
                        <span>Установить приложение</span>
                    </button>
                </div>



                <!-- Цвет фона -->
                <div class="mt-3 position-relative d-inline-block">
                    <button
                        class="btn btn-secondary rounded-circle"
                        style="width: 40px; height: 40px;"
                        @click="showColorPicker = !showColorPicker"
                        title="Выбрать цвет фона"
                    >
                        <i class="fa-solid fa-palette"></i>
                    </button>
                    <div
                        v-show="showColorPicker"
                        class="position-absolute bg-white p-2 rounded shadow"
                        style="bottom: 50px; left: 50%; transform: translateX(-50%); z-index: 1050; min-width: 260px;"
                    >
                        <ul class="nav nav-pills nav-fill mb-2">
                            <li class="nav-item">
                                <a
                                    class="nav-link py-1 px-2"
                                    style="font-size: 14px; cursor: pointer;"
                                    :class="{active: bgMode === 'solid'}"
                                    @click.prevent="bgMode = 'solid'"
                                >Сплошной</a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link py-1 px-2"
                                    style="font-size: 14px; cursor: pointer;"
                                    :class="{active: bgMode === 'gradient'}"
                                    @click.prevent="bgMode = 'gradient'"
                                >Градиент</a>
                            </li>
                        </ul>
                        <div v-show="bgMode === 'solid'">
                            <ColorPicker v-model:pureColor="solidColor" useType="pure" format="hex" isWidget />
                        </div>
                        <div v-show="bgMode === 'gradient'">
                            <ColorPicker v-model:gradientColor="gradientColor" useType="gradient" format="hex" isWidget />
                        </div>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- === МОДАЛКА ВЫБОРА ШАБЛОНА === -->
    <Transition name="modal-fade">
        <div
            v-if="showTemplateModal"
            class="modal-overlay"
            @click.self="closeTemplateModal"
            @keydown.esc="closeTemplateModal"
        >
            <div class="modal-window template-modal">
                <!-- HEADER -->
                <div class="modal-header-custom">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                        </div>
                        <div class="header-text">
                            <h3 class="modal-title-text">Выберите шаблон доски</h3>
                            <p class="modal-subtitle">Начните с готового решения или подключите существующую доску</p>
                        </div>
                    </div>
                </div>

                <!-- BODY -->
                <div class="modal-body-custom">
                    <!-- Сетка шаблонов -->
                    <div class="template-grid">
                        <button
                            v-for="tpl in templateStore.templates"
                            :key="tpl.id"
                            class="template-card"
                            :class="{ 'has-generation': tpl.hasGeneration }"
                            :disabled="templateStore.loading"
                            @click="selectTemplate(tpl.id)"
                        >
                            <!-- Бейдж генерации -->
                            <div v-if="tpl.hasGeneration" class="generation-badge">
                                <i class="fa-solid fa-database"></i>
                                <span>С данными</span>
                            </div>

                            <div class="template-card-icon">
                                <i :class="['fa-solid', tpl.icon]"></i>
                            </div>
                            <div class="template-card-content">
                                <div class="template-card-title">{{ tpl.title }}</div>
                                <div class="template-card-hint">Нажмите для создания</div>
                            </div>
                            <i class="fa-solid fa-arrow-right template-card-arrow"></i>
                        </button>
                    </div>

                    <!-- Состояние загрузки -->
                    <div v-if="templateStore.loading" class="template-loading">
                        <div class="loading-spinner-large">
                            <i class="fa-solid fa-spinner fa-spin"></i>
                        </div>
                        <p>Создаём вашу доску...</p>
                    </div>

                    <!-- Секция подключения -->
                    <div class="join-section">
                        <div class="join-divider">
                            <span>или</span>
                        </div>

                        <Transition name="fade">
                            <div v-if="!showJoinInput" class="join-trigger">
                                <button class="btn-join" @click="showJoinInput = true">
                                    <i class="fa-solid fa-link"></i>
                                    <span>У меня уже есть доска</span>
                                    <i class="fa-solid fa-arrow-right"></i>
                                </button>
                            </div>

                            <div v-else class="join-form">
                                <div class="join-form-title">
                                    <i class="fa-solid fa-key"></i>
                                    Введите ключ или ссылку
                                </div>
                                <div class="join-input-wrapper">
                                    <i class="fa-solid fa-link join-input-icon"></i>
                                    <input
                                        v-model="joinKey"
                                        type="text"
                                        class="join-input"
                                        placeholder="UUID доски или полная ссылка"
                                        @keyup.enter="handleJoinBoard"
                                        :disabled="loadingJoin"
                                        autofocus
                                    />
                                    <button
                                        class="join-submit-btn"
                                        @click="handleJoinBoard"
                                        :disabled="!joinKey.trim() || loadingJoin"
                                    >
                                        <span v-if="loadingJoin" class="join-spinner"></span>
                                        <i v-else class="fa-solid fa-arrow-right"></i>
                                    </button>
                                </div>
                                <button
                                    class="join-cancel"
                                    @click="showJoinInput = false; joinKey = ''"
                                >
                                    <i class="fa-solid fa-xmark"></i>
                                    Отмена
                                </button>
                            </div>
                        </Transition>
                    </div>
                </div>
            </div>
        </div>
    </Transition>

    <!-- === МОДАЛКА УСТАНОВКИ PWA === -->
    <Transition name="modal-fade">
        <div
            v-if="showPwaModal"
            class="modal-overlay"
            @click.self="closePwaModal"
            @keydown.esc="closePwaModal"
        >
            <div class="modal-window pwa-modal">
                <!-- HEADER -->
                <div class="modal-header-custom pwa-header">
                    <div class="header-content">
                        <div class="header-icon pwa-icon">
                            <i class="fa-solid fa-download"></i>
                        </div>
                        <div class="header-text">
                            <h3 class="modal-title-text">Установить приложение</h3>
                            <p class="modal-subtitle">Работайте быстрее с KanbanCRM</p>
                        </div>
                    </div>
                    <button class="close-btn" @click="closePwaModal" title="Закрыть">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- BODY -->
                <div class="modal-body-custom">
                    <p class="pwa-description">
                        Установите KanbanCRM как приложение и работайте с доской прямо с рабочего стола —
                        быстрее, удобнее, без лишних вкладок.
                    </p>

                    <!-- Преимущества -->
                    <div class="pwa-features">
                        <div class="pwa-feature">
                            <div class="feature-icon feature-speed">
                                <i class="fa-solid fa-bolt"></i>
                            </div>
                            <div class="feature-content">
                                <div class="feature-title">Мгновенный запуск</div>
                                <div class="feature-desc">Открывается быстрее, чем сайт</div>
                            </div>
                        </div>

                        <div class="pwa-feature">
                            <div class="feature-icon feature-offline">
                                <i class="fa-solid fa-wifi"></i>
                            </div>
                            <div class="feature-content">
                                <div class="feature-title">Работа офлайн</div>
                                <div class="feature-desc">Доступ к данным без интернета</div>
                            </div>
                        </div>

                        <div class="pwa-feature">
                            <div class="feature-icon feature-notify">
                                <i class="fa-solid fa-bell"></i>
                            </div>
                            <div class="feature-content">
                                <div class="feature-title">Push-уведомления</div>
                                <div class="feature-desc">Не пропустите важные события</div>
                            </div>
                        </div>

                        <div class="pwa-feature">
                            <div class="feature-icon feature-separate">
                                <i class="fa-solid fa-window-restore"></i>
                            </div>
                            <div class="feature-content">
                                <div class="feature-title">Отдельное окно</div>
                                <div class="feature-desc">Не смешивается с вкладками браузера</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FOOTER -->
                <div class="modal-footer-custom">
                    <button class="btn-footer btn-cancel" @click="closePwaModal">
                        <i class="fa-solid fa-clock me-2"></i>
                        Позже
                    </button>
                    <button class="btn-footer btn-submit" @click="installPWA">
                        <i class="fa-solid fa-download me-2"></i>
                        Установить
                    </button>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script>
import KanbanBoard from '@/Components/Kanban/KanbanBoard.vue'
import { useKanbanStore } from "@/stores/kanban/useKanbanStore.js"
import { useBoardTemplateStore } from "@/stores/useBoardTemplateStore.js"
import { ColorPicker } from "vue3-colorpicker"
import "vue3-colorpicker/style.css"
import OnlineUsers from '@/Components/Kanban/Online/OnlineUsers.vue'

export default {
    props: {
        board: Object,
        vapidPublicKey: String
    },
    components: { KanbanBoard, ColorPicker, OnlineUsers },

    data() {
        return {
            store: useKanbanStore(),
            templateStore: useBoardTemplateStore(),
            progress: 0,
            progressTimer: null,
            refreshTimer: null,
            need_request_updates: false,
            showTools: false,
            // Модалки
            showTemplateModal: false,
            showPwaModal: false,

            // Подключение к доске
            showJoinInput: false,
            joinKey: '',
            loadingJoin: false,

            // Цвет фона
            showColorPicker: false,
            bgMode: (localStorage.getItem('board_bg_color') || '').includes('gradient') ? 'gradient' : 'solid',
            solidColor: (!((localStorage.getItem('board_bg_color') || '').includes('gradient')))
                ? (localStorage.getItem('board_bg_color') || '#4f46e5')
                : '#4f46e5',
            gradientColor: ((localStorage.getItem('board_bg_color') || '').includes('gradient'))
                ? localStorage.getItem('board_bg_color')
                : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
        }
    },

    computed: {
        canInstallPwa() {
            return (
                'serviceWorker' in navigator &&
                'BeforeInstallPromptEvent' in window &&
                !window.matchMedia('(display-mode: standalone)').matches &&
                window.deferredPWAInstall
            )
        }
    },

    watch: {
        solidColor(newVal) {
            if (this.bgMode === 'solid') this.applyBgColor(newVal)
        },
        gradientColor(newVal) {
            if (this.bgMode === 'gradient') this.applyBgColor(newVal)
        },
        bgMode(newMode) {
            if (newMode === 'solid') this.applyBgColor(this.solidColor)
            if (newMode === 'gradient') this.applyBgColor(this.gradientColor)
        },
        'need_request_updates': {
            handler() {
                if (this.need_request_updates) {
                    this.updateTimer()
                } else {
                    this.progress = 0
                    clearInterval(this.progressTimer)
                    clearInterval(this.refreshTimer)
                }
            },
            deep: true,
        }
    },

    async mounted() {
        this.need_request_updates = JSON.parse(localStorage.getItem("need_request_updates") || 'false')

        // Если колонок нет → показываем модалку выбора шаблона
        if (!this.board.columns || this.board.columns.length === 0) {
            await this.templateStore.loadTemplates()
            this.showTemplateModal = true
        }

        // Проверяем возможность установки PWA
        this.checkPwaInstall()

       await this.initPush()

        if (this.need_request_updates) {
            this.updateTimer()
        }

        navigator.serviceWorker.addEventListener('message', (event) => {
            if (event.data?.type === 'request-update'||event.data?.type === 'PUSH') {
                this.updateTimer()
            }
        })
    },

    methods: {
        // === ЦВЕТ ФОНА ===
        applyBgColor(newColor) {
            localStorage.setItem('board_bg_color', newColor)
            document.body.style.setProperty('background', newColor, 'important')
        },

        // === ТАЙМЕР ОБНОВЛЕНИЯ ===
        updateTimer() {
            this.progress = 0
            clearInterval(this.progressTimer)
            clearInterval(this.refreshTimer)

            this.progressTimer = setInterval(() => {
                this.progress += 100 / 600
                if (this.progress >= 100) this.progress = 100
            }, 100)

            this.refreshTimer = setInterval(async () => {
                await this.store.loadBoard(this.board.uuid)
                this.progress = 0
            }, 60000)
        },

        // === ШАБЛОН ===
        async selectTemplate(templateId) {
            await this.templateStore.applyTemplate(this.board.uuid, templateId)
            this.showTemplateModal = false

            await this.store.loadBoard(this.board.uuid)
            window.dispatchEvent(new CustomEvent('select-new-tab'))
        },

        closeTemplateModal() {
            // Не даём закрыть, если колонок нет
            if (!this.board.columns || this.board.columns.length === 0) return
            this.showTemplateModal = false
        },

        // === PWA ===
        checkPwaInstall() {
            if (!('serviceWorker' in navigator) || !('BeforeInstallPromptEvent' in window)) {
                return
            }

            // Уже установлено
            if (window.matchMedia('(display-mode: standalone)').matches) {
                return
            }

            // Отклоняли недавно
            const dismissed = localStorage.getItem('pwa_install_dismissed')
            if (dismissed) {
                const daysSinceDismissed = (Date.now() - parseInt(dismissed)) / (1000 * 60 * 60 * 24)
                if (daysSinceDismissed < 7) return
            }

            // Ждём событие beforeinstallprompt
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault()
                window.deferredPWAInstall = e

                // Показываем модалку через 5 секунд
                setTimeout(() => {
                    this.showPwaModal = true
                }, 5000)
            })
        },
        // === ТЕСТ PUSH-УВЕДОМЛЕНИЙ ===
        async testPushNotification() {
            if (!('serviceWorker' in navigator)) {
                alert('Service Worker не поддерживается в этом браузере')
                return
            }

            try {
                // 1. Ждём готовности Service Worker
                const registration = await navigator.serviceWorker.ready

                // 2. Проверяем или запрашиваем разрешение
                if (Notification.permission !== 'granted') {
                    const permission = await Notification.requestPermission()
                    if (permission !== 'granted') {
                        alert('Пожалуйста, разрешите уведомления в настройках браузера, чтобы протестировать эту функцию.')
                        return
                    }
                }

                // 3. Отправляем сообщение напрямую в Service Worker
                // ⚠️ ВАЖНО: Убедись, что путь к иконке верный для твоего проекта!
                registration.active.postMessage({
                    type: 'TEST_PUSH',
                    title: '🔔 KanbanCRM Тест',
                    body: 'Если ты видишь это в шторке, значит push-уведомления работают идеально! 🚀',
                    icon: '/icons/icon-192x192.png', // Замени на реальный путь к твоей иконке, если он другой
                    url: '/board/#/menu'
                })

                console.log('✅ Тестовое уведомление отправлено в Service Worker')
            } catch (error) {
                console.error('❌ Ошибка при тестировании уведомлений:', error)
                alert('Не удалось отправить тестовое уведомление. Проверь консоль.')
            }
        },
        installPWA() {
            if (window.deferredPWAInstall) {
                window.deferredPWAInstall.prompt()
                window.deferredPWAInstall.userChoice.then((choiceResult) => {
                    if (choiceResult.outcome === 'accepted') {
                        console.log('PWA installed')
                    }
                    window.deferredPWAInstall = null
                })
            }
            this.closePwaModal()
        },

        closePwaModal() {
            this.showPwaModal = false
            localStorage.setItem('pwa_install_dismissed', Date.now().toString())
        },

        // === ПОДКЛЮЧЕНИЕ К ДОСКЕ ===
        async handleJoinBoard() {
            if (!this.joinKey.trim()) return
            this.loadingJoin = true
            try {
                const { data } = await axios.post('/board/join', { key: this.joinKey })
                if (data.redirect_url) window.location.href = data.redirect_url
            } catch (e) {
                alert('Не удалось подключиться. Проверьте ключ или ссылку.')
            } finally {
                this.loadingJoin = false
            }
        },

        // === PUSH ===
        async initPush() {
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                console.warn('Push notifications not supported')
                return
            }

            try {
                // 1. Регистрируем SW
                const registration = await navigator.serviceWorker.register('/sw.js')
                await navigator.serviceWorker.ready
                console.log('Service Worker ready:', registration)

                // 2. Проверяем, есть ли уже активная подписка
                let subscription = await registration.pushManager.getSubscription()

                // 3. Если подписки нет, запрашиваем разрешение и создаём новую
                if (!subscription) {
                    const permission = await Notification.requestPermission()
                    if (permission !== 'granted') {
                        console.warn('User denied notifications')
                        return
                    }

                    // 🔥 КРИТИЧЕСКИ ВАЖНО: Конвертируем Base64 VAPID ключ в Uint8Array
                    const vapidKey = this.vapidPublicKey.replace(/-/g, '+').replace(/_/g, '/')
                    const padLength = 4 - (vapidKey.length % 4)
                    const paddedKey = vapidKey + '='.repeat(padLength)
                    const binaryString = atob(paddedKey)
                    const uint8Array = new Uint8Array(binaryString.length)
                    for (let i = 0; i < binaryString.length; i++) {
                        uint8Array[i] = binaryString.charCodeAt(i)
                    }

                    // Создаём подписку
                    subscription = await registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: uint8Array
                    })

                    console.log('New push subscription created')
                } else {
                    console.log('Push subscription already exists')
                }

                // 4. Отправляем подписку на сервер (даже если она старая, сервер обновит её)
                await axios.post('/api/push/subscribe', {
                    subscription: subscription.toJSON(),
                    board_uuid: this.board.uuid
                })

                console.log('Push subscription synced with server')
            } catch (error) {
                console.error('Push initialization failed:', error)
            }
        }
    }
}
</script>

<style scoped>
/* === ПРОГРЕСС-БАР === */
.refresh-progress {
    position: fixed;
    top: 0;
    left: 0;
    height: 4px;
    background: linear-gradient(90deg, #667eea 0%, #764ba2 25%, #f093fb 50%, #764ba2 75%, #667eea 100%);
    background-size: 200% 100%;
    animation: gradientShift 2s ease infinite;
    box-shadow: 0 0 10px rgba(102, 126, 234, 0.5), 0 0 20px rgba(118, 75, 162, 0.3);
    transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 9999;
    overflow: hidden;
}

.refresh-progress::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent 0%, rgba(255, 255, 255, 0.4) 50%, transparent 100%);
    animation: wave 1.5s ease-in-out infinite;
}

.refresh-progress::after {
    content: '';
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 20px;
    height: 20px;
    background: radial-gradient(circle, rgba(255, 255, 255, 0.8) 0%, rgba(240, 147, 251, 0.6) 50%, transparent 100%);
    border-radius: 50%;
    filter: blur(4px);
    animation: glow 1s ease-in-out infinite alternate;
}

@keyframes gradientShift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}

@keyframes wave {
    0% { left: -100%; }
    100% { left: 100%; }
}

@keyframes glow {
    0% { opacity: 0.5; transform: translateY(-50%) scale(1); }
    100% { opacity: 1; transform: translateY(-50%) scale(1.2); }
}

/* === OVERLAY === */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(6px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1050;
    padding: 10px;
}

/* === МОДАЛЬНОЕ ОКНО === */
.modal-window {
    background: #ffffff;
    border-radius: 20px;
    max-width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
    animation: modalSlideUp 0.3s ease;
    overflow: hidden;
}

.template-modal { width: 720px; }
.pwa-modal { width: 560px; }

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

.pwa-header {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
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

.pwa-icon {
    background: rgba(255, 255, 255, 0.25);
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
    padding: 24px 28px;
    overflow-y: auto;
    flex: 1;
}

/* === ШАБЛОНЫ === */
.template-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

@media (min-width: 640px) {
    .template-grid {
        grid-template-columns: repeat(3, 1fr);
    }
}

.template-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    padding: 20px 16px;
    border: 2px solid #e9ecef;
    background: #ffffff;
    border-radius: 14px;
    cursor: pointer;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    text-align: center;
    position: relative;
    overflow: hidden;
}

.template-card::before {
    content: '';
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    opacity: 0;
    transition: opacity 0.25s;
    z-index: 0;
}

.template-card > * {
    position: relative;
    z-index: 1;
}

.template-card:hover:not(:disabled) {
    border-color: #667eea;
    transform: translateY(-3px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.2);
}

.template-card:hover:not(:disabled)::before {
    opacity: 0.05;
}

.template-card:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.template-card-icon {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    background: linear-gradient(135deg, #f3f0ff 0%, #ede9fe 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: #7c3aed;
    transition: all 0.25s;
}

.template-card:hover:not(:disabled) .template-card-icon {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    transform: scale(1.08) rotate(-5deg);
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
}

.template-card-content {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.template-card-title {
    font-size: 13px;
    font-weight: 700;
    color: #212529;
    line-height: 1.3;
}

.template-card-hint {
    font-size: 10px;
    color: #adb5bd;
    opacity: 0;
    transition: opacity 0.25s;
}

.template-card:hover:not(:disabled) .template-card-hint {
    opacity: 1;
    color: #667eea;
}

.template-card-arrow {
    position: absolute;
    top: 12px;
    right: 12px;
    font-size: 11px;
    color: #adb5bd;
    opacity: 0;
    transform: translateX(-6px);
    transition: all 0.25s;
}

.template-card:hover:not(:disabled) .template-card-arrow {
    opacity: 1;
    transform: translateX(0);
    color: #667eea;
}

/* Загрузка шаблона */
.template-loading {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    padding: 32px 20px;
    color: #6c757d;
}

.loading-spinner-large {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f3f0ff 0%, #ede9fe 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: #7c3aed;
    animation: spin 0.8s linear infinite;
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

.template-loading p {
    font-size: 14px;
    margin: 0;
    font-weight: 500;
}

/* === СЕКЦИЯ ПОДКЛЮЧЕНИЯ === */
.join-section {
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}

.join-divider {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 16px;
    color: #adb5bd;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.join-divider::before,
.join-divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e9ecef;
}

.join-trigger {
    display: flex;
    justify-content: center;
}

.btn-join {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 24px;
    border: 2px dashed #dee2e6;
    background: #ffffff;
    color: #495057;
    border-radius: 12px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 600;
    transition: all 0.25s;
}

.btn-join:hover {
    border-color: #667eea;
    color: #667eea;
    background: #f8f9ff;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.1);
}

.btn-join i:first-child {
    color: #667eea;
}

.btn-join i:last-child {
    font-size: 12px;
    opacity: 0.6;
    transition: transform 0.25s;
}

.btn-join:hover i:last-child {
    transform: translateX(4px);
    opacity: 1;
}

/* Форма подключения */
.join-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 16px;
    background: #f8f9fa;
    border-radius: 12px;
}

.join-form-title {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
    color: #495057;
}

.join-form-title i {
    color: #667eea;
}

.join-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.join-input-icon {
    position: absolute;
    left: 14px;
    color: #adb5bd;
    font-size: 13px;
    pointer-events: none;
}

.join-input {
    width: 100%;
    padding: 12px 60px 12px 40px;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    font-size: 14px;
    outline: none;
    transition: all 0.2s;
    background: #ffffff;
}

.join-input:focus {
    border-color: #667eea;
    box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
}

.join-submit-btn {
    position: absolute;
    right: 6px;
    width: 44px;
    height: 40px;
    border: none;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.join-submit-btn:hover:not(:disabled) {
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
}

.join-submit-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.join-spinner {
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

.join-cancel {
    align-self: flex-start;
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border: none;
    background: transparent;
    color: #6c757d;
    border-radius: 6px;
    cursor: pointer;
    font-size: 12px;
    font-weight: 500;
    transition: all 0.2s;
}

.join-cancel:hover {
    background: #e9ecef;
    color: #dc3545;
}

/* === PWA === */
.pwa-description {
    font-size: 14px;
    color: #495057;
    line-height: 1.6;
    margin: 0 0 20px 0;
}

.pwa-features {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
}

.pwa-feature {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px;
    background: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.2s;
}

.pwa-feature:hover {
    background: #ffffff;
    border-color: #dee2e6;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.feature-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: white;
    flex-shrink: 0;
}

.feature-speed {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
    box-shadow: 0 4px 10px rgba(245, 158, 11, 0.3);
}

.feature-offline {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    box-shadow: 0 4px 10px rgba(13, 110, 253, 0.3);
}

.feature-notify {
    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    box-shadow: 0 4px 10px rgba(239, 68, 68, 0.3);
}

.feature-separate {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
}

.feature-content {
    flex: 1;
    min-width: 0;
}

.feature-title {
    font-size: 13px;
    font-weight: 700;
    color: #212529;
    margin-bottom: 2px;
}

.feature-desc {
    font-size: 11px;
    color: #6c757d;
    line-height: 1.3;
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

.btn-submit {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
}

.btn-submit:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
}

/* === FOOTER ACTIONS ROW === */
.footer-actions-row {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 16px;
    padding: 20px 0;
    flex-wrap: wrap;
}

.btn-install-pwa {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    background: rgba(255, 255, 255, 0.1);
    color: white;
    border-radius: 10px;
    cursor: pointer;
    font-size: 13px;
    font-weight: 600;
    transition: all 0.2s;
    backdrop-filter: blur(10px);
}

.btn-install-pwa:hover {
    background: rgba(255, 255, 255, 0.2);
    border-color: rgba(255, 255, 255, 0.5);
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
}

.btn-install-pwa i {
    font-size: 14px;
}

/* === ЛОГО === */
.kanbancrm-logo {
    font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
    font-weight: 700;
    letter-spacing: 0.04em;
    font-size: 36px;
    background: white;
    -webkit-background-clip: text;
    background-clip: text;
    color: transparent;
    text-transform: none;
}

/* === MAIN === */
.board-main {
    padding: 0;
}

@media (min-width: 768px) {
    .board-main {
        padding: 30px;
    }
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

.fade-enter-active,
.fade-leave-active {
    transition: all 0.25s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
    transform: translateY(-8px);
}

/* === АДАПТИВ === */
@media (max-width: 768px) {
    .modal-window {
        width: 100%;
        max-height: 95vh;
        border-radius: 16px;
    }

    .modal-header-custom {
        padding: 10px;
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
        padding: 10px;
    }

    .template-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 8px;
    }

    .template-card {
        padding: 16px 12px;
    }

    .template-card-icon {
        width: 44px;
        height: 44px;
        font-size: 18px;
    }

    .template-card-title {
        font-size: 12px;
    }

    .pwa-features {
        grid-template-columns: 1fr;
    }

    .modal-footer-custom {
        padding: 16px 10px;
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

/* === КАРТОЧКА С ГЕНЕРАЦИЕЙ === */
.template-card.has-generation {
    border-color: #10b981;
    background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);
}

.template-card.has-generation:hover:not(:disabled) {
    border-color: #059669;
    box-shadow: 0 10px 30px rgba(16, 185, 129, 0.2);
}

/* Бейдж генерации */
.generation-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    display: flex;
    align-items: center;
    gap: 4px;
    padding: 3px 8px;
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border-radius: 6px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    z-index: 2;
}

.generation-badge i {
    font-size: 8px;
}

/* Информация о генерации */
.template-card-generation-info {
    display: flex;
    flex-direction: column;
    gap: 3px;
    margin-top: 6px;
    padding-top: 6px;
    border-top: 1px dashed #e9ecef;
}

.gen-info-item {
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 10px;
    color: #6c757d;
}

.gen-info-item i {
    font-size: 9px;
    color: #10b981;
    width: 12px;
    text-align: center;
}

.gen-info-clients i {
    color: #7c3aed;
}

.gen-info-clients {
    color: #7c3aed;
    font-weight: 600;
}

/* === КНОПКА ТЕСТА УВЕДОМЛЕНИЙ === */
.btn-test-push {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    margin-top: 12px;
    padding: 6px 14px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: rgba(255, 255, 255, 0.7);
    border-radius: 20px;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-test-push:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
    border-color: rgba(255, 255, 255, 0.4);
    transform: translateY(-1px);
}

.btn-test-push:active {
    transform: translateY(0);
}

.btn-test-push i {
    font-size: 12px;
}

/* === FOOTER TOOLS (Сворачиваемый блок) === */
.footer-tools-wrapper {
    margin-top: 16px;
    display: flex;
    flex-direction: column;
    align-items: center;
}

.btn-tools-toggle {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 8px 20px;
    background: rgba(255, 255, 255, 0.1);
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: rgba(255, 255, 255, 0.8);
    border-radius: 20px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-tools-toggle:hover {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
    border-color: rgba(255, 255, 255, 0.4);
}

.tools-panel {
    margin-top: 12px;
    background: rgba(0, 0, 0, 0.25);
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 12px;
    padding: 16px;
    display: flex;
    flex-direction: column;
    gap: 12px;
    width: 100%;
    max-width: 360px;
    backdrop-filter: blur(8px);
}

.tool-item {
    display: flex;
    align-items: center;
    justify-content: center;
}

/* Стили для кнопки теста внутри панели */
.btn-test-push-inline {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    width: 100%;
    padding: 10px 16px;
    background: rgba(255, 255, 255, 0.15);
    border: 1px solid rgba(255, 255, 255, 0.3);
    color: #ffffff;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-test-push-inline:hover {
    background: rgba(255, 255, 255, 0.25);
    border-color: rgba(255, 255, 255, 0.5);
    transform: translateY(-1px);
}

.btn-test-push-inline:active {
    transform: translateY(0);
}

/* Корректировка переключателя для футера */
.tool-item .form-check-label {
    font-size: 13px;
    cursor: pointer;
    user-select: none;
}

/* Анимация для fade-slide (если её ещё нет в твоих стилях) */
.fade-slide-enter-active,
.fade-slide-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.fade-slide-enter-from,
.fade-slide-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>

<template>
    <div class="online-users-widget">
        <!-- Кнопка открытия -->
        <button
            class="online-toggle-btn"
            :class="{ active: isOpen }"
            @click="openModal"
        >
            <div class="online-indicator" :class="{ pulse: onlineCount > 0 }"></div>
            <span class="online-count">{{ onlineCount }}</span>
            <i class="fa-solid fa-users"></i>
        </button>

        <!-- МОДАЛКА -->
        <Transition name="modal-fade">
            <div v-if="isOpen" class="modal-overlay" @click.self="closeModal">
                <div class="modal-window">
                    <!-- HEADER -->
                    <div class="modal-header-custom">
                        <div class="header-content">
                            <div class="header-icon">
                                <i class="fa-solid fa-users"></i>
                            </div>
                            <div class="header-text">
                                <h3 class="modal-title-text">
                                    Пользователи онлайн
                                    <span class="online-badge">{{ onlineCount }}</span>
                                </h3>
                                <p class="modal-subtitle">
                                    {{ onlineCount > 0
                                    ? `Активных пользователей: ${onlineCount}`
                                    : 'Пока никого нет'
                                    }}
                                </p>
                            </div>
                        </div>
                        <button class="close-btn" @click="closeModal" title="Закрыть">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>

                    <!-- STATS -->
                    <div v-if="onlineCount > 0" class="stats-section">
                        <div class="stat-item">
                            <div class="stat-icon desktop">
                                <i class="fa-solid fa-desktop"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value">{{ deviceStats.desktop }}</div>
                                <div class="stat-label">Десктоп</div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon mobile">
                                <i class="fa-solid fa-mobile-screen"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value">{{ deviceStats.mobile }}</div>
                                <div class="stat-label">Мобильные</div>
                            </div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-icon tablet">
                                <i class="fa-solid fa-tablet-screen-button"></i>
                            </div>
                            <div class="stat-info">
                                <div class="stat-value">{{ deviceStats.tablet }}</div>
                                <div class="stat-label">Планшеты</div>
                            </div>
                        </div>
                    </div>

                    <!-- BODY -->
                    <div class="modal-body-custom">
                        <!-- Поиск -->
                        <div v-if="onlineUsers.length > 0" class="search-section">
                            <div class="input-wrapper">
                                <i class="fa-solid fa-magnifying-glass input-icon"></i>
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    class="custom-input"
                                    placeholder="Поиск по IP, браузеру или ОС..."
                                >
                            </div>
                        </div>

                        <!-- Список пользователей -->
                        <div v-if="filteredUsers.length > 0" class="users-list">
                            <TransitionGroup name="user-list">
                                <div
                                    v-for="user in filteredUsers"
                                    :key="user.session_id"
                                    class="user-card"
                                >
                                    <!-- Иконка устройства -->
                                    <div class="user-icon" :class="user.device_type">
                                        <i :class="getDeviceIcon(user.device_type)"></i>
                                    </div>

                                    <!-- Информация -->
                                    <div class="user-info">
                                        <div class="user-main">
                                            <span class="user-browser">{{ user.browser }}</span>
                                            <span class="user-separator">•</span>
                                            <span class="user-os">{{ user.os }}</span>
                                        </div>
                                        <div class="user-meta">
                                            <span class="user-ip">
                                                <i class="fa-solid fa-network-wired"></i>
                                                {{ user.ip_address }}
                                            </span>
                                            <span v-if="user.screen_resolution" class="user-resolution">
                                                <i class="fa-solid fa-display"></i>
                                                {{ user.screen_resolution }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Время -->
                                    <div class="user-time">
                                        <div class="time-value">{{ user.last_seen_at }}</div>
                                        <div class="time-indicator" :class="getTimeClass(user.last_seen_timestamp)"></div>
                                    </div>
                                </div>
                            </TransitionGroup>
                        </div>

                        <!-- Пустое состояние -->
                        <div v-else-if="searchQuery" class="empty-state search-empty">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <p>Ничего не найдено</p>
                            <p class="empty-hint">Попробуйте изменить запрос</p>
                        </div>
                        <div v-else class="empty-state">
                            <div class="empty-icon">
                                <i class="fa-regular fa-user"></i>
                            </div>
                            <p>Пока никого нет</p>
                            <p class="empty-hint">Пользователи появятся здесь, когда зайдут на доску</p>
                        </div>
                    </div>

                    <!-- FOOTER -->
                    <div class="modal-footer-custom">
                        <div class="footer-info">
                            <i class="fa-solid fa-rotate"></i>
                            <span>Обновление каждые 30 сек</span>
                        </div>
                        <button class="btn-refresh" @click="fetchOnlineUsers" :disabled="isRefreshing">
                            <i :class="isRefreshing ? 'fa-solid fa-spinner fa-spin' : 'fa-solid fa-arrows-rotate'"></i>
                            <span>{{ isRefreshing ? 'Обновление...' : 'Обновить' }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    </div>
</template>

<script>
export default {
    props: {
        boardUuid: { type: String, required: true }
    },

    data() {
        return {
            isOpen: false,
            onlineUsers: [],
            onlineCount: 0,
            sessionId: null,
            heartbeatInterval: null,
            pollInterval: null,
            searchQuery: '',
            isRefreshing: false
        }
    },

    computed: {
        filteredUsers() {
            if (!this.searchQuery.trim()) {
                return this.onlineUsers
            }

            const query = this.searchQuery.toLowerCase()
            return this.onlineUsers.filter(user =>
                user.ip_address.toLowerCase().includes(query) ||
                user.browser.toLowerCase().includes(query) ||
                user.os.toLowerCase().includes(query)
            )
        },

        deviceStats() {
            return {
                desktop: this.onlineUsers.filter(u => u.device_type === 'desktop').length,
                mobile: this.onlineUsers.filter(u => u.device_type === 'mobile').length,
                tablet: this.onlineUsers.filter(u => u.device_type === 'tablet').length
            }
        }
    },

    mounted() {
        this.initSession()
        this.startHeartbeat()
        this.startPolling()
    },

    beforeUnmount() {
        this.stopHeartbeat()
        this.stopPolling()
    },

    methods: {
        // === МОДАЛКА ===
        openModal() {
            this.isOpen = true
            document.body.style.overflow = 'hidden'
            this.fetchOnlineUsers()
        },

        closeModal() {
            this.isOpen = false
            document.body.style.overflow = ''
            this.searchQuery = ''
        },

        // === СЕССИЯ ===
        initSession() {
            this.sessionId = localStorage.getItem('board_session_id')

            if (!this.sessionId) {
                this.sessionId = this.generateSessionId()
                localStorage.setItem('board_session_id', this.sessionId)
            }
        },

        generateSessionId() {
            const timestamp = Date.now().toString(36)
            const random = Math.random().toString(36).substring(2, 15)
            return `${timestamp}-${random}`
        },

        // === HEARTBEAT ===
        async sendHeartbeat() {
            try {
                await axios.post('/api/online/heartbeat', {
                    board_uuid: this.boardUuid,
                    session_id: this.sessionId,
                    screen_resolution: `${window.screen.width}x${window.screen.height}`,
                    canvas_hash: await this.generateCanvasHash()
                })
            } catch (error) {
                console.error('Heartbeat error:', error)
            }
        },

        // === ПОЛУЧЕНИЕ ОНЛАЙН ===
        async fetchOnlineUsers() {
            this.isRefreshing = true
            try {
                const response = await axios.get(`/api/online/${this.boardUuid}`)
                this.onlineUsers = response.data.users
                this.onlineCount = response.data.count
            } catch (error) {
                console.error('Fetch online error:', error)
            } finally {
                this.isRefreshing = false
            }
        },

        // === CANVAS FINGERPRINT ===
        async generateCanvasHash() {
            try {
                const canvas = document.createElement('canvas')
                const ctx = canvas.getContext('2d')
                ctx.textBaseline = 'top'
                ctx.font = '14px Arial'
                ctx.fillText('fingerprint', 2, 2)

                const dataUrl = canvas.toDataURL()
                const hash = await this.simpleHash(dataUrl)
                return hash
            } catch (e) {
                return null
            }
        },

        async simpleHash(str) {
            const encoder = new TextEncoder()
            const data = encoder.encode(str)
            const hashBuffer = await crypto.subtle.digest('SHA-256', data)
            const hashArray = Array.from(new Uint8Array(hashBuffer))
            return hashArray.map(b => b.toString(16).padStart(2, '0')).join('')
        },

        // === ИНТЕРВАЛЫ ===
        startHeartbeat() {
            this.sendHeartbeat()
            this.heartbeatInterval = setInterval(() => {
                this.sendHeartbeat()
            }, 30000)
        },

        stopHeartbeat() {
            if (this.heartbeatInterval) {
                clearInterval(this.heartbeatInterval)
            }
        },

        startPolling() {
            this.fetchOnlineUsers()
            this.pollInterval = setInterval(() => {
                this.fetchOnlineUsers()
            }, 30000)
        },

        stopPolling() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval)
            }
        },

        // === УТИЛИТЫ ===
        getDeviceIcon(deviceType) {
            const icons = {
                desktop: 'fa-solid fa-desktop',
                mobile: 'fa-solid fa-mobile-screen',
                tablet: 'fa-solid fa-tablet-screen-button'
            }
            return icons[deviceType] || 'fa-solid fa-desktop'
        },

        getTimeClass(timestamp) {
            const now = Date.now() / 1000
            const diff = now - timestamp

            if (diff < 60) return 'active' // менее минуты
            if (diff < 120) return 'recent' // менее 2 минут
            return 'idle'
        }
    }
}
</script>

<style scoped>
.online-users-widget {
    position: relative;
}

/* === КНОПКА === */
.online-toggle-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 14px;
    border: 1px solid #e9ecef;
    background: #ffffff;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 13px;
    font-weight: 600;
    color: #495057;
}

.online-toggle-btn:hover {
    background: #f8f9fa;
    border-color: #dee2e6;
}

.online-toggle-btn.active {
    background: #e7f1ff;
    border-color: #0d6efd;
    color: #0d6efd;
}

.online-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #adb5bd;
    flex-shrink: 0;
}

.online-indicator.pulse {
    background: #10b981;
    animation: pulse 2s ease-in-out infinite;
}

@keyframes pulse {
    0%, 100% {
        box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
    }
    50% {
        box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
    }
}

.online-count {
    font-weight: 700;
}

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
    display: flex;
    align-items: center;
    gap: 10px;
}

.online-badge {
    background: rgba(255, 255, 255, 0.25);
    padding: 4px 12px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 700;
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

/* === STATS === */
.stats-section {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    padding: 20px 28px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px;
    background: #ffffff;
    border-radius: 10px;
    border: 1px solid #e9ecef;
}

.stat-icon {
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

.stat-icon.desktop {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
}

.stat-icon.mobile {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.stat-icon.tablet {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.stat-info { flex: 1; }

.stat-value {
    font-size: 20px;
    font-weight: 700;
    color: #212529;
    line-height: 1;
}

.stat-label {
    font-size: 11px;
    color: #6c757d;
    margin-top: 2px;
}

/* === BODY === */
.modal-body-custom {
    padding: 20px 28px;
    overflow-y: auto;
    flex: 1;
}

/* === SEARCH === */
.search-section {
    margin-bottom: 16px;
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

.custom-input::placeholder {
    color: #adb5bd;
}

/* === USERS LIST === */
.users-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.user-card {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 14px 16px;
    background: #ffffff;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.2s;
}

.user-card:hover {
    border-color: #dee2e6;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    transform: translateY(-1px);
}

.user-icon {
    width: 44px;
    height: 44px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    color: white;
    flex-shrink: 0;
}

.user-icon.desktop {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
}

.user-icon.mobile {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.user-icon.tablet {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.user-info {
    flex: 1;
    min-width: 0;
}

.user-main {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 4px;
}

.user-browser {
    font-size: 14px;
    font-weight: 600;
    color: #212529;
}

.user-separator {
    color: #dee2e6;
}

.user-os {
    font-size: 13px;
    color: #6c757d;
}

.user-meta {
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 11px;
    color: #6c757d;
}

.user-ip,
.user-resolution {
    display: flex;
    align-items: center;
    gap: 4px;
}

.user-ip i,
.user-resolution i {
    font-size: 9px;
    opacity: 0.7;
}

.user-ip {
    font-family: 'Courier New', monospace;
    font-weight: 500;
}

/* === TIME === */
.user-time {
    display: flex;
    flex-direction: column;
    align-items: flex-end;
    gap: 4px;
    flex-shrink: 0;
}

.time-value {
    font-size: 11px;
    color: #6c757d;
    white-space: nowrap;
}

.time-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.time-indicator.active {
    background: #10b981;
    box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.2);
}

.time-indicator.recent {
    background: #f59e0b;
}

.time-indicator.idle {
    background: #adb5bd;
}

/* === EMPTY STATE === */
.empty-state {
    text-align: center;
    padding: 48px 20px;
    color: #adb5bd;
}

.empty-icon {
    width: 72px;
    height: 72px;
    margin: 0 auto 16px;
    border-radius: 50%;
    background: linear-gradient(135deg, #f3f0ff 0%, #ede9fe 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    color: #8b5cf6;
    opacity: 0.6;
}

.empty-state i {
    font-size: 48px;
    margin-bottom: 12px;
    opacity: 0.5;
}

.empty-state p {
    font-size: 14px;
    margin: 0 0 4px 0;
    color: #6c757d;
}

.empty-hint {
    font-size: 12px !important;
    color: #adb5bd !important;
}

/* === FOOTER === */
.modal-footer-custom {
    padding: 16px 28px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #f8f9fa;
    flex-shrink: 0;
}

.footer-info {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: #6c757d;
}

.footer-info i {
    font-size: 11px;
}

.btn-refresh {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    border: 1px solid #e9ecef;
    background: #ffffff;
    color: #0d6efd;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 13px;
    font-weight: 600;
}

.btn-refresh:hover:not(:disabled) {
    background: #e7f1ff;
    border-color: #0d6efd;
}

.btn-refresh:disabled {
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

.user-list-enter-active {
    transition: all 0.3s ease;
}

.user-list-leave-active {
    transition: all 0.2s ease;
}

.user-list-enter-from {
    opacity: 0;
    transform: translateX(-20px);
}

.user-list-leave-to {
    opacity: 0;
    transform: translateX(20px);
}

.user-list-move {
    transition: transform 0.3s ease;
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

    .stats-section {
        padding: 16px 20px;
        gap: 8px;
    }

    .stat-item {
        padding: 10px;
    }

    .stat-icon {
        width: 36px;
        height: 36px;
        font-size: 14px;
    }

    .stat-value {
        font-size: 18px;
    }

    .modal-body-custom {
        padding: 16px 20px;
    }

    .user-card {
        padding: 12px;
    }

    .user-icon {
        width: 40px;
        height: 40px;
        font-size: 16px;
    }

    .user-browser {
        font-size: 13px;
    }

    .user-meta {
        flex-direction: column;
        align-items: flex-start;
        gap: 4px;
    }

    .modal-footer-custom {
        padding: 12px 20px;
        flex-direction: column;
        gap: 12px;
    }

    .btn-refresh {
        width: 100%;
        justify-content: center;
    }
}
</style>

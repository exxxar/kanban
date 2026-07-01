<template>
    <Transition name="modal-fade">
        <div v-if="isVisible" class="modal-overlay" @click.self="close" @keydown.esc="close">
            <div class="modal-window">
                <!-- HEADER -->
                <div class="modal-header-custom">
                    <div class="header-content">
                        <div class="header-icon">
                            <i class="fa-solid fa-gear"></i>
                        </div>
                        <div class="header-text">
                            <h3 class="modal-title-text">Настройки доски</h3>
                            <p class="modal-subtitle">Управление уведомлениями и интеграциями</p>
                        </div>
                    </div>
                    <button class="close-btn" @click="close" title="Закрыть">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <!-- TABS -->
                <div class="modal-tabs">
                    <button
                        class="tab-btn"
                        :class="{ active: activeTab === 'notifications' }"
                        @click="activeTab = 'notifications'"
                    >
                        <i class="fa-solid fa-bell me-2"></i>
                        Уведомления
                    </button>
                    <button
                        class="tab-btn"
                        :class="{ active: activeTab === 'crm' }"
                        @click="activeTab = 'crm'"
                    >
                        <i class="fa-solid fa-plug me-2"></i>
                        Интеграции CRM
                    </button>
                    <button
                        class="tab-btn"
                        :class="{ active: activeTab === 'fields' }"
                        @click="activeTab = 'fields'"
                    >
                        <i class="fa-solid fa-puzzle-piece me-2"></i>
                        Настройка полей
                    </button>
                    <button
                        class="tab-btn"
                        :class="{ active: activeTab === 'linked' }"
                        @click="activeTab = 'linked'"
                    >
                        <i class="fa-solid fa-link me-2"></i>
                        Связанные доски
                        <span v-if="settings.linked_boards?.length" class="tab-badge">
                            {{ settings.linked_boards.length }}
                        </span>
                    </button>

                    <button
                        class="tab-btn"
                        :class="{ active: activeTab === 'categories' }"
                        @click="activeTab = 'categories'"
                    >
                        <i class="fa-solid fa-tags me-2"></i>
                        Категории
                    </button>

                </div>

                <!-- BODY -->
                <div class="modal-body-custom">

                    <!-- TAB: Уведомления -->
                    <div v-if="activeTab === 'notifications'" class="tab-content">
                        <form @submit.prevent="submit">
                            <!-- Секция: Webhook -->
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-icon webhook-icon">
                                        <i class="fa-solid fa-link"></i>
                                    </div>
                                    <div>
                                        <h4 class="section-title">Webhook</h4>
                                        <p class="section-desc">URL для отправки уведомлений</p>
                                    </div>
                                </div>

                                <div class="input-with-action">
                                    <div class="input-wrapper flex-grow">
                                        <i class="fa-solid fa-globe input-icon"></i>
                                        <input
                                            v-model="settings.webhook_url"
                                            type="text"
                                            class="custom-input"
                                            placeholder="https://example.com/webhook"
                                        />
                                    </div>
                                    <button
                                        :disabled="!settings.webhook_url || boardStore.loading"
                                        @click="testWebhook"
                                        type="button"
                                        class="btn-test"
                                        title="Тестировать webhook"
                                    >
                                        <span v-if="!boardStore.loading">
                                            <i class="fa-solid fa-play"></i>
                                            <span class="btn-text">Тест</span>
                                        </span>
                                        <span v-else class="spinner-small"></span>
                                    </button>
                                </div>

                                <div v-if="boardStore.webhookTestResult" class="test-result">
                                    <i class="fa-solid fa-circle-info"></i>
                                    {{ boardStore.webhookTestResult }}
                                </div>
                            </div>

                            <!-- Секция: Email -->
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-icon email-icon">
                                        <i class="fa-solid fa-envelope"></i>
                                    </div>
                                    <div>
                                        <h4 class="section-title">Email уведомления</h4>
                                        <p class="section-desc">Получайте уведомления на почту</p>
                                    </div>
                                </div>

                                <div class="checkbox-wrapper">
                                    <label class="custom-checkbox">
                                        <input
                                            v-model="settings.need_email_notification"
                                            type="checkbox"
                                            class="checkbox-input"
                                        />
                                        <span class="checkbox-custom">
                                            <i v-if="settings.need_email_notification" class="fa-solid fa-check"></i>
                                        </span>
                                        <span class="checkbox-label">Отправлять уведомления на email</span>
                                    </label>
                                </div>

                                <div v-if="settings.need_email_notification" class="input-with-action">
                                    <div class="input-wrapper flex-grow">
                                        <i class="fa-solid fa-at input-icon"></i>
                                        <input
                                            v-model="settings.email_for_notification"
                                            type="email"
                                            class="custom-input"
                                            placeholder="email@example.com"
                                        />
                                    </div>
                                    <button
                                        :disabled="!settings.email_for_notification || boardStore.loading"
                                        @click="testEmail"
                                        type="button"
                                        class="btn-test"
                                        title="Тестировать email"
                                    >
                                        <span v-if="!boardStore.loading">
                                            <i class="fa-solid fa-play"></i>
                                            <span class="btn-text">Тест</span>
                                        </span>
                                        <span v-else class="spinner-small"></span>
                                    </button>
                                </div>

                                <div v-if="boardStore.emailTestResult" class="test-result">
                                    <i class="fa-solid fa-circle-info"></i>
                                    {{ boardStore.emailTestResult }}
                                </div>
                            </div>

                            <!-- Секция: Безопасность -->
                            <div class="form-section">
                                <div class="section-header">
                                    <div class="section-icon security-icon">
                                        <i class="fa-solid fa-shield-halved"></i>
                                    </div>
                                    <div>
                                        <h4 class="section-title">Безопасность</h4>
                                        <p class="section-desc">Управление ключом сессии</p>
                                    </div>
                                </div>

                                <div class="warning-box">
                                    <i class="fa-solid fa-triangle-exclamation warning-icon"></i>
                                    <div class="warning-content">
                                        <p class="warning-title">Обновление ключа сессии</p>
                                        <p class="warning-text">
                                            При обновлении ключа старый адрес перестанет работать. Все пользователи получат новую ссылку.
                                        </p>
                                    </div>
                                </div>

                                <button
                                    @click="handleRefreshUuid"
                                    type="button"
                                    class="btn-danger-full"
                                    :disabled="boardStore.loading"
                                >
                                    <span v-if="!boardStore.loading">
                                        <i class="fa-solid fa-rotate me-2"></i>
                                        Обновить ключ сессии
                                    </span>
                                    <span v-else class="spinner-small"></span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB: CRM -->
                    <div v-if="activeTab === 'crm'" class="tab-content">
                        <!-- Список CRM -->
                        <div v-if="settings.crm.length > 0" class="crm-list">
                            <div v-for="(crm, index) in settings.crm" :key="index" class="crm-card">
                                <div class="crm-card-header">
                                    <div class="crm-card-icon" :class="crm.type">
                                        <i :class="crm.type === 'amocrm' ? 'fa-solid fa-cloud' : 'fa-solid fa-chart-line'"></i>
                                    </div>
                                    <div class="crm-card-title">
                                        <h5>{{ crm.title }}</h5>
                                        <span class="crm-card-type">{{ crm.type === 'amocrm' ? 'amoCRM' : 'Битрикс24' }}</span>
                                    </div>
                                    <button class="crm-remove-btn" @click="removeCrm(index)" title="Удалить интеграцию">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>

                                <div class="crm-card-body">
                                    <!-- amoCRM -->
                                    <template v-if="crm.type === 'amocrm'">
                                        <div class="form-grid">
                                            <div class="form-group">
                                                <label class="form-label-custom">Домен</label>
                                                <div class="input-wrapper">
                                                    <i class="fa-solid fa-globe input-icon"></i>
                                                    <input v-model="crm.domain" type="text" class="custom-input" placeholder="example.amocrm.com">
                                                </div>
                                            </div>
                                            <div class="form-group">
                                                <label class="form-label-custom">ID интеграции</label>
                                                <div class="input-wrapper">
                                                    <i class="fa-solid fa-fingerprint input-icon"></i>
                                                    <input v-model="crm.client_id" type="text" class="custom-input" placeholder="ID">
                                                </div>
                                            </div>
                                            <div class="form-group full-width">
                                                <label class="form-label-custom">Секрет интеграции</label>
                                                <div class="input-wrapper">
                                                    <i class="fa-solid fa-key input-icon"></i>
                                                    <input v-model="crm.client_secret" type="text" class="custom-input" placeholder="Секретный ключ">
                                                </div>
                                            </div>
                                            <div class="form-group full-width">
                                                <label class="form-label-custom">Токен</label>
                                                <div class="input-wrapper">
                                                    <i class="fa-solid fa-shield input-icon"></i>
                                                    <input v-model="crm.token" type="text" class="custom-input" placeholder="Токен доступа">
                                                </div>
                                            </div>
                                        </div>
                                    </template>

                                    <!-- Bitrix24 -->
                                    <template v-if="crm.type === 'bitrix24'">
                                        <div class="form-group">
                                            <label class="form-label-custom">Webhook URL</label>
                                            <div class="input-wrapper">
                                                <i class="fa-solid fa-link input-icon"></i>
                                                <input v-model="crm.webhook" type="text" class="custom-input" placeholder="https://your-domain.bitrix24.ru/rest/...">
                                            </div>
                                        </div>
                                    </template>

                                    <button
                                        class="btn-test-full"
                                        :disabled="boardStore.loading"
                                        @click="testCrm(crm)"
                                    >
                                        <span v-if="!boardStore.loading">
                                            <i class="fa-solid fa-flask me-2"></i>
                                            Проверить интеграцию
                                        </span>
                                        <span v-else class="spinner-small"></span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Пустое состояние -->
                        <div v-else class="empty-crm">
                            <i class="fa-solid fa-plug"></i>
                            <p>Нет подключенных CRM</p>
                            <p class="empty-hint">Добавьте интеграцию для синхронизации данных</p>
                        </div>

                        <!-- Добавление CRM -->
                        <div class="add-crm-section">
                            <div class="add-crm-dropdown" ref="addCrmDropdown">
                                <button class="btn-add-crm" @click.stop="toggleAddCrmMenu">
                                    <i class="fa-solid fa-plus"></i>
                                    <span>Добавить CRM</span>
                                    <i class="fa-solid fa-chevron-down" :class="{ 'rotated': showAddCrmMenu }"></i>
                                </button>

                                <Transition name="menu-fade">
                                    <div v-if="showAddCrmMenu" class="add-crm-menu">
                                        <button class="menu-item" @click.stop="addCrm('amocrm')">
                                            <div class="menu-item-icon amocrm">
                                                <i class="fa-solid fa-cloud"></i>
                                            </div>
                                            <div class="menu-item-content">
                                                <div class="menu-item-title">amoCRM</div>
                                                <div class="menu-item-desc">Интеграция с amoCRM</div>
                                            </div>
                                        </button>
                                        <button class="menu-item" @click.stop="addCrm('bitrix24')">
                                            <div class="menu-item-icon bitrix24">
                                                <i class="fa-solid fa-chart-line"></i>
                                            </div>
                                            <div class="menu-item-content">
                                                <div class="menu-item-title">Битрикс24</div>
                                                <div class="menu-item-desc">Интеграция с Битрикс24</div>
                                            </div>
                                        </button>
                                    </div>
                                </Transition>
                            </div>
                        </div>

                        <div v-if="boardStore.crmTestResult" class="test-result">
                            <i class="fa-solid fa-circle-info"></i>
                            {{ boardStore.crmTestResult }}
                        </div>
                    </div>

                    <!-- В body, после таба CRM -->
                    <div v-if="activeTab === 'fields'" class="tab-content">
                        <CustomFieldsSettings v-model="settings.custom_fields" />
                    </div>

                    <!-- TAB: СВЯЗАННЫЕ ДОСКИ -->
                    <div v-if="activeTab === 'linked'" class="tab-content">
                        <div class="form-section">
                            <div class="section-header">
                                <div class="section-icon linked-icon">
                                    <i class="fa-solid fa-link"></i>
                                </div>
                                <div>
                                    <h4 class="section-title">Связанные доски</h4>
                                    <p class="section-desc">Быстрое переключение между досками</p>
                                </div>
                            </div>

                            <!-- Список связанных досок -->
                            <div v-if="settings.linked_boards?.length > 0" class="linked-boards-list">
                                <div
                                    v-for="(board, index) in settings.linked_boards"
                                    :key="index"
                                    class="linked-board-card"
                                >
                                    <div class="linked-board-info">
                                        <div class="linked-board-icon">
                                            <i class="fa-solid fa-table-columns"></i>
                                        </div>
                                        <div class="linked-board-content">
                                            <div class="linked-board-title">
                                                {{ board.title || 'Без названия' }}
                                            </div>
                                            <div class="linked-board-url">
                                                <i class="fa-solid fa-link me-1"></i>
                                                {{ board.url }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="linked-board-actions">
                                        <button
                                            class="btn-linked-action btn-open"
                                            @click="openLinkedBoard(board)"
                                            title="Открыть доску"
                                        >
                                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                                        </button>
                                        <button
                                            class="btn-linked-action btn-remove"
                                            @click="removeLinkedBoard(index)"
                                            title="Удалить связь"
                                        >
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Пустое состояние -->
                            <div v-else class="empty-linked">
                                <i class="fa-regular fa-window-restore"></i>
                                <p>Нет связанных досок</p>
                                <p class="empty-hint">Добавьте URL доски для быстрого переключения</p>
                            </div>

                            <!-- Добавление доски -->
                            <div class="add-linked-section">
                                <div class="add-linked-form">
                                    <div class="input-wrapper flex-grow">
                                        <i class="fa-solid fa-link input-icon"></i>
                                        <input
                                            v-model="newLinkedUrl"
                                            type="text"
                                            class="custom-input"
                                            placeholder="https://example.com/board/uuid"
                                            @keyup.enter="addLinkedBoard"
                                        >
                                    </div>
                                    <div class="input-wrapper flex-grow">
                                        <i class="fa-solid fa-heading input-icon"></i>
                                        <input
                                            v-model="newLinkedTitle"
                                            type="text"
                                            class="custom-input"
                                            placeholder="Название (необязательно)"
                                            @keyup.enter="addLinkedBoard"
                                        >
                                    </div>
                                    <button
                                        class="btn-add-linked"
                                        @click="addLinkedBoard"
                                        :disabled="!newLinkedUrl.trim()"
                                    >
                                        <i class="fa-solid fa-plus"></i>
                                        <span>Добавить</span>
                                    </button>
                                </div>
                                <div class="input-hint">
                                    <i class="fa-solid fa-circle-info me-1"></i>
                                    Вставьте ссылку на доску. Название можно указать для удобства.
                                </div>
                            </div>
                        </div>
                    </div>


                    <div v-if="activeTab === 'categories'" class="tab-content">
                        <CustomCategoriesSettings v-model="settings.custom_categories" />
                    </div>
                </div>

                <!-- FOOTER -->
                <div class="modal-footer-custom">
                    <a href="/?new=1" target="_blank" class="btn-footer btn-new-session">
                        <i class="fa-solid fa-arrow-up-right-from-square me-2"></i>
                        Новая сессия
                    </a>
                    <div class="footer-actions">
                        <button class="btn-footer btn-cancel" @click="close">
                            <i class="fa-solid fa-xmark me-2"></i>
                            Отмена
                        </button>
                        <button class="btn-footer btn-save" @click="submit">
                            <i class="fa-solid fa-check me-2"></i>
                            Сохранить
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </Transition>
</template>

<script>
import { useKanbanStore } from "@/stores/useKanbanStore.js"
import CustomFieldsSettings from '@/Components/Kanban/Support/CustomFieldsSettings.vue'
import CustomCategoriesSettings from "@/Components/Kanban/Support/CustomCategoriesSettings.vue";
export default {
    name: 'BoardSettings',

    components:{
        CustomFieldsSettings, CustomCategoriesSettings
    },
    data() {
        return {
            isVisible: false,
            activeTab: 'notifications',
            boardStore: useKanbanStore(),
            showAddCrmMenu: false,

            // Для добавления связанной доски
            newLinkedUrl: '',
            newLinkedTitle: '',

            settings: {
                webhook_url: null,
                email_for_notification: null,
                need_email_notification: false,
                crm: [],
                linked_boards: [],
                custom_fields: [] // ← НОВОЕ
            }
        }
    },

    mounted() {
        const config = this.boardStore.board?.config || {}

        this.settings = {
            webhook_url: config.webhook_url || null,
            email_for_notification: config.email_for_notification || null,
            need_email_notification: config.need_email_notification || false,
            crm: Array.isArray(config.crm) ? config.crm : [],
            linked_boards: Array.isArray(config.linked_boards) ? config.linked_boards : [],
            custom_fields: Array.isArray(config.custom_fields) ? config.custom_fields : []
        }


        if (!Array.isArray(this.settings.crm)) {
            this.settings.crm = []
        }

        if (!Array.isArray(this.settings.linked_boards)) {
            this.settings.linked_boards = []
        }

        this.$nextTick(() => {
            this.isVisible = true
        })

        document.body.style.overflow = 'hidden'
        document.addEventListener('click', this.handleClickOutside)
    },

    beforeUnmount() {
        document.body.style.overflow = ''
        document.removeEventListener('click', this.handleClickOutside)
    },

    methods: {
        close() {
            this.isVisible = false
            document.body.style.overflow = ''
            this.$emit('close')
        },

        handleClickOutside(event) {
            if (this.$refs.addCrmDropdown && !this.$refs.addCrmDropdown.contains(event.target)) {
                this.showAddCrmMenu = false
            }
        },

        toggleAddCrmMenu() {
            this.showAddCrmMenu = !this.showAddCrmMenu
        },

        addCrm(type) {
            this.showAddCrmMenu = false

            if (type === 'amocrm') {
                this.settings.crm.push({
                    type: "amocrm",
                    title: "amoCRM",
                    domain: "",
                    client_id: "",
                    client_secret: "",
                    token: ""
                })
            }

            if (type === 'bitrix24') {
                this.settings.crm.push({
                    type: "bitrix24",
                    title: "Битрикс24",
                    webhook: ""
                })
            }
        },

        removeCrm(index) {
            this.settings.crm.splice(index, 1)
        },

        // === СВЯЗАННЫЕ ДОСКИ ===
        addLinkedBoard() {
            if (!this.newLinkedUrl.trim()) return

            // Извлекаем UUID из URL если возможно
            const uuidMatch = this.newLinkedUrl.match(/[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}/i)
            const uuid = uuidMatch ? uuidMatch[0] : null

            this.settings.linked_boards.push({
                url: this.newLinkedUrl.trim(),
                title: this.newLinkedTitle.trim() || this.extractTitleFromUrl(this.newLinkedUrl),
                uuid: uuid
            })

            this.newLinkedUrl = ''
            this.newLinkedTitle = ''
        },

        removeLinkedBoard(index) {
            this.settings.linked_boards.splice(index, 1)
        },

        openLinkedBoard(board) {
            window.open(board.url, '_blank')
        },

        extractTitleFromUrl(url) {
            // Пытаемся извлечь что-то осмысленное из URL
            try {
                const urlObj = new URL(url)
                const pathParts = urlObj.pathname.split('/').filter(p => p)
                if (pathParts.length > 0) {
                    return pathParts[pathParts.length - 1]
                }
            } catch (e) {
                // Игнорируем ошибки парсинга
            }
            return 'Доска'
        },

        // === ОСТАЛЬНЫЕ МЕТОДЫ ===
        async testWebhook() {
            await this.boardStore.testWebhook({
                url: this.settings.webhook_url || null
            })
        },

        async testEmail() {
            await this.boardStore.testEmail({
                email: this.settings.email_for_notification || null
            })
        },

        async testCrm(crm) {
            await this.boardStore.testCrmIntegration(crm)
        },

        async submit() {
            this.$emit('save', this.settings)
            this.close()
        },

        async handleRefreshUuid() {
            if (!confirm('Вы уверены, что хотите обновить ключ сессии?')) return

            try {
                const data = await this.boardStore.refreshUuid(this.boardStore.board.uuid)
                if (data.redirect_url) {
                    window.location.href = data.redirect_url
                }
            } catch (e) {
                alert('Не удалось обновить ключ сессии')
            }
        }
    }
}
</script>

<style scoped>
/* === TAB BADGE === */
.tab-badge {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 10px;
    font-weight: 700;
    min-width: 20px;
    text-align: center;
    margin-left: 4px;
}

/* === СВЯЗАННЫЕ ДОСКИ === */
.linked-icon {
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.3);
}

.linked-boards-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-bottom: 20px;
}

.linked-board-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    padding: 14px 16px;
    background: #ffffff;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    transition: all 0.2s;
}

.linked-board-card:hover {
    border-color: #8b5cf6;
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.1);
}

.linked-board-info {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
    min-width: 0;
}

.linked-board-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: linear-gradient(135deg, #f3f0ff 0%, #ede9fe 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
    color: #7c3aed;
    flex-shrink: 0;
}

.linked-board-content {
    flex: 1;
    min-width: 0;
}

.linked-board-title {
    font-size: 14px;
    font-weight: 600;
    color: #212529;
    margin-bottom: 2px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.linked-board-url {
    font-size: 11px;
    color: #6c757d;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.linked-board-actions {
    display: flex;
    gap: 6px;
    flex-shrink: 0;
}

.btn-linked-action {
    width: 34px;
    height: 34px;
    border: none;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 13px;
}

.btn-open {
    background: #f3f0ff;
    color: #7c3aed;
}

.btn-open:hover {
    background: #ede9fe;
    transform: translateY(-1px);
}

.btn-remove {
    background: transparent;
    color: #dc3545;
}

.btn-remove:hover {
    background: #fff5f5;
}

.empty-linked {
    text-align: center;
    padding: 48px 20px;
    color: #adb5bd;
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    margin-bottom: 20px;
}

.empty-linked i {
    font-size: 48px;
    margin-bottom: 12px;
    opacity: 0.5;
}

.empty-linked p {
    font-size: 14px;
    margin: 0 0 4px 0;
    color: #6c757d;
}

.empty-hint {
    font-size: 12px !important;
    color: #adb5bd !important;
}

.add-linked-section {
    margin-top: 16px;
}

.add-linked-form {
    display: flex;
    gap: 8px;
    margin-bottom: 8px;
}

.btn-add-linked {
    padding: 10px 20px;
    border: none;
    background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
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
    box-shadow: 0 2px 8px rgba(139, 92, 246, 0.3);
}

.btn-add-linked:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(139, 92, 246, 0.4);
}

.btn-add-linked:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

/* === ОСТАЛЬНЫЕ СТИЛИ (без изменений) === */
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

.modal-window {
    background: #ffffff;
    border-radius: 20px;
    width: 700px;
    max-width: 100%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 80px rgba(0, 0, 0, 0.3);
    animation: modalSlideUp 0.3s ease;
}

@keyframes modalSlideUp {
    from { opacity: 0; transform: translateY(30px) scale(0.95); }
    to { opacity: 1; transform: translateY(0) scale(1); }
}

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

.header-text { flex: 1; }

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

.modal-tabs {
    display: flex;
    gap: 4px;
    padding: 16px 28px 0;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    overflow-x: auto;
}

.tab-btn {
    padding: 10px 20px;
    border: none;
    background: transparent;
    border-radius: 10px 10px 0 0;
    font-size: 14px;
    font-weight: 600;
    color: #6c757d;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    white-space: nowrap;
}

.tab-btn:hover {
    background: #e9ecef;
    color: #495057;
}

.tab-btn.active {
    background: #ffffff;
    color: #0d6efd;
    box-shadow: 0 -2px 8px rgba(0, 0, 0, 0.05);
}

.modal-body-custom {
    padding: 28px;
    overflow-y: auto;
    flex: 1;
}

.tab-content {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.form-section { margin-bottom: 32px; }
.form-section:last-child { margin-bottom: 0; }

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

.webhook-icon {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
}

.email-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
}

.security-icon {
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
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

.input-with-action {
    display: flex;
    gap: 8px;
    margin-bottom: 12px;
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

.checkbox-wrapper { margin-bottom: 12px; }

.custom-checkbox {
    display: flex;
    align-items: center;
    gap: 10px;
    cursor: pointer;
}

.checkbox-input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.checkbox-custom {
    width: 20px;
    height: 20px;
    border: 2px solid #dee2e6;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s;
    background: #ffffff;
    flex-shrink: 0;
}

.checkbox-input:checked + .checkbox-custom {
    background: #0d6efd;
    border-color: #0d6efd;
    color: white;
}

.checkbox-custom i { font-size: 11px; }

.checkbox-label {
    font-size: 14px;
    color: #495057;
    font-weight: 500;
}

.btn-test {
    padding: 10px 20px;
    border: none;
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
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
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
}

.btn-test:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
}

.btn-test:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-text { display: inline; }

.btn-test-full {
    width: 100%;
    padding: 10px 20px;
    border: none;
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
    color: white;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 12px;
    box-shadow: 0 2px 8px rgba(13, 110, 253, 0.3);
}

.btn-test-full:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
}

.btn-test-full:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-danger-full {
    width: 100%;
    padding: 12px 24px;
    border: none;
    background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
    color: white;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3);
}

.btn-danger-full:hover:not(:disabled) {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
}

.btn-danger-full:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.test-result {
    padding: 12px 16px;
    background: #e7f1ff;
    border-left: 3px solid #0d6efd;
    border-radius: 8px;
    font-size: 13px;
    color: #0d6efd;
    display: flex;
    align-items: center;
    gap: 8px;
    margin-top: 8px;
}

.warning-box {
    display: flex;
    gap: 12px;
    padding: 16px;
    background: #fff5f5;
    border: 2px solid #fecaca;
    border-radius: 10px;
    margin-bottom: 16px;
}

.warning-icon {
    font-size: 20px;
    color: #dc3545;
    flex-shrink: 0;
}

.warning-content { flex: 1; }

.warning-title {
    font-size: 14px;
    font-weight: 600;
    color: #dc3545;
    margin: 0 0 4px 0;
}

.warning-text {
    font-size: 13px;
    color: #6c757d;
    margin: 0;
    line-height: 1.5;
}

.crm-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
    margin-bottom: 20px;
}

.crm-card {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    transition: all 0.2s;
}

.crm-card:hover {
    border-color: #dee2e6;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
}

.crm-card-header {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 16px;
    background: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
}

.crm-card-icon {
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

.crm-card-icon.amocrm {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
}

.crm-card-icon.bitrix24 {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.crm-card-title {
    flex: 1;
    min-width: 0;
}

.crm-card-title h5 {
    font-size: 15px;
    font-weight: 600;
    color: #212529;
    margin: 0 0 2px 0;
}

.crm-card-type {
    font-size: 11px;
    color: #6c757d;
}

.crm-remove-btn {
    width: 36px;
    height: 36px;
    border: none;
    background: transparent;
    color: #dc3545;
    border-radius: 8px;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}

.crm-remove-btn:hover {
    background: #fff5f5;
}

.crm-card-body { padding: 16px; }

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 12px;
    margin-bottom: 12px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 6px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-label-custom {
    font-size: 13px;
    font-weight: 600;
    color: #495057;
}

.empty-crm {
    text-align: center;
    padding: 48px 20px;
    color: #adb5bd;
    border: 2px dashed #dee2e6;
    border-radius: 12px;
    margin-bottom: 20px;
}

.empty-crm i {
    font-size: 48px;
    margin-bottom: 12px;
    opacity: 0.5;
}

.empty-crm p {
    font-size: 14px;
    margin: 0 0 4px 0;
    color: #6c757d;
}

.empty-hint {
    font-size: 12px !important;
    color: #adb5bd !important;
}

.add-crm-section { position: relative; }
.add-crm-dropdown { position: relative; }

.btn-add-crm {
    width: 100%;
    padding: 12px 20px;
    border: 2px dashed #dee2e6;
    background: #ffffff;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    color: #6c757d;
    cursor: pointer;
    transition: all 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.btn-add-crm:hover {
    border-color: #0d6efd;
    color: #0d6efd;
    background: #f8f9fa;
}

.btn-add-crm i:last-child {
    font-size: 10px;
    transition: transform 0.2s;
}

.btn-add-crm i.rotated {
    transform: rotate(180deg);
}

.add-crm-menu {
    position: absolute;
    bottom: calc(100% + 8px);
    left: 0;
    right: 0;
    background: #ffffff;
    border-radius: 12px;
    padding: 6px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    border: 1px solid #e9ecef;
    z-index: 100;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.menu-item {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
    padding: 10px 12px;
    border: none;
    background: transparent;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.2s;
    text-align: left;
}

.menu-item:hover {
    background: #f8f9fa;
    transform: translateX(2px);
}

.menu-item-icon {
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

.menu-item-icon.amocrm {
    background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
}

.menu-item-icon.bitrix24 {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.menu-item-content {
    flex-grow: 1;
    min-width: 0;
}

.menu-item-title {
    font-size: 14px;
    font-weight: 600;
    color: #212529;
    margin-bottom: 1px;
}

.menu-item-desc {
    font-size: 11px;
    color: #6c757d;
}

.modal-footer-custom {
    padding: 20px 28px;
    border-top: 1px solid #e9ecef;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 12px;
    background: #f8f9fa;
    border-radius: 0 0 20px 20px;
}

.footer-actions {
    display: flex;
    gap: 12px;
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
    text-decoration: none;
}

.btn-new-session {
    background: #ffffff;
    color: #6c757d;
    border: 1px solid #dee2e6;
}

.btn-new-session:hover {
    background: #f8f9fa;
    color: #495057;
    border-color: #adb5bd;
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

.spinner-small {
    width: 14px;
    height: 14px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top-color: white;
    border-radius: 50%;
    animation: spin 0.6s linear infinite;
}

@keyframes spin { to { transform: rotate(360deg); } }

.modal-fade-enter-active, .modal-fade-leave-active { transition: opacity 0.3s ease; }
.modal-fade-enter-from, .modal-fade-leave-to { opacity: 0; }

.menu-fade-enter-active, .menu-fade-leave-active { transition: all 0.2s ease; }
.menu-fade-enter-from, .menu-fade-leave-to { opacity: 0; transform: translateY(10px); }

.input-hint {
    font-size: 11px;
    color: #6c757d;
    margin-top: 4px;
}

@media (max-width: 768px) {
    .modal-window { width: 100%; max-height: 95vh; border-radius: 16px; }
    .modal-header-custom { padding: 20px; border-radius: 16px 16px 0 0; }
    .header-icon { width: 48px; height: 48px; font-size: 20px; }
    .modal-title-text { font-size: 18px; }
    .modal-tabs { padding: 12px 20px 0; }
    .tab-btn { padding: 8px 16px; font-size: 13px; }
    .modal-body-custom { padding: 20px; }
    .form-grid { grid-template-columns: 1fr; }
    .input-with-action { flex-direction: column; }
    .add-linked-form { flex-direction: column; }
    .btn-test { width: 100%; justify-content: center; }
    .btn-add-linked { width: 100%; justify-content: center; }
    .modal-footer-custom { padding: 16px 20px; flex-direction: column; border-radius: 0 0 16px 16px; }
    .footer-actions { width: 100%; flex-direction: column-reverse; }
    .btn-footer { width: 100%; }
}

.modal-body-custom::-webkit-scrollbar { width: 8px; }
.modal-body-custom::-webkit-scrollbar-track { background: #f1f3f5; border-radius: 4px; }
.modal-body-custom::-webkit-scrollbar-thumb { background: #dee2e6; border-radius: 4px; }
.modal-body-custom::-webkit-scrollbar-thumb:hover { background: #adb5bd; }
</style>

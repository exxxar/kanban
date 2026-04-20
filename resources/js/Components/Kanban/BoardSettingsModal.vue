<template>

    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down">
            <div class="modal-content">

                <!-- HEADER -->
                <div class="modal-header">
                    <h5 class="modal-title">Настройки</h5>
                    <button type="button" class="btn-close" @click="$emit('close')"></button>
                </div>

                <!-- BODY -->
                <div class="modal-body">

                    <!-- Вкладки -->
                    <ul class="nav nav-tabs mb-2">
                        <li class="nav-item">
                            <button
                                class="nav-link"
                                :class="{ active: activeTab === 'notifications' }"
                                @click="activeTab = 'notifications'"
                            >
                                Уведомления
                            </button>
                        </li>

                        <li class="nav-item">
                            <button
                                class="nav-link"
                                :class="{ active: activeTab === 'crm' }"
                                @click="activeTab = 'crm'"
                            >
                                Интеграции CRM
                            </button>
                        </li>
                    </ul>

                    <!-- TAB: Уведомления -->
                    <div v-if="activeTab === 'notifications'">

                        <form @submit.prevent="submit">

                            <!-- Webhook URL -->
                            <div class="input-group mb-2">
                                <div class="form-floating">
                                    <input
                                        v-model="settings.webhook_url"
                                        type="text"
                                        class="form-control"
                                        id="webhookUrl"
                                        placeholder="Webhook URL"
                                    />
                                    <label for="webhookUrl">Webhook URL</label>
                                </div>

                                <button
                                    :disabled="!settings.webhook_url || boardStore.loading"
                                    @click="testWebhook"
                                    type="button"
                                    class="input-group-text btn btn-outline-primary"
                                >
                                    <span v-if="!boardStore.loading"><i class="fa-solid fa-play"></i></span>
                                    <span v-else class="spinner-border spinner-border-sm"></span>
                                </button>
                            </div>

                            <template v-if="boardStore.webhookTestResult">
                                <p class="alert alert-info mb-2">
                                    {{ boardStore.webhookTestResult }}
                                </p>
                            </template>

                            <!-- Email -->
                            <div class="form-check mb-2">
                                <input
                                    v-model="settings.need_email_notification"
                                    type="checkbox"
                                    class="form-check-input"
                                    id="needEmailNotification"
                                />
                                <label class="form-check-label" for="needEmailNotification">
                                    Отправлять уведомления на email
                                </label>
                            </div>

                            <div class="input-group mb-2">
                                <div class="form-floating">
                                    <input
                                        v-model="settings.email_for_notification"
                                        type="email"
                                        class="form-control"
                                        id="emailNotification"
                                        placeholder="Email"
                                    />
                                    <label for="emailNotification">Email для уведомлений</label>
                                </div>

                                <button
                                    :disabled="!settings.email_for_notification || boardStore.loading"
                                    @click="testEmail"
                                    type="button"
                                    class="input-group-text btn btn-outline-primary"
                                >
                                    <span v-if="!boardStore.loading"><i class="fa-solid fa-play"></i></span>
                                    <span v-else class="spinner-border spinner-border-sm"></span>
                                </button>
                            </div>

                            <template v-if="boardStore.emailTestResult">
                                <p class="alert alert-info mb-2">
                                    {{ boardStore.emailTestResult }}
                                </p>
                            </template>

                            <!-- Безопасность -->
                            <div class="mt-4">
                                <h6>Безопасность</h6>
                                <p class="text-muted small">
                                    Ключ сессии используется для доступа к доске. При обновлении ключа, старый адрес перестанет работать.
                                </p>

                                <button
                                    @click="handleRefreshUuid"
                                    type="button"
                                    class="btn btn-outline-danger w-100 my-2"
                                    :disabled="boardStore.loading"
                                >
                                    <span v-if="!boardStore.loading">Обновить ключ сессии</span>
                                    <span v-else class="spinner-border spinner-border-sm"></span>
                                </button>
                            </div>

                        </form>

                    </div>

                    <!-- TAB: CRM -->
                    <div v-if="activeTab === 'crm'">

                        <h6 class="fw-bold mb-2">Интеграции CRM</h6>

                        <!-- CRM LIST -->
                        <div v-for="(crm, index) in settings.crm" :key="index" class="mb-2">

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong>{{ crm.title }}</strong>

                                <button class="btn btn-sm btn-outline-danger" @click="removeCrm(index)">
                                    <i class="fa-solid fa-xmark"></i>
                                </button>
                            </div>

                            <!-- amoCRM -->
                            <template v-if="crm.type === 'amocrm'">

                                <div class="form-floating mb-2">
                                    <input v-model="crm.domain" type="text" class="form-control" placeholder="Домен">
                                    <label>Домен amoCRM</label>
                                </div>

                                <div class="form-floating mb-2">
                                    <input v-model="crm.client_id" type="text" class="form-control" placeholder="ID интеграции">
                                    <label>ID интеграции</label>
                                </div>

                                <div class="form-floating mb-2">
                                    <input v-model="crm.client_secret" type="text" class="form-control" placeholder="Секрет">
                                    <label>Секрет интеграции</label>
                                </div>

                                <div class="form-floating mb-2">
                                    <input v-model="crm.token" type="text" class="form-control" placeholder="Токен">
                                    <label>Токен</label>
                                </div>

                            </template>

                            <!-- Bitrix24 -->
                            <template v-if="crm.type === 'bitrix24'">

                                <div class="form-floating mb-2">
                                    <input v-model="crm.webhook" type="text" class="form-control" placeholder="Webhook URL">
                                    <label>Webhook URL Битрикс24</label>
                                </div>

                            </template>

                            <button
                                class="btn btn-outline-primary w-100"
                                :disabled="boardStore.loading"
                                @click="testCrm(crm)"
                            >
                                <span v-if="!boardStore.loading">Проверить интеграцию</span>
                                <span v-else class="spinner-border spinner-border-sm"></span>
                            </button>

                        </div>

                        <!-- ADD CRM -->
                        <div class="d-grid">
                            <div class="dropdown d-grid">
                                <button
                                    class="btn btn-outline-secondary dropdown-toggle"
                                    data-bs-toggle="dropdown"
                                >
                                    <i class="fa-solid fa-plus"></i> Добавить CRM
                                </button>

                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item" @click="addCrm('amocrm')">
                                            amoCRM
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item" @click="addCrm('bitrix24')">
                                            Битрикс24
                                        </a>
                                    </li>
                                </ul>
                            </div>

                        </div>

                        <template v-if="boardStore.crmTestResult">
                            <p class="alert alert-info mt-3">
                                {{ boardStore.crmTestResult }}
                            </p>
                        </template>

                    </div>

                </div>

                <!-- FOOTER -->
                <div class="modal-footer">
                    <a href="/?new=1" target="_blank" class="btn btn-outline-secondary me-auto">
                        Новая сессия <i class="fa-solid fa-arrow-up-right-from-square small ms-1"></i>
                    </a>
                    <button class="btn btn-secondary" @click="$emit('close')">Отмена</button>
                    <button class="btn btn-primary" @click="submit">Сохранить</button>
                </div>

            </div>
        </div>
    </div>

</template>

<script>
import { useKanbanStore } from "@/stores/useKanbanStore.js";

export default {
    name: 'BoardSettings',

    data() {
        return {
            activeTab: 'notifications',

            boardStore: useKanbanStore(),

            settings: {
                webhook_url: null,
                email_for_notification: null,
                need_email_notification: false,

                crm: [] // массив CRM
            }
        }
    },

    mounted() {
        this.settings = {
            ...this.settings,
            ...(this.boardStore.board?.config || {})
        }

        if (!Array.isArray(this.settings.crm)) {
            this.settings.crm = []
        }
    },

    methods: {
        addCrm(type) {
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
            this.$emit("close")
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

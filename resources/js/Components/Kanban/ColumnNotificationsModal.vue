<template>
    <div class="modal fade show d-block" v-if="show && column && local">
        <div class="modal-dialog modal-fullscreen-sm-down">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title"> Уведомления для колонки "{{ column?.title || '' }}"</h5>
                    <button class="btn-close" @click="$emit('close')"></button>
                </div>

                <div class="modal-body">

                    <!-- Включение уведомлений -->
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" v-model="local.enabled">
                        <label class="form-check-label">Включить уведомления</label>
                    </div>

                    <hr>

                    <!-- EMAIL -->
                    <h6 class="fw-bold my-3">Почта</h6>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" v-model="local.email.enabled">
                        <label class="form-check-label">Email уведомления</label>
                    </div>

                    <div v-if="local.email.enabled">

                        <!-- form-floating email inputs -->
                        <div
                            v-for="(email, index) in local.email.to"
                            :key="'email-' + index"
                            class="position-relative mb-2"
                        >
                            <div class="form-floating">
                                <input
                                    type="email"
                                    class="form-control"
                                    v-model="local.email.to[index]"
                                    :id="'emailInput' + index"
                                    placeholder="Email"
                                >
                                <label :for="'emailInput' + index">Email получателя</label>
                            </div>

                            <button
                                class="btn btn-sm btn-outline-danger position-absolute top-50 end-0 translate-middle-y me-2"
                                @click="removeEmail(index)"
                                v-if="local.email.to.length > 1"
                            >
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>

                        <div class="btn-group mb-2">
                            <button class="btn btn-sm btn-outline-primary" @click="addEmail">
                                <i class="fa-solid fa-plus"></i> Добавить получателя
                            </button>

                            <button class="btn btn-sm btn-outline-secondary" @click="testEmail">
                                <i class="fa-solid fa-envelope-circle-check"></i> Проверить почту
                            </button>
                        </div>

                    </div>

                    <hr>

                    <!-- WEBHOOK -->
                    <h6 class="fw-bold my-3">Вебхук</h6>

                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" v-model="local.webhook.enabled">
                        <label class="form-check-label">Webhook уведомления</label>
                    </div>

                    <div v-if="local.webhook.enabled">

                        <!-- form-floating webhook inputs -->
                        <div
                            v-for="(url, index) in local.webhook.urls"
                            :key="'wh-' + index"
                            class="position-relative mb-3"
                        >
                            <div class="form-floating">
                                <input
                                    type="text"
                                    class="form-control"
                                    v-model="local.webhook.urls[index]"
                                    :id="'whInput' + index"
                                    placeholder="Webhook URL"
                                >
                                <label :for="'whInput' + index">URL вебхука</label>
                            </div>

                            <button
                                class="btn btn-sm btn-outline-danger position-absolute top-50 end-0 translate-middle-y me-2"
                                @click="removeWebhook(index)"
                                v-if="local.webhook.urls.length > 1"
                            >
                                <i class="fa-solid fa-xmark"></i>
                            </button>
                        </div>
                        <div class="btn-group mb-2">
                        <button class="btn btn-sm btn-outline-primary" @click="addWebhook">
                            <i class="fa-solid fa-plus"></i> Добавить вебхук
                        </button>

                        <button class="btn btn-sm btn-outline-secondary" @click="testWebhook">
                            <i class="fa-solid fa-plug-circle-check"></i> Проверить вебхук
                        </button>
                        </div>
                    </div>

                    <hr>

                    <!-- СОБЫТИЯ -->
                    <h6 class="fw-bold my-3">События</h6>

                    <div class="form-check" v-for="(val, key) in local.events" :key="key">
                        <input class="form-check-input" type="checkbox" v-model="local.events[key]">
                        <label class="form-check-label">
                            {{ eventLabels[key] }}
                        </label>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="$emit('close')">Отмена</button>
                    <button class="btn btn-primary" @click="save">Сохранить</button>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        show: Boolean,
        column: Object,
        store: Object
    },

    data() {
        return {
            local: {},
            eventLabels: {
                card_created: "Создание карточки",
                card_updated: "Изменение карточки",
                card_moved: "Перемещение карточки",
                new_message: "Новое сообщение"
            }
        }
    },

    watch: {
        show(val) {
            if (!val) return

            // Если колонка ещё не загружена — ждём
            if (!this.column) {
                this.local = null
                return
            }

            // Берём настройки из accessor'а Laravel
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
        }
    },

    methods: {
        addEmail() {
            this.local.email.to.push("")
        },
        removeEmail(index) {
            this.local.email.to.splice(index, 1)
        },

        addWebhook() {
            this.local.webhook.urls.push("")
        },
        removeWebhook(index) {
            this.local.webhook.urls.splice(index, 1)
        },

        async testWebhook() {
            const urls = this.local.webhook.urls.filter(u => u.trim() !== "")
            const result = await this.store.testWebhook(urls)
            console.log("Webhook test:", result)
        },

        async testEmail() {
            const emails = this.local.email.to.filter(e => e.trim() !== "")
            const result = await this.store.testEmail(emails)
            console.log("Email test:", result)
        },

        save() {
            this.local.email.to = this.local.email.to.filter(e => e.trim() !== "")
            this.local.webhook.urls = this.local.webhook.urls.filter(u => u.trim() !== "")

            if (this.local.email.to.length === 0) this.local.email.to = [""]
            if (this.local.webhook.urls.length === 0) this.local.webhook.urls = [""]

            this.$emit("save", this.local)
        }
    }
}
</script>

<style scoped>
.modal {
    background: rgba(0,0,0,0.4);
}
</style>

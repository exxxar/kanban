<template>
    <div class="refresh-progress" :style="{ width: progress + '%' }"></div>

    <KanbanBoard :initial-board="board"/>

    <div class="modal fade" id="installPwaModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Установить приложение</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <p>Вы можете установить Kanban как приложение и запускать его прямо с рабочего стола.</p>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Позже</button>
                    <button class="btn btn-primary" @click="installPWA">Установить</button>
                </div>

            </div>
        </div>
    </div>


</template>

<script>
import KanbanBoard from '@/Components/Kanban/KanbanBoard.vue'
import {useKanbanStore} from "@/stores/useKanbanStore.js";

export default {
    props: {
        board: Object,
        vapidPublicKey: String
    },
    components: {KanbanBoard},
    data() {
        return {
            store: useKanbanStore(),
            progress: 0
        }
    },



    mounted() {



        this.initPush()

        let progressTimer = null
        let refreshTimer = null

        this.progress = 0

        // обновляем прогресс каждые 100 мс
        progressTimer = setInterval(() => {
            this.progress += 100 / 600 // 600 шагов по 100мс = 60 сек
            if (this.progress >= 100) {
                this.progress = 100
            }
        }, 100)

        // обновление доски раз в минуту
        refreshTimer = setInterval(async () => {
            await this.store.loadBoard(this.board.uuid)
            this.progress = 0
        }, 60000)

    },
    methods: {
        installPWA(){
            window.installPWA()
        },
        async initPush() {
            // Проверяем поддержку
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
                console.warn('Push notifications not supported')
                return
            }

            // Регистрируем SW
            const registration = await navigator.serviceWorker.register('/sw.js')

            // Спрашиваем разрешение
            const permission = await Notification.requestPermission()
            if (permission !== 'granted') {
                console.warn('User denied notifications')
                return
            }

            // Подписываемся
            const subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: this.vapidPublicKey
            })

            // Отправляем на сервер
            await axios.post('/api/push/subscribe', subscription)
        }
    }

}
</script>
<style>
.refresh-progress {
    position: fixed;
    top: 0;
    left: 0;
    height: 3px;
    background: #0d6efd; /* Bootstrap primary */
    transition: width 0.1s linear;
    z-index: 9999;
}

</style>

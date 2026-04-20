<template>
    <div class="modal fade show d-block" v-if="show">
        <div class="modal-dialog modal-fullscreen-sm-down">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Перенос колонок</h5>
                    <button class="btn-close" @click="$emit('close')"></button>
                </div>

                <div class="modal-body">



                    <p class="text-muted small">Перетащи колонки, чтобы изменить порядок</p>

                    <ul class="list-group">
                        <li
                            v-for="(col, index) in localColumns"
                            :key="col.id"
                            class="list-group-item d-flex align-items-center justify-content-between"
                            draggable="true"
                            @dragstart="onDragStart(col)"
                            @dragover.prevent
                            @drop="onDrop(col)"
                        >
                            <div class="d-flex align-items-center gap-2 small">
                                <i class="fa-solid fa-up-down-left-right text-secondary"></i>
                                <span
                                    v-if="need_id"
                                    class="fw-bold badge bg-primary">#{{ col.id }}</span>
                                <span>{{ col.title }}</span>
                            </div>

                            <div class="btn-group btn-group-sm">
                                <button
                                    class="btn btn-outline-secondary"
                                    :disabled="index === 0"
                                    @click="moveUp(index)"
                                >
                                    <i class="fa-solid fa-arrow-up"></i>
                                </button>

                                <button
                                    class="btn btn-outline-secondary"
                                    :disabled="index === localColumns.length - 1"
                                    @click="moveDown(index)"
                                >
                                    <i class="fa-solid fa-arrow-down"></i>
                                </button>
                            </div>
                        </li>
                    </ul>

                    <!-- Включение уведомлений -->
                    <div class="form-check form-switch my-3">
                        <input class="form-check-input" type="checkbox" v-model="need_id">
                        <label class="form-check-label">Отображать идентификаторы колонок</label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" @click="$emit('close')">Отмена</button>
                    <button class="btn btn-primary" @click="saveOrder">Сохранить</button>
                </div>

            </div>
        </div>
    </div>
</template>

<script>
export default {
    props: {
        show: Boolean,
        columns: Array
    },

    data() {
        return {
            need_id:false,
            dragItem: null,
            localColumns: []
        }
    },
    watch: {
        show(val) {
            if (val) {
                this.localColumns = JSON.parse(JSON.stringify(this.columns))
            }
        }
    },
    created(){
      //this.localColumns =  JSON.parse(JSON.stringify(this.columns))
    },
    methods: {
        onDragStart(col) {
            this.dragItem = col
        },

        onDrop(target) {
            if (!this.dragItem || this.dragItem.id === target.id) return

            const from = this.localColumns.indexOf(this.dragItem)
            const to = this.localColumns.indexOf(target)

            this.localColumns.splice(from, 1)
            this.localColumns.splice(to, 0, this.dragItem)
        },
        moveUp(index) {
            if (index === 0) return
            const item = this.localColumns[index]
            this.localColumns.splice(index, 1)
            this.localColumns.splice(index - 1, 0, item)
        },

        moveDown(index) {
            if (index === this.localColumns.length - 1) return
            const item = this.localColumns[index]
            this.localColumns.splice(index, 1)
            this.localColumns.splice(index + 1, 0, item)
        },
        saveOrder() {
            const ids = this.localColumns.map(c => c.id)
            this.$emit('save', ids)
        }
    }
}
</script>

<style scoped>
.modal {
    background: rgba(0,0,0,0.4);
}
</style>

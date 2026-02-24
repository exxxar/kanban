<template>
    <div class="modal-backdrop">
        <div class="modal-window">
            <h3>Новая колонка</h3>

            <form @submit.prevent="save">
                <input v-model="title" class="form-control mb-3" placeholder="Название" required>

                <div class="d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary" @click="$emit('close')">Отмена</button>
                    <button type="submit" class="btn btn-primary">Добавить</button>
                </div>
            </form>
        </div>
    </div>
</template>

<script>
export default {
    data() {
        return { title: '' }
    },

    methods: {
        save() {
            this.$emit('save', this.title)
        }
    }
}
</script>

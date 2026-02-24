<template>
    <div class="my-2">
        <h5 class="mb-3">Разработка</h5>

        <ul class="list-group mb-3">
            <li class="list-group-item"><strong>Менеджер:</strong> {{ card.data.manager }}</li>
            <li class="list-group-item"><strong>Дедлайн:</strong> {{ card.data.deadline }}</li>
        </ul>

        <h6>Технологии</h6>
        <ul class="list-group mb-3">
            <li v-for="t in card.data.tech" :key="t" class="list-group-item">
                {{ t }}
            </li>
        </ul>

        <h6>Подзадачи</h6>
        <ul class="list-group">
            <li
                v-for="s in card.subtasks"
                :key="s.id"
                class="list-group-item"
                :class="{ 'bg-success bg-opacity-10': s.done }"
            >
                <input type="checkbox" class="form-check-input me-2" :checked="s.done" disabled>
                {{ s.text }}
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    props: { card: Object }
}
</script>

<template>
    <div class="my-2">
        <h5 class="mb-3">Вопрос / Ответ</h5>

        <div class="mb-3">
            <strong>Вопрос:</strong>
            <p>{{ card.data.question }}</p>
        </div>

        <div>
            <strong>Ответ:</strong>
            <p>{{ card.data.answer }}</p>
        </div>
    </div>
</template>

<script>
export default {
    props: { card: Object }
}
</script>

<template>
    <div class="my-2">
        <h5 class="mb-3">Финансовая операция</h5>

        <ul class="list-group">
            <li class="list-group-item"><strong>Сумма:</strong> {{ card.data.amount }} {{ card.data.currency }}</li>
            <li class="list-group-item"><strong>Тип операции:</strong> {{ card.data.operation }}</li>
            <li class="list-group-item"><strong>Баланс после:</strong> {{ card.data.balanceAfter }}</li>
            <li class="list-group-item"><strong>Комментарий:</strong> {{ card.data.comment }}</li>
        </ul>
    </div>
</template>

<script>
export default {
    props: { card: Object }
}
</script>

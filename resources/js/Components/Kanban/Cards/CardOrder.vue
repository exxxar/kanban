<template>
    <div class="my-2">
        <h5 class="mb-3">Информация о заказе</h5>

        <ul class="list-group mb-3">
            <li class="list-group-item"><strong>Товар:</strong> {{ card.data.product }}</li>
            <li class="list-group-item"><strong>Количество:</strong> {{ card.data.quantity }}</li>
            <li class="list-group-item"><strong>Цена:</strong> {{ card.data.price }} ₽</li>
            <li class="list-group-item"><strong>Скидка:</strong> {{ card.data.discount }}</li>
            <li class="list-group-item"><strong>Адрес:</strong> {{ card.data.address }}</li>
            <li class="list-group-item"><strong>Комментарий:</strong> {{ card.data.comment }}</li>
        </ul>

        <h6>Состав заказа</h6>
        <ul class="list-group">
            <li v-for="p in card.data.parts" :key="p.name" class="list-group-item">
                {{ p.name }} — {{ p.amount }}
            </li>
        </ul>
    </div>
</template>

<script>
export default {
    props: { card: Object }
}
</script>

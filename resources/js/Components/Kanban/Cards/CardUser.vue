<template>
    <div class="my-2">
        <h5 class="mb-3">Информация о клиенте</h5>

        <ul class="list-group">
            <li class="list-group-item"><strong>Телефон:</strong> {{ card.data.phone }}</li>
            <li class="list-group-item"><strong>Email:</strong> {{ card.data.email }}</li>
            <li class="list-group-item"><strong>Город:</strong> {{ card.data.city }}</li>
            <li class="list-group-item"><strong>Компания:</strong> {{ card.data.company }}</li>
            <li class="list-group-item"><strong>Должность:</strong> {{ card.data.position }}</li>
            <li class="list-group-item"><strong>Заметки:</strong> {{ card.data.notes }}</li>
        </ul>
    </div>
</template>

<script>
export default {
    props: { card: Object }
}
</script>

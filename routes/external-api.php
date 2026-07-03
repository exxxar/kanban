<?php

// === ДОСКИ (BOARDS) ===
use App\Http\Controllers\Api\BoardController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\ColumnController;
use App\Http\Controllers\Api\CommentController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TaskController;
use Illuminate\Support\Facades\Route;

Route::prefix('boards')->group(function () {
    Route::get('/', [BoardController::class, 'index']);           // Список досок
    Route::post('/', [BoardController::class, 'store']);          // Создать доску
    Route::get('/templates', [BoardController::class, 'templates']); // Список шаблонов
    Route::get('/{uuid}', [BoardController::class, 'show']);      // Получить доску
    Route::put('/{uuid}', [BoardController::class, 'update']);    // Обновить доску
    Route::delete('/{uuid}', [BoardController::class, 'destroy']); // Удалить доску
    Route::post('/{uuid}/apply-template', [BoardController::class, 'applyTemplate']); // Применить шаблон
    Route::post('/{uuid}/config', [BoardController::class, 'updateConfig']); // Обновить конфиг

    // === КОЛОНКИ ДОСКИ ===
    Route::post('/{uuid}/columns', [ColumnController::class, 'store']); // Создать колонку
    Route::put('/{uuid}/columns/reorder', [ColumnController::class, 'reorder']); // Изменить порядок

    // === ЗАДАЧИ ДОСКИ ===
    Route::get('/{uuid}/tasks', [TaskController::class, 'indexByBoard']); // Все задачи доски
    Route::post('/{uuid}/tasks', [TaskController::class, 'store']); // Создать задачу

    // === ТЕГИ ДОСКИ ===
    Route::get('/{uuid}/tags', [TagController::class, 'index']); // Список тегов
    Route::post('/{uuid}/tags', [TagController::class, 'store']); // Создать тег
});

// === КОЛОНКИ (COLUMNS) ===
Route::prefix('columns')->group(function () {
    Route::put('/{columnId}', [ColumnController::class, 'update']); // Обновить колонку
    Route::delete('/{columnId}', [ColumnController::class, 'destroy']); // Удалить колонку
    Route::post('/{columnId}/notifications', [ColumnController::class, 'updateNotifications']); // Уведомления
    Route::put('/{columnId}/tasks/reorder', [TaskController::class, 'reorder']); // Порядок задач
    Route::get('/{columnId}/tasks', [TaskController::class, 'indexByColumn']); // Задачи колонки
});

// === ЗАДАЧИ (TASKS) ===
Route::prefix('tasks')->group(function () {
    Route::get('/', [TaskController::class, 'index']); // Все задачи (опционально)
    Route::get('/{taskId}', [TaskController::class, 'show']); // Получить задачу
    Route::put('/{taskId}', [TaskController::class, 'update']); // Обновить задачу
    Route::delete('/{taskId}', [TaskController::class, 'destroy']); // Удалить задачу
    Route::post('/{taskId}/duplicate', [TaskController::class, 'duplicate']); // Дублировать
    Route::post('/{taskId}/view', [TaskController::class, 'markViewed']); // Отметить просмотренной
    Route::post('/move', [TaskController::class, 'move']); // Переместить задачу

    // === КЛИЕНТ ЗАДАЧИ ===
    Route::get('/{taskId}/client', [ClientController::class, 'show']); // Получить клиента
    Route::put('/{taskId}/client', [ClientController::class, 'update']); // Обновить клиента

    // === КОММЕНТАРИИ ===
    Route::get('/{taskId}/comments', [CommentController::class, 'index']); // Список комментариев
    Route::post('/{taskId}/comments', [CommentController::class, 'store']); // Добавить комментарий
    Route::put('/comments/{commentId}', [CommentController::class, 'update']); // Обновить комментарий
    Route::delete('/comments/{commentId}', [CommentController::class, 'destroy']); // Удалить комментарий

    // === СООБЩЕНИЯ ===
    Route::get('/{taskId}/messages', [MessageController::class, 'index']); // Список сообщений
    Route::post('/{taskId}/messages', [MessageController::class, 'store']); // Отправить сообщение
    Route::post('/{taskId}/messages/read-all', [MessageController::class, 'markAllRead']); // Отметить все прочитанными
    Route::post('/messages/{messageId}/read', [MessageController::class, 'markRead']); // Отметить прочитанным
    Route::post('/messages/{messageId}/attachments', [MessageController::class, 'addAttachments']);
    Route::delete('/messages/{messageId}/attachments/{attachmentIndex}', [MessageController::class, 'removeAttachment']);

    // === ТЕГИ ЗАДАЧИ ===
    Route::post('/{taskId}/tags', [TagController::class, 'attachToTask']); // Привязать теги
    Route::delete('/{taskId}/tags/{tagId}', [TagController::class, 'detachFromTask']); // Отвязать тег
});

// === ТЕГИ (TAGS) ===
Route::prefix('tags')->group(function () {
    Route::put('/{tagId}', [TagController::class, 'update']); // Обновить тег
    Route::delete('/{tagId}', [TagController::class, 'destroy']); // Удалить тег
});

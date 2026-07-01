<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id', 'source', 'phone', 'contact_person', 'company_name',
        'address', 'links', 'interaction_stage', 'deal_comment',
        'partner', 'cost', 'placement_type','custom_data'
    ];

    protected $casts = [
        'links' => 'array',
        'custom_data' => 'array',
        'cost' => 'decimal:2',
    ];

    protected $trackedFields = [
        'source', 'phone', 'contact_person', 'company_name',
        'address', 'interaction_stage', 'deal_comment',
        'partner', 'cost', 'placement_type'
    ];

    // Красивые названия полей для логов
    protected $fieldNames = [
        'source' => 'Источник лида',
        'phone' => 'Телефон',
        'contact_person' => 'Контактное лицо',
        'company_name' => 'Название компании',
        'address' => 'Адрес',
        'interaction_stage' => 'Этап взаимодействия',
        'deal_comment' => 'Комментарий к сделке',
        'partner' => 'Партнёр',
        'cost' => 'Стоимость',
        'placement_type' => 'Вид размещения'
    ];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(ClientActivity::class)->orderBy('created_at', 'desc');
    }

    protected static function booted()
    {
        // При создании
        static::created(function ($client) {
            $client->logActivity(
                'created',
                'Клиент создан',
                $client->company_name ? "Компания «{$client->company_name}»" : null
            );
        });

        // ⚠️ ИСПРАВЛЕНИЕ: используем updating вместо updated
        // В updating getDirty() ещё содержит изменения
        static::updating(function ($client) {
            $changes = $client->getDirty();

            foreach ($changes as $field => $newValue) {
                if (!in_array($field, $client->trackedFields)) {
                    continue;
                }

                $oldValue = $client->getOriginal($field);

                // Пропускаем, если значения одинаковые (например, пустая строка и null)
                if ((string)$oldValue === (string)$newValue) {
                    continue;
                }

                // Форматируем значения для отображения
                $oldDisplay = $client->formatFieldValue($field, $oldValue);
                $newDisplay = $client->formatFieldValue($field, $newValue);

                // Определяем тип действия
                $actionType = $client->getActionTypeForField($field);

                // Определяем заголовок
                $fieldName = $client->fieldNames[$field] ?? $field;
                $title = "{$fieldName} изменён";

                // Формируем описание
                $description = $client->buildDescription($field, $oldDisplay, $newDisplay);

                $client->logActivity(
                    $actionType,
                    $title,
                    $description,
                    ['field' => $field, 'old' => $oldValue, 'new' => $newValue]
                );
            }
        });

        // При удалении
        static::deleted(function ($client) {
            $client->logActivity(
                'client_deleted',
                'Клиент удалён',
                $client->company_name ? "Удалён клиент «{$client->company_name}»" : null
            );
        });
    }

    // === ВСПОМОГАТЕЛЬНЫЕ МЕТОДЫ ===

    public function logActivity($type, $title, $description = null, $metadata = [])
    {
        $this->activities()->create([
            'action_type' => $type,
            'title' => $title,
            'description' => $description,
            'metadata' => $metadata,
            'user_name' => 'Пользователь'
        ]);
    }

    // Форматирование значения поля для отображения
    protected function formatFieldValue($field, $value)
    {
        if ($value === null || $value === '') {
            return '—';
        }

        if ($field === 'cost') {
            return number_format((float)$value, 0, ',', ' ') . ' ₽';
        }

        if ($field === 'links' && is_array($value)) {
            return count($value) . ' ссылок';
        }

        return (string)$value;
    }

    // Определение типа действия по полю
    protected function getActionTypeForField($field)
    {
        return match ($field) {
            'interaction_stage' => 'stage_changed',
            'cost' => 'cost_changed',
            'company_name', 'contact_person', 'phone' => 'contact_changed',
            'source' => 'source_changed',
            default => 'updated',
        };
    }

    // Построение описания изменения
    protected function buildDescription($field, $oldDisplay, $newDisplay)
    {
        if ($field === 'cost') {
            return "{$oldDisplay} → {$newDisplay}";
        }

        return "«{$oldDisplay}» → «{$newDisplay}»";
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель для глобальных настроек (Панель).
 * Хранится в центральной базе данных.
 *
 * @property int $id
 * @property string $module
 * @property string $key
 * @property string $type
 * @property string|null $value
 * @property string $label
 * @property string|null $description
 * @property bool $is_overridable
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class GlobalSetting extends Model
{
    /**
     * Соединение с базой данных (Центральное).
     *
     * @var string
     */
    protected $connection = 'central';

    /**
     * Атрибуты, для которых разрешено массовое заполнение.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'module',
        'key',
        'type',
        'value',
        'label',
        'description',
        'is_overridable',
    ];

    /**
     * Преобразование типов атрибутов.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_overridable' => 'boolean',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }
}

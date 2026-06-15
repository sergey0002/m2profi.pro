<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель для глобальных настроек (Панель).
 * Хранится в центральной базе данных (m2profi_main).
 */
class GlobalSetting extends Model
{
    protected $table = 'global_settings';

    protected $fillable = [
        'module',
        'key',
        'type',
        'value',
        'label',
        'description',
        'is_overridable',
    ];

    protected $casts = [
        'is_overridable' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

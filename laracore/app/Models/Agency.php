<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agency extends Model
{
    protected $table = 'agency';
    protected $primaryKey = 'agency_id';
    public $timestamps = false;

    // Типы агентств
    const TYPE_SELF_REGISTERED = 0;
    const TYPE_GLOBAL_USER = 1;
    const TYPE_SALES_DEPARTMENT = 2;
    const TYPE_ADMINS = 3;

    // Статусы регистрации
    const STATUS_ACTIVE = 0;
    const STATUS_APPLICATION = 1;
    const STATUS_REJECTED = 2;

    protected $fillable = [
        'caption', 'admin_user_id', 'type', 'inn', 'unactiv', 'del', 'registration_status', 'registration_data', 'add_datetime', 'comment'
    ];

    protected $casts = [
        'agency_id' => 'integer',
        'admin_user_id' => 'integer',
        'type' => 'integer',
        'unactiv' => 'integer',
        'del' => 'integer',
        'registration_status' => 'integer',
        'registration_data' => 'array',
        'add_datetime' => 'datetime',
    ];

    public function users()
    {
        return $this->hasMany(User::class, 'agency_id', 'agency_id');
    }

    public function adminUser()
    {
        return $this->belongsTo(User::class, 'admin_user_id', 'id');
    }

    public function docs()
    {
        return $this->hasMany(AgencyDoc::class, 'agency_id', 'agency_id');
    }

    public function isSalesDepartment(): bool
    {
        return (int) $this->type === self::TYPE_SALES_DEPARTMENT;
    }

    public function getTypeName(): string
    {
        return match((int) $this->type) {
            self::TYPE_SELF_REGISTERED => 'Самозарегистрированное',
            self::TYPE_GLOBAL_USER => 'Глобальный пользователь',
            self::TYPE_SALES_DEPARTMENT => 'Отдел продаж',
            self::TYPE_ADMINS => 'Администраторы',
            default => 'Неизвестно'
        };
    }
}

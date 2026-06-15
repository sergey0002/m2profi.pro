<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use Notifiable, HasRoles;

    protected $table = 'users';
    protected $primaryKey = 'id';
    public $timestamps = false; // Нет created_at/updated_at в legacy БД

    protected $fillable = [
        'login', 'password', 'name', 'agency_id', 'email', 'e_mail', 'phone', 'del', 'add_datetime',
        'social_id', 'social_type', 'social_avatar'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'agency_id' => 'integer',
        'del' => 'integer',
        'add_datetime' => 'datetime',
    ];

    /**
     * Алиас для email (совместимость с Filament)
     */
    public function getEmailAttribute()
    {
        return $this->attributes['email'] ?? ($this->attributes['e_mail'] ?? null);
    }

    public function setEmailAttribute($value)
    {
        if (array_key_exists('email', $this->attributes) || !array_key_exists('e_mail', $this->attributes)) {
            $this->attributes['email'] = $value;
        } else {
            $this->attributes['e_mail'] = $value;
        }
    }

    /**
     * ВАЖНО: Пароли в legacy БД хранятся в открытом виде.
     * Для платформенных админов используем хешированные пароли.
     */
    public function getAuthPassword()
    {
        // Если пароль уже хеширован (начинается с $2y$), возвращаем как есть
        if (str_starts_with($this->password, '$2y$')) {
            return $this->password;
        }
        
        // Для legacy пользователей возвращаем открытый пароль
        return $this->password;
    }

    // Связь с агентством
    public function agency()
    {
        // Безопасная связь - проверяем существование таблицы
        if (!\Illuminate\Support\Facades\Schema::hasTable('agency')) {
            return null; 
        }
        return $this->belongsTo(Agency::class, 'agency_id', 'agency_id');
    }

    // Связь с агентством, где пользователь админ
    public function adminAgency()
    {
        if (!\Illuminate\Support\Facades\Schema::hasTable('agency')) {
            return null;
        }
        return $this->hasOne(Agency::class, 'admin_user_id', 'id');
    }

    // Проверка на Super Admin (em admin)
    public function isSuperAdmin()
    {
        return $this->hasRole('super-admin') || $this->hasRole('platform-admin') || $this->login === 'admin';
    }

    // Проверка на Agency Admin
    public function isAgencyAdmin()
    {
        return $this->hasRole('agency-admin');
    }

    public function getAgencyId()
    {
        return (int) $this->agency_id;
    }

    public function stats()
    {
        return $this->hasMany(UserStat::class, 'users_id', 'id');
    }

    public function getLastActivityAttribute()
    {
        return $this->stats()->max('date');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        if ($panel->getId() === 'platform') {
            return $this->hasRole('platform-admin');
        }
        return $this->isSuperAdmin() || $this->isAgencyAdmin();
    }
}

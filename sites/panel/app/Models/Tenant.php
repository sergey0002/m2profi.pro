<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use SoftDeletes;

    protected $connection = 'mysql'; // Default for Main Dashboard, will be 'central' for tenants
    protected $table = 'tenants';
    
    protected $fillable = [
        'name',
        'subdomain',
        'domain',
        'db_name',
        'db_host',
        'db_port',
        'db_username',
        'db_password',
        'status',
        'config',
    ];
    
    protected $casts = [
        'db_password' => 'encrypted',
        'config' => 'array',
        'db_port' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
    
    // Связи
    public function tenantModules()
    {
        return $this->hasMany(TenantModule::class);
    }

    public function modules()
    {
        return $this->belongsToMany(Module::class, 'tenant_modules', 'tenant_id', 'module_slug', 'id', 'slug')
            ->withPivot('is_enabled', 'settings', 'enabled_at')
            ->withTimestamps();
    }
    
    // Методы
    public function enableModule(string $moduleSlug, array $settings = [])
    {
        return $this->modules()->syncWithoutDetaching([
            $moduleSlug => [
                'is_enabled' => true,
                'settings' => json_encode($settings),
                'enabled_at' => now(),
            ]
        ]);
    }
    
    public function disableModule(string $moduleSlug)
    {
        return $this->modules()->updateExistingPivot($moduleSlug, [
            'is_enabled' => false,
        ]);
    }
    
    public function hasModule(string $moduleSlug): bool
    {
        return $this->modules()
            ->where('module_slug', $moduleSlug)
            ->wherePivot('is_enabled', true)
            ->exists();
    }
    
    // Получить конфигурацию БД для подключения к тенанту
    public function getDatabaseConfig(): array
    {
        return [
            'driver' => 'mysql',
            'host' => $this->db_host,
            'port' => $this->db_port,
            'database' => $this->db_name,
            'username' => $this->db_username,
            'password' => $this->db_password,
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ];
    }
}

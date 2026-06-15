<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $table = 'modules';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'slug',
        'name',
        'description',
        'is_active',
        'price',
        'version',
        'dependencies',
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'price' => 'decimal:2',
        'dependencies' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    // Связи
    public function tenants()
    {
        return $this->belongsToMany(Tenant::class, 'tenant_modules', 'module_slug', 'tenant_id', 'slug', 'id')
            ->withPivot('is_enabled', 'settings', 'enabled_at')
            ->withTimestamps();
    }
}

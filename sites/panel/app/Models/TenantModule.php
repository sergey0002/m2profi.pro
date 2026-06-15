<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantModule extends Model
{
    protected $table = 'tenant_modules';
    
    protected $fillable = [
        'tenant_id',
        'module_slug',
        'is_enabled',
        'settings',
        'enabled_at',
    ];
    
    protected $casts = [
        'is_enabled' => 'boolean',
        'settings' => 'array',
        'enabled_at' => 'datetime',
    ];
    
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    
    public function module()
    {
        return $this->belongsTo(Module::class, 'module_slug', 'slug');
    }
}

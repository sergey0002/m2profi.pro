<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingDefinition extends Model
{
    protected $table = 'setting_definitions';
    
    protected $fillable = [
        'key',
        'name',
        'type',
        'default_value',
        'description',
        'is_global',
    ];
    
    protected $casts = [
        'is_global' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}

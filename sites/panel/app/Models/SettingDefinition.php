<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\SoftDeletes;

class SettingDefinition extends Model
{
    use SoftDeletes;

    protected $table = 'setting_definitions';
    
    protected $fillable = [
        'key',
        'name',
        'type',
        'default_value',
        'description',
        'is_global',
        'is_public',
        'is_system',
        'module_id',
        'section_id',
    ];
    
    protected $casts = [
        'is_global' => 'boolean',
        'is_public' => 'boolean',
        'is_system' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function section()
    {
        return $this->belongsTo(SettingSection::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SettingSection extends Model
{
    protected $fillable = ['module_id', 'name'];

    public function module()
    {
        return $this->belongsTo(Module::class);
    }
    
    public function definitions()
    {
        return $this->hasMany(SettingDefinition::class, 'section_id');
    }
}

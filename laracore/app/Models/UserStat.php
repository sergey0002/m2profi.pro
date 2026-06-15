<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserStat extends Model
{
    protected $table = 'users_stat';
    protected $primaryKey = 'users_stat_id';
    public $timestamps = false;

    protected $fillable = [
        'users_id', 'date', 'action'
    ];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }
}

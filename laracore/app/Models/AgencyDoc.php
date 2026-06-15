<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AgencyDoc extends Model
{
    protected $table = 'agency_docs';
    protected $primaryKey = 'agency_doc_id';
    public $timestamps = false;

    protected $fillable = [
        'agency_id', 'doc_company', 'doc_type', 'doc_start', 'doc_and', 'agency_file', 'comment', 'update', 'del'
    ];

    protected $casts = [
        'agency_id' => 'integer',
        'doc_company' => 'integer',
        'doc_type' => 'integer',
        'doc_start' => 'date',
        'doc_and' => 'date',
        'update' => 'integer',
        'del' => 'boolean',
    ];

    public function agency()
    {
        return $this->belongsTo(Agency::class, 'agency_id', 'agency_id');
    }

    public function isExpired(): bool
    {
        if (!$this->doc_and) {
            return false;
        }
        return $this->doc_and->isPast();
    }
}

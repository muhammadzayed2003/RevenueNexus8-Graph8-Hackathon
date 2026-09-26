<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Graph8Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'external_id',
        'event_type',
        'source',
        'company_id',
        'contact_id',
        'deal_id',
        'payload',
        'occurred_at',
        'processed',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'occurred_at' => 'datetime',
            'processed' => 'boolean',
            'processed_at' => 'datetime',
        ];
    }
}
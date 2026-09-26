<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Graph8Record extends Model
{
    use HasFactory;

    protected $fillable = [
        'record_type',
        'external_id',
        'name',
        'payload',
        'synced_at',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
            'synced_at' => 'datetime',
        ];
    }
}
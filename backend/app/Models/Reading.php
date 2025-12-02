<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reading extends Model
{
    protected $table = 'readings';
    public $timestamps = false;

    protected $casts = [
        'payload' => 'array',
        'created_at' => 'datetime',
    ];

    protected $fillable = [
        'topic',
        'toilet_id',
        'sensor_type',
        'payload',
        'created_at',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ad extends Model
{
    protected $fillable = [
        'name',
        'placement',
        'town_id',
        'image_url',
        'html_snippet',
        'target_url',
        'starts_at',
        'ends_at',
        'is_active',
        'impressions',
        'clicks',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_active' => 'bool',
        'impressions' => 'int',
        'clicks' => 'int',
    ];

    public function town(): BelongsTo
    {
        return $this->belongsTo(Town::class);
    }
}

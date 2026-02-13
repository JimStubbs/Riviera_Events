<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeaturedItem extends Model
{
    protected $fillable = [
        'event_series_id',
        'starts_at',
        'ends_at',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'sort_order' => 'int',
        'is_active' => 'bool',
    ];

    public function eventSeries(): BelongsTo
    {
        return $this->belongsTo(EventSeries::class);
    }
}

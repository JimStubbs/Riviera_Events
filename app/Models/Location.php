<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Location extends Model
{
    protected $fillable = [
        'town_id',
        'name',
        'slug',
        'address',
        'lat',
        'lng',
        'timezone',
        'is_active',
    ];

    protected $casts = [
        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
        'is_active' => 'bool',
    ];

    public function town(): BelongsTo
    {
        return $this->belongsTo(Town::class);
    }

    public function eventSeries(): HasMany
    {
        return $this->hasMany(EventSeries::class);
    }
}

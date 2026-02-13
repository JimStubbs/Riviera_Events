<?php

namespace App\Models;

use App\Enums\EventSeriesStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventSeries extends Model
{
    protected $table = 'event_series';

    protected $fillable = [
        'organizer_user_id',
        'contact_email',
        'town_id',
        'location_id',
        'category_id',
        'title',
        'slug',
        'description',
        'image_url',
        'timezone',
        'is_all_day',
        'starts_at_local',
        'ends_at_local',
        'rrule',
        'until_local',
        'count',
        'exdates',
        'is_premium',
        'premium_price_mxn',
        'premium_paid_at',
        'stripe_checkout_session_id',
        'stripe_payment_intent_id',
        'status',
        'published_data',
        'draft_data',
        'last_submitted_at',
        'last_approved_at',
        'rejection_reason',
    ];

    public function isApproved(): bool
    {
        return $this->status === EventSeriesStatus::Approved->value;
    }


    protected $casts = [
        'is_all_day' => 'bool',
        'starts_at_local' => 'datetime',
        'ends_at_local' => 'datetime',
        'until_local' => 'datetime',
        'exdates' => 'array',
        'is_premium' => 'bool',
        'premium_price_mxn' => 'int',
        'premium_paid_at' => 'datetime',
        'published_data' => 'array',
        'draft_data' => 'array',
        'last_submitted_at' => 'datetime',
        'last_approved_at' => 'datetime',
    ];

    public function organizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'organizer_user_id');
    }

    public function town(): BelongsTo
    {
        return $this->belongsTo(Town::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function featuredItems(): HasMany
    {
        return $this->hasMany(FeaturedItem::class);
    }
}

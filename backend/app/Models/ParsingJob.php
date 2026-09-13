<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int                    $id
 * @property int                    $organization_id
 * @property string                 $status          queued
 * @property int                    $reviews_fetched 0
 * @property int                    $reviews_total_estimate
 * @property int                    $attempt
 * @property string                 $error_message
 * @property Carbon                 $started_at
 * @property Carbon                 $finished_at
 * @property Carbon                 $created_at
 * @property Carbon                 $updated_at
 *
 * @property BelongsTo|Organization $organization
 */
final class ParsingJob extends Model
{
    protected $fillable = [
        'organization_id',
        'status',
        'reviews_fetched',
        'reviews_total_estimate',
        'attempt',
        'error_message',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}

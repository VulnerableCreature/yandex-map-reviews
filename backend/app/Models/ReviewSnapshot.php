<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int                    $id
 * @property int                    $organization_id
 * @property int                    $parsing_job_id
 * @property float                  $rating
 * @property integer                $ratings_count
 * @property integer                $reviews_count
 * @property integer                $reviews_added
 * @property integer                $reviews_updated
 * @property integer                $reviews_removed
 * @property Carbon                 $captured_at
 * @property Carbon                 $crated_at
 * @property Carbon                 $updated_at
 *
 * @property BelongsTo|Organization $organization
 * @property BelongsTo|ParsingJob   $parsingJob
 */
final class ReviewSnapshot extends Model
{
    protected $fillable = [
        'organization_id',
        'parsing_job_id',
        'rating',
        'ratings_count',
        'reviews_count',
        'reviews_added',
        'reviews_updated',
        'reviews_removed',
        'captured_at',
    ];

    protected $casts = [
        'captured_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function parsingJob(): BelongsTo
    {
        return $this->belongsTo(ParsingJob::class);
    }
}

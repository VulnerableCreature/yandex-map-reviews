<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * @property int                                     $id
 * @property int                                     $user_id
 * @property string                                  $source_url
 * @property string                                  $normalized_url
 * @property string                                  $yandex_permalink
 * @property ?string                                 $name
 * @property float                                   $rating
 * @property int                                     $ratings_count
 * @property int                                     $reviews_count
 * @property string                                  $parsing_status
 * @property string                                  $parsing_error
 * @property Carbon                                  $last_parsed_at
 * @property Carbon                                  $created_at
 * @property Carbon                                  $updated_at
 *
 * @property BelongsTo|User                          $user
 * @property HasMany|Collection<int, Review>         $reviews
 * @property HasMany|Collection<int, ReviewSnapshot> $snapshots
 * @property HasMany|Collection<int, ParsingJob>     $parsingJobs
 */
final class Organization extends Model
{
    protected $fillable = [
        'user_id',
        'source_url',
        'normalized_url',
        'yandex_permalink',
        'name',
        'rating',
        'ratings_count',
        'reviews_count',
        'parsing_status',
        'parsing_error',
        'last_parsed_at',
    ];

    protected $casts = [
        'rating' => 'decimal:2',
        'last_parsed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function snapshots(): HasMany
    {
        return $this->hasMany(ReviewSnapshot::class);
    }

    public function parsingJobs(): HasMany
    {
        return $this->hasMany(ParsingJob::class);
    }
}

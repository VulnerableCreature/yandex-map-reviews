<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int                    $id
 * @property int                    $organization_id
 * @property string                 $external_id
 * @property string                 $author
 * @property integer                $rating
 * @property string                 $text
 * @property Carbon                 $published_at
 * @property string                 $content_hash
 * @property Carbon                 $created_at
 * @property Carbon                 $updated_at
 *
 * @property BelongsTo|Organization $organization
 */
final class Review extends Model
{
    protected $fillable = [
        'organization_id',
        'external_id',
        'author',
        'rating',
        'text',
        'published_at',
        'content_hash',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }
}

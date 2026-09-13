<?php

namespace App\Http\Resources;

use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class ReviewResource extends JsonResource
{
    public $resource = Review::class;

    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'author' => $this->resource->author,
            'rating' => $this->resource->rating,
            'text' => $this->resource->text,
            'published_at' => $this->resource->published_at?->toIso8601String(),
        ];
    }
}

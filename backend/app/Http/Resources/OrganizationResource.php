<?php

namespace App\Http\Resources;

use App\Models\Organization;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

final class OrganizationResource extends JsonResource
{
    public $resource = Organization::class;
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'source_url' => $this->resource->source_url,
            'name' => $this->resource->name,
            'rating' => $this->resource->rating !== null ? $this->resource->rating : null,
            'ratings_count' => $this->resource->ratings_count,
            'reviews_count' => $this->resource->reviews_count,
            'parsing_status' => $this->resource->parsing_status,
            'parsing_error' => $this->resource->parsing_error,
        ];
    }
}

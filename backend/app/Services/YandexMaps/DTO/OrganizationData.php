<?php

namespace App\Services\YandexMaps\DTO;

final readonly class OrganizationData
{
    /**
     * @param ReviewData[] $reviews
     */
    public function __construct(
        public ?string $name,
        public ?float  $rating,
        public ?int    $ratingsCount,
        public ?int    $reviewsCount,
        public array   $reviews,
    ) {}
}

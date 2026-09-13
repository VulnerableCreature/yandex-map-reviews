<?php

namespace App\Repositories;

use App\Models\Organization;
use App\Repositories\Contracts\ReviewSnapshotRepositoryInterface;
use App\Services\YandexMaps\DTO\OrganizationData;

final class ReviewSnapshotRepository implements ReviewSnapshotRepositoryInterface
{
    public function createFromParsingJob(Organization $organization, int $parsingJobId, OrganizationData $data, array $diff): void
    {
        $organization->snapshots()->create([
            'parsing_job_id' => $parsingJobId,
            'rating' => $data->rating,
            'ratings_count' => $data->ratingsCount,
            'reviews_count' => $data->reviewsCount,
            'reviews_added' => $diff['added'],
            'reviews_updated' => $diff['updated'],
            'reviews_removed' => $diff['removed'] ?? 0,
            'captured_at' => now(),
        ]);
    }
}

<?php

namespace App\Repositories;

use App\Models\Organization;
use App\Models\Review;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ReviewRepository implements ReviewRepositoryInterface
{
    public function paginateForOrganization(Organization $organization, int $perPage = 50, int $page = 1): LengthAwarePaginator
    {
        return Review::query()
            ->where('organization_id', $organization->id)
            ->orderByDesc('published_at')
            ->paginate($perPage, ['*'], 'page', $page);
    }

    public function upsertBatch(Organization $organization, array $reviews): array
    {
        if (empty($reviews)) {
            return ['added' => 0, 'updated' => 0];
        }

        $existingHashes = Review::query()
            ->where('organization_id', $organization->id)
            ->whereIn('external_id', array_column($reviews, 'external_id'))
            ->pluck('content_hash', 'external_id');

        $added = 0;
        $updated = 0;

        foreach ($reviews as $review) {
            $isNew = !$existingHashes->has($review['external_id']);
            $isChanged = !$isNew && $existingHashes->get($review['external_id']) !== $review['content_hash'];

            if ($isNew) {
                $added++;
            } else if ($isChanged) {
                $updated++;
            }

            Review::query()->updateOrCreate(
                [
                    'organization_id' => $organization->id,
                    'external_id' => $review['external_id'],
                ],
                [
                    'author' => $review['author'],
                    'rating' => $review['rating'],
                    'text' => $review['text'],
                    'published_at' => $review['published_at'],
                    'content_hash' => $review['content_hash'],
                ]
            );
        }

        return ['added' => $added, 'updated' => $updated];
    }

    public function existingExternalIds(Organization $organization): array
    {
        return Review::query()
            ->where('organization_id', $organization->id)
            ->pluck('external_id')
            ->all();
    }
}

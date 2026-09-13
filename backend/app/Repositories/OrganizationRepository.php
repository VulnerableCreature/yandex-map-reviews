<?php

namespace App\Repositories;

use App\Models\Organization;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use App\Services\YandexMaps\DTO\OrganizationData;

class OrganizationRepository implements OrganizationRepositoryInterface
{
    public function findById(int $organizationId): ?Organization
    {
        return Organization::query()->find($organizationId);
    }

    public function findForUser(int $userId, int $organizationId): ?Organization
    {
        return Organization::query()
            ->where('user_id', $userId)
            ->where('id', $organizationId)
            ->first();
    }

    public function firstForUser(int $userId): ?Organization
    {
        return Organization::query()
            ->where('user_id', $userId)
            ->latest('id')
            ->first();
    }

    public function findByNormalizedUrl(string $normalizedUrl): ?Organization
    {
        return Organization::query()->where('normalized_url', $normalizedUrl)->first();
    }

    public function createForUser(int $userId, string $sourceUrl, string $normalizedUrl, string $permalink): Organization
    {
        return Organization::query()->create([
            'user_id' => $userId,
            'source_url' => $sourceUrl,
            'normalized_url' => $normalizedUrl,
            'yandex_permalink' => $permalink,
            'parsing_status' => 'pending',
        ]);
    }

    public function updateSourceData(Organization $organization, string $sourceUrl, string $normalizedUrl, string $permalink): void
    {
        $organization->update([
            'source_url' => $sourceUrl,
            'normalized_url' => $normalizedUrl,
            'yandex_permalink' => $permalink,
        ]);
    }

    public function markStatus(Organization $organization, string $status, ?string $error = null): void
    {
        $organization->update([
            'parsing_status' => $status,
            'parsing_error' => $error,
        ]);
    }

    public function applyParsedData(Organization $organization, OrganizationData $data): void
    {
        $organization->update([
            'name' => $data->name ?? $organization->name,
            'rating' => $data->rating ?? $organization->rating,
            'ratings_count' => $data->ratingsCount ?? $organization->ratings_count,
            'reviews_count' => $data->reviewsCount ?? $organization->reviews_count,
            'parsing_status' => 'done',
            'parsing_error' => null,
            'last_parsed_at' => now(),
        ]);
    }
}

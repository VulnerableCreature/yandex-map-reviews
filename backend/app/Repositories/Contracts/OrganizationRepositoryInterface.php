<?php

namespace App\Repositories\Contracts;

use App\Models\Organization;
use App\Services\YandexMaps\DTO\OrganizationData;

interface OrganizationRepositoryInterface
{
    public function findById(int $organizationId): ?Organization;

    public function findForUser(int $userId, int $organizationId): ?Organization;

    public function firstForUser(int $userId): ?Organization;

    public function findByNormalizedUrl(string $normalizedUrl): ?Organization;

    public function createForUser(int $userId, string $sourceUrl, string $normalizedUrl, string $permalink): Organization;

    public function updateSourceData(Organization $organization, string $sourceUrl, string $normalizedUrl, string $permalink): void;

    public function markStatus(Organization $organization, string $status, ?string $error = null): void;

    public function applyParsedData(Organization $organization, OrganizationData $data): void;
}

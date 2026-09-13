<?php

namespace App\Repositories\Contracts;

use App\Models\Organization;
use App\Services\YandexMaps\DTO\OrganizationData;

interface ReviewSnapshotRepositoryInterface
{
    public function createFromParsingJob(Organization $organization, int $parsingJobId, OrganizationData $data, array $diff): void;
}

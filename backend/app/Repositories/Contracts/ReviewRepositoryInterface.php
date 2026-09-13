<?php

namespace App\Repositories\Contracts;

use App\Models\Organization;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ReviewRepositoryInterface
{
    public function paginateForOrganization(Organization $organization, int $perPage = 50, int $page = 1): LengthAwarePaginator;

    public function upsertBatch(Organization $organization, array $reviews): array;

    public function existingExternalIds(Organization $organization): array;
}

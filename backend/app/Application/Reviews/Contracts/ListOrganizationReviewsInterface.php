<?php

namespace App\Application\Reviews\Contracts;

use App\Models\Organization;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ListOrganizationReviewsInterface
{
    public function execute(int $userId, int $page, int $perPage): ?LengthAwarePaginator;
}

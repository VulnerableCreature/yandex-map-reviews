<?php

namespace App\Application\Reviews;

use App\Application\Reviews\Contracts\ListOrganizationReviewsInterface;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

final readonly class ListOrganizationReviewsService implements ListOrganizationReviewsInterface
{
    public function __construct(
        private OrganizationRepositoryInterface $organizations,
        private ReviewRepositoryInterface       $reviews,
    ) {
    }

    public function execute(int $userId, int $page, int $perPage): ?LengthAwarePaginator
    {
        $organization = $this->organizations->firstForUser($userId);

        if ($organization === null) {
            return null;
        }

        return $this->reviews->paginateForOrganization($organization, $perPage, max(1, $page));
    }
}

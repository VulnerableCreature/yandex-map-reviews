<?php

namespace App\Application\Organizations;

use App\Application\Organizations\Contracts\GetCurrentOrganizationInterface;
use App\Models\Organization;
use App\Repositories\Contracts\OrganizationRepositoryInterface;

final class GetCurrentOrganizationService implements GetCurrentOrganizationInterface
{
    public function __construct(private readonly OrganizationRepositoryInterface $organizations) {}

    public function execute(int $userId): ?Organization
    {
        return $this->organizations->firstForUser($userId);
    }
}

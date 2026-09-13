<?php

namespace App\Application\Organizations\Contracts;

use App\Models\Organization;

interface GetCurrentOrganizationInterface
{
    public function execute(int $userId): ?Organization;
}

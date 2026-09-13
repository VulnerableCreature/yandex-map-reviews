<?php

namespace App\Application\Organizations\Contracts;

use App\Models\Organization;

interface ReparseOrganizationInterface
{
    public function execute(int $userId): Organization;
}

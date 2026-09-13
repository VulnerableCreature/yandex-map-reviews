<?php

namespace App\Application\Organizations\Contracts;

use App\Models\Organization;

interface ConnectOrganizationInterface
{
    public function execute(int $userId, string $url): Organization;
}

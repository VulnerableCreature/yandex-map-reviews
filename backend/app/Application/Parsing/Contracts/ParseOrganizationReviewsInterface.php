<?php

namespace App\Application\Parsing\Contracts;

interface ParseOrganizationReviewsInterface
{
    public function execute(int $organizationId, int $parsingJobId, int $attempt): void;
}

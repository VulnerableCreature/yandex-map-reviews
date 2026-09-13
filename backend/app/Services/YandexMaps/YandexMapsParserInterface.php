<?php

namespace App\Services\YandexMaps;

use App\Services\YandexMaps\DTO\OrganizationData;

interface YandexMapsParserInterface
{
    public function fetchOrganization(string $permalink, ?callable $onProgress = null): OrganizationData;
}

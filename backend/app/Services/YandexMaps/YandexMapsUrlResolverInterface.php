<?php

namespace App\Services\YandexMaps;

interface YandexMapsUrlResolverInterface
{
    public function resolve(string $url): string;
}

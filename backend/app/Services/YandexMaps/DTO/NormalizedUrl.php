<?php

declare(strict_types=1);

namespace App\Services\YandexMaps\DTO;

final readonly class NormalizedUrl
{
    public function __construct(
        public string  $original,
        public string  $normalized,
        public ?string $permalink,
        public bool    $requiresRedirectResolution,
    ) {}
}

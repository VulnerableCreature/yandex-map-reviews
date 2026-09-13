<?php

namespace App\Services\YandexMaps;

use App\Services\YandexMaps\DTO\NormalizedUrl;
use App\Services\YandexMaps\Exceptions\InvalidOrganizationUrlException;

final readonly class YandexMapsUrlValidator
{
    private const array ALLOWED_HOSTS = [
        'yandex.ru',
        'yandex.com',
        'maps.yandex.ru',
        'maps.yandex.com',
    ];

    /**
     * @throws InvalidOrganizationUrlException
     */
    public function validateAndNormalize(string $url): NormalizedUrl
    {
        $url = trim($url);

        $parts = parse_url($url);

        if ($parts === false || empty($parts['host']) || empty($parts['scheme'])) {
            throw new InvalidOrganizationUrlException('Ссылка не распознана как корректный URL.');
        }

        $host = strtolower(preg_replace('/^www\./', '', $parts['host']));

        if (!in_array($host, self::ALLOWED_HOSTS, true)) {
            throw new InvalidOrganizationUrlException('Ссылка должна вести на yandex.ru/maps или maps.yandex.ru.');
        }

        $path = $parts['path'] ?? '';
        parse_str($parts['query'] ?? '', $query);

        $permalink = $this->extractPermalink($path, $query);

        $isShortLink = (bool)preg_match('#/-/[A-Za-z0-9_-]+#', $path);

        if ($permalink === null && !$isShortLink) {
            throw new InvalidOrganizationUrlException(
                'В ссылке не найден идентификатор организации (oid/permalink). ' .
                'Похоже, это не ссылка на карточку конкретной организации.'
            );
        }

        $normalized = $permalink !== null
            ? sprintf('https://yandex.ru/maps/org/_/%s/', $permalink)
            : 'https://yandex.ru/maps' . $path;

        return new NormalizedUrl(
            original                  : $url,
            normalized                : $normalized,
            permalink                 : $permalink,
            requiresRedirectResolution: $isShortLink,
        );
    }

    private function extractPermalink(string $path, array $query): ?string
    {
        if (preg_match('#/maps/org/[^/]+/(\d+)#', $path, $m)) {
            return $m[1];
        }

        // ?oid=1234567890
        if (!empty($query['oid']) && ctype_digit((string)$query['oid'])) {
            return (string)$query['oid'];
        }

        return null;
    }
}

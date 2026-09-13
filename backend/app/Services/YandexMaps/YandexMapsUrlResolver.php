<?php

namespace App\Services\YandexMaps;

use App\Services\YandexMaps\Exceptions\InvalidOrganizationUrlException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

final class YandexMapsUrlResolver implements YandexMapsUrlResolverInterface
{
    /**
     * @throws InvalidOrganizationUrlException
     */
    public function resolve(string $url): string
    {
        try {
            $response = Http::withOptions(['allow_redirects' => true])->get($url);
        } catch (ConnectionException $e) {
            throw new InvalidOrganizationUrlException('Не удалось открыть короткую ссылку Яндекс.Карт.', previous: $e);
        }

        if (! $response->successful()) {
            throw new InvalidOrganizationUrlException('Короткая ссылка Яндекс.Карт недоступна.');
        }

        $finalUrl = $response->effectiveUri();

        if (! $finalUrl) {
            throw new InvalidOrganizationUrlException('Не удалось определить конечный адрес короткой ссылки.');
        }

        return (string) $finalUrl;
    }
}

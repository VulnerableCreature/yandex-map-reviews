<?php

namespace App\Services\YandexMaps;

use App\Services\YandexMaps\DTO\OrganizationData;
use App\Services\YandexMaps\DTO\ReviewData;
use App\Services\YandexMaps\Exceptions\ParsingException;
use App\Services\YandexMaps\Exceptions\SourceMarkupChangedException;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Random\RandomException;

final class YandexMapsParserService implements YandexMapsParserInterface
{
    private const int REVIEWS_PAGE_SIZE = 50;

    private const int MAX_REVIEWS = 600;

    private Client $http;

    private CookieJar $cookies;

    public function __construct(?Client $http = null)
    {
        $this->cookies = new CookieJar();
        $this->http = $http ?? new Client([
            'timeout' => config('services.yandex_maps.timeout', 15),
            'http_errors' => false,
            'cookies' => $this->cookies,
            'headers' => [
                'Accept-Language' => 'ru-RU,ru;q=0.9',
            ],
        ]);
    }

    /**
     * @throws SourceMarkupChangedException
     * @throws RandomException
     * @throws ParsingException
     */
    public function fetchOrganization(string $permalink, ?callable $onProgress = null): OrganizationData
    {
        $summary = $this->fetchBusinessSummary($permalink);
        [$reviews, $total] = $this->fetchAllReviews($permalink, $onProgress);

        return new OrganizationData(
            name        : $summary['name'],
            rating      : $summary['rating'],
            ratingsCount: $summary['ratingsCount'],
            reviewsCount: $summary['reviewsCount'] ?? $total,
            reviews     : $reviews,
        );
    }

    private function fetchBusinessSummary(string $permalink): array
    {
        $url = 'https://yandex.ru/maps/org/_/' . rawurlencode($permalink) . '/';

        try {
            $response = $this->http->get($url, [
                'headers' => array_merge($this->buildHeaders(), [
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Referer' => 'https://yandex.ru/maps/',
                ]),
            ]);
        } catch (GuzzleException $e) {
            Log::warning('yandex_maps_parser: business page request failed', ['error' => $e->getMessage()]);
            return $this->emptySummary();
        }

        if ($response->getStatusCode() >= 400) {
            Log::warning('yandex_maps_parser: business page returned error', ['status' => $response->getStatusCode()]);
            return $this->emptySummary();
        }

        $html = (string)$response->getBody();
        if ($html === '') {
            return $this->emptySummary();
        }
        $name = $this->matchJsonString($html, ['title', 'name']);

        $rating = $this->matchNumber($html, ['totalScore', 'ratingValue', 'rating']);

        $ratingsCount = $this->matchInteger($html, ['ratingCount', 'ratingsCount']);
        $reviewsCount = $this->matchInteger($html, ['reviewsCount', 'reviewCount']);

        return compact('name', 'rating', 'ratingsCount', 'reviewsCount');
    }

    /**
     * @throws SourceMarkupChangedException
     * @throws ParsingException|RandomException
     */
    private function fetchAllReviews(string $permalink, ?callable $onProgress): array
    {
        $csrfToken = $this->openReviewsSession();
        $reviews = [];
        $page = 1;
        $total = null;

        while (count($reviews) < self::MAX_REVIEWS) {
            if ($page > 1) {
                $this->throttle();
            }

            $payload = $this->requestReviewsPage($permalink, $csrfToken, $page);
            $data = $payload['data'] ?? null;
            $items = is_array($data) ? ($data['reviews'] ?? null) : null;

            if (!is_array($items)) {
                throw new SourceMarkupChangedException(
                    'Не найдены отзывы в ответе fetchReviews контракт внутреннего API Яндекса изменился'
                );
            }

            $pager = is_array($data['pager'] ?? null) ? $data['pager'] : [];
            if (isset($pager['total']) && is_numeric($pager['total'])) {
                $total = (int)$pager['total'];
            }

            if (isset($payload['csrfToken']) && is_string($payload['csrfToken'])) {
                $csrfToken = $payload['csrfToken'];
            }

            if ($items === []) {
                break;
            }

            foreach ($items as $item) {
                if (!is_array($item)) {
                    continue;
                }
                $review = $this->mapReview($item);
                if ($review !== null) {
                    $reviews[] = $review;
                    if (count($reviews) >= self::MAX_REVIEWS) {
                        break;
                    }
                }
            }

            if ($onProgress !== null) {
                $onProgress(count($reviews), $total);
            }

            $offset = isset($pager['offset']) ? (int)$pager['offset'] : (($page - 1) * self::REVIEWS_PAGE_SIZE);
            $limit = isset($pager['limit']) ? (int)$pager['limit'] : self::REVIEWS_PAGE_SIZE;
            if (($total !== null && $offset + $limit >= $total) || count($items) < self::REVIEWS_PAGE_SIZE) {
                break;
            }

            $page++;
        }

        return [$reviews, $total];
    }

    /**
     * @throws SourceMarkupChangedException
     * @throws ParsingException
     */
    private function openReviewsSession(): string
    {
        try {
            $response = $this->http->post($this->fetchReviewsUrl(), [
                'headers' => array_merge($this->buildHeaders(), [
                    'Accept' => 'application/json, text/plain, */*',
                    'Origin' => 'https://yandex.ru',
                    'Referer' => 'https://yandex.ru/maps/',
                ]),
            ]);
        } catch (GuzzleException $e) {
            throw new ParsingException('Не удалось открыть сессию Яндекс.Карт: ' . $e->getMessage(), previous: $e);
        }

        $payload = $this->decodeJsonResponse($response->getStatusCode(), (string)$response->getBody());
        $token = $payload['csrfToken'] ?? null;

        if (!is_string($token) || $token === '') {
            throw new SourceMarkupChangedException('Яндекс не вернул csrfToken при открытии fetchReviews-сессии.');
        }

        return $token;
    }

    /**
     * @throws SourceMarkupChangedException
     * @throws RandomException
     * @throws ParsingException
     */
    private function requestReviewsPage(string $permalink, string $csrfToken, int $page): array
    {
        $nowMs = (int)floor(microtime(true) * 1000);
        $params = [
            'ajax' => '1',
            'businessId' => $permalink,
            'csrfToken' => $csrfToken,
            'locale' => 'ru_RU',
            'page' => (string)$page,
            'pageSize' => (string)self::REVIEWS_PAGE_SIZE,
            'ranking' => 'by_time',
            'reqId' => $nowMs . '-' . random_int(100000000, 999999999) . '-sas1-1',
            'sessionId' => $nowMs . '_' . random_int(100000, 999999),
        ];
        $params['s'] = $this->yandexSignature($params);

        try {
            $response = $this->http->get($this->fetchReviewsUrl(), [
                'query' => $params,
                'headers' => array_merge($this->buildHeaders(), [
                    'Accept' => 'application/json, text/plain, */*',
                    'Referer' => 'https://yandex.ru/maps/org/_/' . $permalink . '/reviews/',
                    'X-Requested-With' => 'XMLHttpRequest',
                ]),
            ]);
        } catch (GuzzleException $e) {
            throw new ParsingException('Не удалось получить отзывы Яндекс.Карт: ' . $e->getMessage(), previous: $e);
        }

        return $this->decodeJsonResponse($response->getStatusCode(), (string)$response->getBody());
    }

    /**
     * @throws SourceMarkupChangedException
     * @throws ParsingException
     */
    private function decodeJsonResponse(int $status, string $body): array
    {
        if ($status === 403 || $status === 429) {
            throw new ParsingException("Яндекс вернул HTTP $status");
        }
        if ($status >= 400) {
            throw new ParsingException("Яндекс вернул HTTP $status");
        }
        if (trim($body) === '') {
            throw new ParsingException('Яндекс вернул пустой ответ fetchReviews.');
        }

        $decoded = json_decode($body, true);
        if (!is_array($decoded)) {
            throw new SourceMarkupChangedException('fetchReviews вернул не JSON из-за отсутствуия отзывов');
        }

        return $decoded;
    }

    private function yandexSignature(array $params): string
    {
        unset($params['s']);
        ksort($params, SORT_STRING);
        $encoded = http_build_query($params, '', '&', PHP_QUERY_RFC3986);
        $hash = 5381;

        foreach (unpack('C*', $encoded) ? : [] as $byte) {
            $hash = (($hash * 33) ^ $byte) & 0xFFFFFFFF;
        }

        return sprintf('%u', $hash);
    }

    private function mapReview(array $item): ?ReviewData
    {
        $externalId = $item['reviewId'] ?? $item['id'] ?? null;
        if ($externalId === null) {
            Log::warning('yandex_maps_parser: review without id skipped');
            return null;
        }

        return new ReviewData(
            externalId : (string)$externalId,
            author     : $item['author']['name'] ?? null,
            rating     : isset($item['rating']) && is_numeric($item['rating']) ? (int)round((float)$item['rating']) : null,
            text       : isset($item['text']) ? (string)$item['text'] : null,
            publishedAt: $item['date'] ?? $item['updatedTime'] ?? $item['createdTime'] ?? null,
        );
    }

    private function matchJsonString(string $fragment, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (preg_match('/["\\\\]' . preg_quote($key, '/') . '["\\\\]\s*:\s*["\\\\]((?:\\\\.|[^"\\\\])*)["\\\\]/u', $fragment, $m)) {
                $value = json_decode('"' . str_replace('"', '\\"', $m[1]) . '"', true);
                return is_string($value) ? $value : stripcslashes($m[1]);
            }
        }
        return null;
    }

    private function matchNumber(string $fragment, array $keys): ?float
    {
        foreach ($keys as $key) {
            if (preg_match('/["\\\\]' . preg_quote($key, '/') . '["\\\\]\s*:\s*([0-9]+(?:\.[0-9]+)?)/', $fragment, $m)) {
                return (float)$m[1];
            }
        }
        return null;
    }

    private function matchInteger(string $fragment, array $keys): ?int
    {
        $number = $this->matchNumber($fragment, $keys);
        return $number !== null ? (int)$number : null;
    }

    private function emptySummary(): array
    {
        return ['name' => null, 'rating' => null, 'ratingsCount' => null, 'reviewsCount' => null];
    }

    private function buildHeaders(): array
    {
        $userAgents = config('services.yandex_maps.user_agents', [
            'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/128.0 Safari/537.36',
        ]);

        return ['User-Agent' => $userAgents[array_rand($userAgents)]];
    }

    /**
     * @throws RandomException
     */
    private function throttle(): void
    {
        $delayMs = (int)config('services.yandex_maps.throttle_ms', 400);
        $jitterMs = random_int(0, max(1, (int)($delayMs * 0.5)));
        usleep(($delayMs + $jitterMs) * 1000);
    }

    private function fetchReviewsUrl(): string
    {
        $base = rtrim((string)config('services.yandex_maps.api_base', 'https://yandex.ru/maps/api/business'), '/');
        return $base . '/fetchReviews';
    }
}

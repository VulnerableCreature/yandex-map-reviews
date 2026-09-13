<?php

namespace App\Services\YandexMaps\DTO;

final readonly class ReviewData
{
    public function __construct(
        public string  $externalId,
        public ?string $author,
        public ?int    $rating,
        public ?string $text,
        public ?string $publishedAt,
    ) {}

    public function contentHash(): string
    {
        return hash('sha256', implode('|', [
            $this->author ?? '',
            $this->rating ?? '',
            $this->text ?? '',
            $this->publishedAt ?? '',
        ]));
    }

    public function toArray(): array
    {
        return [
            'external_id' => $this->externalId,
            'author' => $this->author,
            'rating' => $this->rating,
            'text' => $this->text,
            'published_at' => $this->publishedAt,
            'content_hash' => $this->contentHash(),
        ];
    }
}

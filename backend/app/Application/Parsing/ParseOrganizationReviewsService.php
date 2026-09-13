<?php

namespace App\Application\Parsing;

use App\Application\Organizations\Enums\MarkUpStatusEnum;
use App\Application\Parsing\Contracts\ParseOrganizationReviewsInterface;
use App\Models\Organization;
use App\Models\ParsingJob;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use App\Repositories\Contracts\ParsingJobRepositoryInterface;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use App\Repositories\Contracts\ReviewSnapshotRepositoryInterface;
use App\Services\YandexMaps\YandexMapsParserInterface;
use RuntimeException;

final readonly class ParseOrganizationReviewsService implements ParseOrganizationReviewsInterface
{
    public function __construct(
        private OrganizationRepositoryInterface   $organizations,
        private ParsingJobRepositoryInterface     $parsingJobs,
        private ReviewRepositoryInterface         $reviews,
        private ReviewSnapshotRepositoryInterface $snapshots,
        private YandexMapsParserInterface         $parser,
    ) {}

    public function execute(int $organizationId, int $parsingJobId, int $attempt): void
    {
        $organization = $this->organizations->findById($organizationId);
        $job = $this->parsingJobs->find($parsingJobId);

        if ($organization === null || $job === null) {
            return;
        }

        if ($organization->yandex_permalink === null) {
            $this->fail($organization, $job, 'Organization permalink is not set.');
            return;
        }

        $this->parsingJobs->markInProgress($job, $attempt);
        $this->organizations->markStatus($organization, MarkUpStatusEnum::IN_PROGRESS->value);

        $data = $this->parser->fetchOrganization(
            $organization->yandex_permalink,
            fn(int $fetched, ?int $total) => $this->parsingJobs->updateProgress($job, $fetched, $total),
        );

        $diff = $this->reviews->upsertBatch(
            $organization,
            array_map(static fn($review) => $review->toArray(), $data->reviews),
        );

        $this->organizations->applyParsedData($organization, $data);
        $this->parsingJobs->markDone($job, count($data->reviews));
        $this->snapshots->createFromParsingJob($organization, $job->id, $data, $diff);
    }

    private function fail(Organization $organization, ParsingJob $job, string $message): void
    {
        $this->organizations->markStatus($organization, 'failed', $message);
        $this->parsingJobs->markFailed($job, $message);
        throw new RuntimeException($message);
    }
}

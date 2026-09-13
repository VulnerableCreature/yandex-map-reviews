<?php

namespace App\Application\Organizations;

use App\Application\Organizations\Contracts\ReparseOrganizationInterface;
use App\Application\Organizations\Enums\MarkUpStatusEnum;
use App\Jobs\ParseOrganizationReviewsJob;
use App\Models\Organization;
use App\Application\Organizations\Exceptions\OrganizationNotConnectedException;
use App\Repositories\Contracts\ParsingJobRepositoryInterface;
use App\Repositories\Contracts\OrganizationRepositoryInterface;

final readonly class ReparseOrganizationService implements ReparseOrganizationInterface
{
    public function __construct(
        private OrganizationRepositoryInterface $organizations,
        private ParsingJobRepositoryInterface   $parsingJobs,
    ) {}

    public function execute(int $userId): Organization
    {
        $organization = $this->organizations->firstForUser($userId);

        if ($organization === null) {
            throw new OrganizationNotConnectedException('Организация не подключена.');
        }

        $this->organizations->markStatus($organization, MarkUpStatusEnum::QUEUED->value);

        $job = $this->parsingJobs->createQueued($organization->id);

        ParseOrganizationReviewsJob::dispatch($organization->id, $job->id)->afterCommit();

        return $organization->fresh();
    }
}

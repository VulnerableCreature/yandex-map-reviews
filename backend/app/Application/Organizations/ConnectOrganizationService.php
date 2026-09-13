<?php

namespace App\Application\Organizations;

use App\Application\Organizations\Contracts\ConnectOrganizationInterface;
use App\Jobs\ParseOrganizationReviewsJob;
use App\Models\Organization;
use App\Repositories\Contracts\ParsingJobRepositoryInterface;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use App\Services\YandexMaps\Exceptions\InvalidOrganizationUrlException;
use App\Services\YandexMaps\YandexMapsUrlResolverInterface;
use App\Services\YandexMaps\YandexMapsUrlValidator;
use Illuminate\Support\Facades\DB;
use App\Application\Organizations\Exceptions\OrganizationAlreadyConnectedException;
use Throwable;

final readonly class ConnectOrganizationService implements ConnectOrganizationInterface
{
    public function __construct(
        private OrganizationRepositoryInterface $organizations,
        private YandexMapsUrlValidator          $urlValidator,
        private YandexMapsUrlResolverInterface  $urlResolver,
        private ParsingJobRepositoryInterface   $parsingJobs,
    ) {
    }

    /**
     * @throws Throwable
     * @throws InvalidOrganizationUrlException
     */
    public function execute(int $userId, string $url): Organization
    {
        $normalized = $this->urlValidator->validateAndNormalize($url);

        if ($normalized->requiresRedirectResolution) {
            $normalized = $this->urlValidator->validateAndNormalize(
                $this->urlResolver->resolve($normalized->original)
            );
        }

        if ($normalized->permalink === null) {
            throw new InvalidOrganizationUrlException('Не удалось определить идентификатор организации.');
        }

        return DB::transaction(function () use ($userId, $normalized): Organization {
            $organization = $this->organizations->findByNormalizedUrl($normalized->normalized);

            if ($organization !== null && $organization->user_id !== $userId) {
                throw new OrganizationAlreadyConnectedException('Эта организация уже подключена другим пользователем.');
            }

            $organization ??= $this->organizations->createForUser(
                $userId,
                $normalized->original,
                $normalized->normalized,
                $normalized->permalink,
            );

            if ($organization->user_id === $userId) {
                $this->organizations->updateSourceData(
                    $organization,
                    $normalized->original,
                    $normalized->normalized,
                    $normalized->permalink,
                );
            }

            $this->organizations->markStatus($organization, 'queued');

            $job = $this->parsingJobs->createQueued($organization->id);

            ParseOrganizationReviewsJob::dispatch($organization->id, $job->id)->afterCommit();

            return $organization->fresh();
        });
    }
}

<?php

namespace App\Providers;

use App\Application\Auth\AuthenticationService;
use App\Application\Auth\Contracts\AuthenticationServiceInterface;
use App\Application\Organizations\ConnectOrganizationService;
use App\Application\Organizations\Contracts\ConnectOrganizationInterface;
use App\Application\Organizations\Contracts\GetCurrentOrganizationInterface;
use App\Application\Organizations\Contracts\ReparseOrganizationInterface;
use App\Application\Organizations\GetCurrentOrganizationService;
use App\Application\Organizations\ReparseOrganizationService;
use App\Application\Parsing\Contracts\ParseOrganizationReviewsInterface;
use App\Application\Parsing\ParseOrganizationReviewsService;
use App\Application\Reviews\Contracts\ListOrganizationReviewsInterface;
use App\Application\Reviews\ListOrganizationReviewsService;
use App\Repositories\Contracts\OrganizationRepositoryInterface;
use App\Repositories\Contracts\ParsingJobRepositoryInterface;
use App\Repositories\Contracts\ReviewRepositoryInterface;
use App\Repositories\Contracts\ReviewSnapshotRepositoryInterface;
use App\Repositories\OrganizationRepository;
use App\Repositories\ParsingJobRepository;
use App\Repositories\ReviewRepository;
use App\Repositories\ReviewSnapshotRepository;
use App\Services\YandexMaps\YandexMapsParserInterface;
use App\Services\YandexMaps\YandexMapsParserService;
use App\Services\YandexMaps\YandexMapsUrlResolver;
use App\Services\YandexMaps\YandexMapsUrlResolverInterface;
use Illuminate\Support\ServiceProvider;

final class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $bindings = [
            OrganizationRepositoryInterface::class => OrganizationRepository::class,
            ParsingJobRepositoryInterface::class => ParsingJobRepository::class,
            ReviewRepositoryInterface::class => ReviewRepository::class,
            ReviewSnapshotRepositoryInterface::class => ReviewSnapshotRepository::class,
            YandexMapsParserInterface::class => YandexMapsParserService::class,
            YandexMapsUrlResolverInterface::class => YandexMapsUrlResolver::class,
            AuthenticationServiceInterface::class => AuthenticationService::class,
            ConnectOrganizationInterface::class => ConnectOrganizationService::class,
            GetCurrentOrganizationInterface::class => GetCurrentOrganizationService::class,
            ReparseOrganizationInterface::class => ReparseOrganizationService::class,
            ListOrganizationReviewsInterface::class => ListOrganizationReviewsService::class,
            ParseOrganizationReviewsInterface::class => ParseOrganizationReviewsService::class,
        ];

        foreach ($bindings as $abstract => $concrete) {
            $this->app->bind($abstract, $concrete);
        }
    }
}

<?php

namespace App\Jobs;

use App\Application\Parsing\Contracts\ParseOrganizationReviewsInterface;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ParseOrganizationReviewsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [30, 120, 300];
    public int $timeout = 600;

    public function __construct(
        private readonly int $organizationId,
        private readonly int $parsingJobId,
    ) {
    }

    public function handle(ParseOrganizationReviewsInterface $parser): void
    {
        $parser->execute($this->organizationId, $this->parsingJobId, $this->attempts());
    }
}

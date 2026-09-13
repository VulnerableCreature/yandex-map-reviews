<?php

namespace App\Repositories;

use App\Application\Parsing\Enums\StatusEnum;
use App\Models\ParsingJob;
use App\Repositories\Contracts\ParsingJobRepositoryInterface;

final class ParsingJobRepository implements ParsingJobRepositoryInterface
{
    public function createQueued(int $organizationId): ParsingJob
    {
        return ParsingJob::query()->create([
            'organization_id' => $organizationId,
            'status' => 'queued',
        ]);
    }

    public function find(int $id): ?ParsingJob
    {
        return ParsingJob::query()->find($id);
    }

    public function markInProgress(ParsingJob $job, int $attempt): void
    {
        $job->update([
            'status' => StatusEnum::IN_PROGRESS->value,
            'attempt' => $attempt,
            'started_at' => now(),
        ]);
    }

    public function updateProgress(ParsingJob $job, int $fetched, ?int $total): void
    {
        $job->update([
            'reviews_fetched' => $fetched,
            'reviews_total_estimate' => $total,
        ]);
    }

    public function markDone(ParsingJob $job, int $fetched): void
    {
        $job->update([
            'status' => StatusEnum::DONE->value,
            'reviews_fetched' => $fetched,
            'finished_at' => now(),
            'error_message' => null,
        ]);
    }

    public function markFailed(ParsingJob $job, string $message): void
    {
        $job->update([
            'status' => StatusEnum::FAILED->value,
            'error_message' => $message,
            'finished_at' => now(),
        ]);
    }
}

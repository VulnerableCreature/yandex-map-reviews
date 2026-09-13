<?php

namespace App\Repositories\Contracts;

use App\Models\ParsingJob;

interface ParsingJobRepositoryInterface
{
    public function createQueued(int $organizationId): ParsingJob;

    public function find(int $id): ?ParsingJob;

    public function markInProgress(ParsingJob $job, int $attempt): void;

    public function updateProgress(ParsingJob $job, int $fetched, ?int $total): void;

    public function markDone(ParsingJob $job, int $fetched): void;

    public function markFailed(ParsingJob $job, string $message): void;
}

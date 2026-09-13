<?php

namespace App\Application\Organizations\Enums;

enum MarkUpStatusEnum: string
{
    case IN_PROGRESS = 'in_progress';

    case MARKUP_CHANGED = 'markup_changed';

    case QUEUED = 'queued';

    case FAILED = 'failed';
}

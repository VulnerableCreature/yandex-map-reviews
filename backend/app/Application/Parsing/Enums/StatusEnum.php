<?php

namespace App\Application\Parsing\Enums;

enum StatusEnum: string
{
    case IN_PROGRESS = 'in_progress';

    case FAILED = 'failed';

    case DONE = 'done';
}

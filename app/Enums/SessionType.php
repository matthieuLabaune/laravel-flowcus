<?php

declare(strict_types=1);

namespace App\Enums;

enum SessionType: string
{
    case Focus = 'focus';
    case ShortBreak = 'short_break';
    case LongBreak = 'long_break';
}

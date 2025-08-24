<?php

declare(strict_types=1);

namespace App\Domain\Pomodoro\Exceptions;

use RuntimeException;

class SessionAlreadyFinished extends RuntimeException {}

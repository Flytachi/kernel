<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Thread\Entity;

final class ProcessInfo
{
    public function __construct(
        public ProcessStatus $status,
        public ?ProcessStats $stats = null
    ) {
    }
}

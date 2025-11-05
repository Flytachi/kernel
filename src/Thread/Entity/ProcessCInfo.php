<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Thread\Entity;

final class ProcessCInfo
{
    public function __construct(
        public ProcessCStatus $status,
        public ?ProcessStats $stats = null
    ) {
    }
}

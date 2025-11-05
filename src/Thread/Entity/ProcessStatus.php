<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Thread\Entity;

final class ProcessStatus
{
    public function __construct(
        public int              $pid,
        public ProcessCondition $condition,
        public int              $startedAt,
        public array            $info = []
    )
    {
    }

    /**
     * @return string
     */
    public function getStartedAt(): string
    {
        return date('Y-m-d H:i:s P', $this->startedAt);
    }
}

<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Thread\Entity;

class CStatus
{
    public function __construct(
        public int $pid,
        public string $className,
        public CCondition $condition,
        public int $startedAt,
        public ?int $balancer = null,
        public array $info = []
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

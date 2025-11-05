<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Thread\Entity;

final class CInfo
{
    public function __construct(
        public CStatus      $status,
        public ?ProcessStats $stats = null
    )
    {
    }
}

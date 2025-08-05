<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Health;

use Flytachi\Kernel\Src\Stereotype\ResponseJson;

interface HealthIndicatorInterface
{
    public function health(): ResponseJson;
    public function info(): ResponseJson;
    public function metrics(): ResponseJson;
    public function env(): ResponseJson;
    public function loggers(): ResponseJson;
    public function mappings(): ResponseJson;
    public function db(): ResponseJson;
    public function cache(): ResponseJson;
    public function disk(): ResponseJson;
}

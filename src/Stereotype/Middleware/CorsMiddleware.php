<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Stereotype\Middleware;

use Flytachi\Kernel\Src\Factory\Middleware\Cors\AccessControlMiddleware;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class CorsMiddleware extends AccessControlMiddleware
{
}

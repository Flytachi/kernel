<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Middleware\Cors;

use Flytachi\Kernel\Src\Factory\Middleware\AbstractMiddleware;
use Flytachi\Kernel\Src\Factory\Middleware\MiddlewareInterface;

abstract class AccessControlMiddleware extends AbstractMiddleware implements MiddlewareInterface
{
    protected array $origin = [];
    protected array $allowHeaders = [];
    protected array $exposeHeaders = [];
    protected bool $credentials = false;
    protected int $maxAge = 0;
    protected array $vary = [];

    final public static function passport(): array
    {
        $self = new static();
        return [
            'origin' => $self->origin,
            'allowHeaders' => $self->allowHeaders,
            'exposeHeaders' => $self->exposeHeaders,
            'credentials' => $self->credentials,
            'maxAge' => $self->maxAge,
            'vary' => $self->vary,
        ];
    }
}

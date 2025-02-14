<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Http\Response;

use Flytachi\Kernel\Src\Factory\Http\HttpCode;

interface ResponseInterface
{
    public function getHttpCode(): HttpCode;
    public function getHeader(): array;
    public function getBody(): string;
}

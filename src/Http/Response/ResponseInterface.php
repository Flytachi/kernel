<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Http\Response;

use Flytachi\Kernel\Src\Http\HttpCode;

interface ResponseInterface
{
    public function defaultHeaders(): array;
    public function addHeader(string $key, string $value): void;
    public function getHttpCode(): HttpCode;
    public function getHeader(): array;
    public function getBody(): string;
}

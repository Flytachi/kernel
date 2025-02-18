<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Http\Response;

use Flytachi\Kernel\Src\Http\HttpCode;

interface ResponseFileContentInterface
{
    public function getHttpCode(): HttpCode;
    public function getHeader(): array;
    public function getBody(): string;
    public function getFileName(): string;
}

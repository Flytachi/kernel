<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Http\Response;

use Flytachi\Kernel\Src\Factory\Http\HttpCode;

interface ResponseFileContentInterface
{
    public function getHttpCode(): HttpCode;
    public function getHeader(): array;
    public function getBody(): string;
    public function getFileName(): string;
}

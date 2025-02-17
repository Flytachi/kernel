<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Http\Response;

use Flytachi\Kernel\Src\Factory\Http\HttpCode;

interface ViewInterface
{
    public function getHttpCode(): HttpCode;
    public function getHeader(): array;
    public function getTemplate(): ?string;
    public function getCallClass(): ?string;
    public function getCallClassMethod(): ?string;
    public function getResource(): string;
    public function getData(): array;
}

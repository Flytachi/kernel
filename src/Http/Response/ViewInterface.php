<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Http\Response;

use Flytachi\Kernel\Src\Http\HttpCode;

interface ViewInterface
{
    public function defaultHeaders(): array;
    public function addHeader(string $key, string $value): void;
    public function getHttpCode(): HttpCode;
    public function getHeader(): array;
    public function getTemplate(): ?string;
    public function getCallClass(): ?string;
    public function getCallClassMethod(): ?string;
    public function getResource(): string;
    public function getData(): array;
}

<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Stereotype;

use Flytachi\Kernel\Src\Factory\Http\HttpCode;
use Flytachi\Kernel\Src\Factory\Http\Response\ResponseFileContent;

class ResponseContent extends ResponseFileContent
{
    public static function binary(
        mixed $data,
        string $fileName,
        string $mimeType = 'application/octet-stream',
        bool $isAttachment = false,
        HttpCode $httpCode = HttpCode::OK
    ): static {
        return new static('binary', $data, $fileName, $mimeType, $isAttachment, $httpCode);
    }

    public static function txt(
        mixed $data,
        string $fileName,
        string $mimeType = 'text/plain',
        bool $isAttachment = false,
        HttpCode $httpCode = HttpCode::OK
    ): static {
        return new static('txt', $data, $fileName, $mimeType, $isAttachment, $httpCode);
    }

    public static function csv(
        array $data,
        string $fileName,
        string $mimeType = 'text/csv',
        bool $isAttachment = false,
        HttpCode $httpCode = HttpCode::OK
    ): static {
        return new static('csv', $data, $fileName, $mimeType, $isAttachment, $httpCode);
    }

    public static function json(
        array|string $data,
        string $fileName,
        string $mimeType = 'application/json',
        bool $isAttachment = false,
        HttpCode $httpCode = HttpCode::OK
    ): static {
        return new static('json', $data, $fileName, $mimeType, $isAttachment, $httpCode);
    }

    public static function xml(
        \SimpleXMLElement|\stdClass|array|string|int|bool $data,
        string $fileName,
        string $mimeType = 'application/xml',
        bool $isAttachment = false,
        HttpCode $httpCode = HttpCode::OK
    ): static {
        return new static('xml', $data, $fileName, $mimeType, $isAttachment, $httpCode);
    }
}

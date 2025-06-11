<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Http\Response;

use Flytachi\Kernel\Src\Http\Header;
use Flytachi\Kernel\Src\Http\HttpCode;
use Flytachi\Kernel\Src\Unit\File\XML;

abstract class ResponseBase implements ResponseInterface
{
    protected array $headers = [];
    protected mixed $content;
    protected HttpCode $httpCode;

    public function __construct(mixed $content, HttpCode $httpCode = HttpCode::OK)
    {
        $this->content = $content;
        $this->httpCode = $httpCode;
    }

    public function defaultHeaders(): array
    {
        $accept = Header::getHeader('Accept');
        if (str_contains($accept, 'text/html')) {
            return ['Content-Type' => 'text/html; charset=utf-8'];
        } else {
            return ['Content-Type' => $accept];
        }
    }

    final public function addHeader(string $key, string $value): void
    {
        $this->headers[$key] = $value;
    }

    final public function getHttpCode(): HttpCode
    {
        return $this->httpCode;
    }

    final public function getHeader(): array
    {
        return [...$this->defaultHeaders(), ...$this->headers];
    }

    final public function getBody(): string
    {
        return match (Header::getHeader('Accept')) {
            'application/json' => $this->constructJson($this->content),
            'application/xml' => $this->constructXml($this->content),
            default => $this->constructDefault($this->content)
        };
    }

    final protected function constructJson(mixed $content): string
    {
        return json_encode($content);
    }

    final protected function constructXml(mixed $content): string
    {
        if (is_array($content)) {
            return XML::arrayToXml($content);
        } elseif (is_object($content) || $content instanceof \stdClass) {
            return XML::arrayToXml(
                json_decode(json_encode($content), true)
            );
        } else {
            return XML::arrayToXml([$content]);
        }
    }

    final protected function constructDefault(mixed $content): string
    {
        if (is_string($content) || is_numeric($content) || is_bool($content) || is_null($content)) {
            return (string) $content;
        } else {
            return print_r($content, true);
        }
    }

    final protected function debugger(): array
    {
        if (env('DEBUG', false)) {
            $delta = round(microtime(true) - EXTRA_STARTUP_TIME, 3);
            $memory = memory_get_usage();

            return [
                'debug' => [
                    'time' => ($delta < 0.001) ? 0.001 : $delta,
                    'date' => date(DATE_ATOM),
                    'timezone' => env('TIME_ZONE', 'UTC'),
                    'sapi' => PHP_SAPI,
                    'memory' => bytes($memory, ($memory >= 1048576 ? 'MiB' : 'KiB')),
                ]
            ];
        } else {
            return [];
        }
    }
}

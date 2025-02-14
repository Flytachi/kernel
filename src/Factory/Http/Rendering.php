<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Http;

use Flytachi\Kernel\Extra;
use Flytachi\Kernel\Src\Factory\Error\ExceptionWrapper;
use Flytachi\Kernel\Src\Factory\Error\ExtraThrowable;
use Flytachi\Kernel\Src\Factory\Http\Response\ResponseFileContentInterface;
use Flytachi\Kernel\Src\Factory\Http\Response\ResponseInterface;
use Flytachi\Kernel\Src\Factory\Http\Response\ViewInterface;

final class Rendering
{
    private HttpCode $httpCode;
    private array $header = [];
    private null|int|float|string|array $body;
    private ?string $resource = null;
    private ?string $handle = null;
    private int $action = 0;

    public function setResource(mixed $resource): void
    {
        if ($resource instanceof ResponseInterface) {
            $this->httpCode = $resource->getHttpCode();
            $this->header = $resource->getHeader();
            $this->body = $resource->getBody();
        } elseif ($resource instanceof ResponseFileContentInterface) {
            $this->httpCode = $resource->getHttpCode();
            $this->header = $resource->getHeader();
            $this->body = $resource->getBody();
            $this->resource = $resource->getFileName();
            $this->action = 2;
        } elseif ($resource instanceof ViewInterface) {
            $this->httpCode = $resource->getHttpCode();
            $this->header = $resource->getHeader();
            $this->resource = $resource->getResource();
            $this->body = $resource->getData();
            $this->handle = $resource->getHandle();
            $this->action = 1;
        } elseif ($resource instanceof \Throwable) {
            $this->httpCode = HttpCode::tryFrom($resource->getCode()) ?: HttpCode::INTERNAL_SERVER_ERROR;
            $this->logging($resource);
            if ($resource instanceof ExtraThrowable) {
                $this->header = $resource->getHeader();
                $this->body = $resource->getBody();
            } else {
                $this->header = ExceptionWrapper::wrapHeader();
                $this->body = ExceptionWrapper::wrapBody($resource);
            }
        } else {
            $this->httpCode = HttpCode::OK;
            $this->body = $resource;
        }
    }

    public function render(): never
    {
        header_remove("X-Powered-By");
        header("HTTP/1.1 {$this->httpCode->value} " . $this->httpCode->message());
        header("Status: {$this->httpCode->value} " . $this->httpCode->message());
        foreach ($this->header as $name => $value) {
            header("{$name}: {$value}");
        }
        if ($this->action === 1) {
            if (!empty($this->handle)) {
                echo $this->handle;
            }
            Extra::$logger->withName("Rendering")->debug(sprintf(
                "HTTP [%d] %s -> %s",
                $this->httpCode->value,
                $this->httpCode->message(),
                $this->resource
            ));
            $data = $this->body;
            if (is_array($data)) {
                extract($data);
            }
            include $this->resource;
        } elseif ($this->action === 2) {
            Extra::$logger->withName("Rendering")->debug(sprintf(
                "HTTP [%d] %s -> %s",
                $this->httpCode->value,
                $this->httpCode->message(),
                $this->resource
            ));
            file_put_contents('php://output', $this->body);
        } else {
            Extra::$logger->withName("Rendering")->debug(sprintf(
                "HTTP [%d] %s -> %s",
                $this->httpCode->value,
                $this->httpCode->message(),
                $this->body ?: ''
            ));
            echo $this->body;
        }
        exit();
    }

    private function logging(\Throwable $resource): void
    {
        $typeError = $this->httpCode->isServerError()
            ? 'error'
            : ($this->httpCode->isClientError() ? 'warning' : 'emergency');
        Extra::$logger->withName($resource::class)->{$typeError}(sprintf(
            "%d: %s\n# %s(%d) -> Stack trace:\n%s",
            $resource->getCode(),
            $resource->getMessage(),
            $resource->getFile(),
            $resource->getLine(),
            $resource->getTraceAsString(),
        ));
    }
}

<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Health;

use Flytachi\Kernel\Src\Stereotype\ResponseJson;

class HealthIndicator implements HealthIndicatorInterface
{
    public function health(array $args = []): array
    {
        return [
            'status' => 'UP',
            'components' => [
                'db' => $this->db(),
                'cache' => $this->cache(),
                'disk' => $this->disk(),
            ]
        ];
    }

    public function info(array $args = []): array
    {
        return [
            'php' => PHP_VERSION,
            'framework' => 'Extra',
            'sapi' => php_sapi_name(),
        ];
    }

    public function metrics(array $args = []): array
    {
        return [
            'memory' => Health::memory(),
            'cpu' => Health::cpu(),
            'php' => [
                'version' => PHP_VERSION,
                'sapi' => PHP_SAPI,
                'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
            ],
            'requests' => [
                'method' => $_SERVER['REQUEST_METHOD'],
                'uri' => $_SERVER['REQUEST_URI'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ],
            'os' => Health::os(),
            'disk' => Health::disk(),
            'uptime_seconds' => Health::uptimeSeconds(),
        ];
    }

    public function env(array $args = []): array
    {
        return ['status' => 'UP'];
    }

    public function loggers(array $args = []): array
    {
        return ['status' => 'UP'];
    }

    public function mappings(array $args = []): array
    {
        return ['status' => 'UP'];
    }
}

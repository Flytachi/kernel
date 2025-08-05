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
            'memory_usage' => memory_get_usage(),
            'memory_peak' => memory_get_peak_usage(),
            'load_average' => sys_getloadavg(),
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

    public function db(): array
    {
        return ['status' => 'UP'];
    }

    public function cache(): array
    {
        return ['status' => 'UP'];
    }

    public function disk(): array
    {
        $free = disk_free_space('/');
        $total = disk_total_space('/');
        return [
            'status' => $free / $total > 0.1 ? 'UP' : 'WARN',
            'details' => [
                'used_percent' => round((1 - $free / $total) * 100, 2)
            ]
        ];
    }
}

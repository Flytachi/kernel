<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Health;

use Flytachi\Kernel\ActuatorItemInterface;
use Flytachi\Kernel\Src\Stereotype\ResponseJson;

class HealthIndicator implements HealthIndicatorInterface
{

    public function health(): ResponseJson
    {
        return new ResponseJson([
            'status' => 'UP',
            'components' => [
                'db' => $this->db()->getBody(),
                'cache' => $this->cache()->getBody(),
                'disk' => $this->disk()->getBody(),
            ]
        ]);
    }

    public function info(): ResponseJson
    {
        return new ResponseJson([
            'php' => PHP_VERSION,
            'framework' => 'Extra',
            'sapi' => php_sapi_name(),
        ]);
    }

    public function metrics(): ResponseJson
    {
        return new ResponseJson([
            'memory_usage' => memory_get_usage(),
            'memory_peak' => memory_get_peak_usage(),
            'load_average' => sys_getloadavg(),
        ]);
    }

    public function env(): ResponseJson
    {
        return new ResponseJson(['status' => 'UP']);
    }

    public function loggers(): ResponseJson
    {
        return new ResponseJson(['status' => 'UP']);
    }

    public function mappings(): ResponseJson
    {
        return new ResponseJson(['status' => 'UP']);
    }

    public function db(): ResponseJson
    {
        return new ResponseJson(['status' => 'UP']);
    }

    public function cache(): ResponseJson
    {
        return new ResponseJson(['status' => 'UP']);
    }

    public function disk(): ResponseJson
    {
        $free = disk_free_space('/');
        $total = disk_total_space('/');
        return new ResponseJson([
            'status' => $free / $total > 0.1 ? 'UP' : 'WARN',
            'details' => [
                'used_percent' => round((1 - $free / $total) * 100, 2)
            ]
        ]);
    }
}

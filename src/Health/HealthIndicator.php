<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Health;

use Flytachi\Kernel\Extra;

class HealthIndicator implements HealthIndicatorInterface
{
    public function health(array $args = []): array
    {
        $components = [
            'db' => $this->diskDb(),
            'cache' => $this->diskCache(),
            'disk' => $this->diskHealth(),
            'memory' => $this->memoryHealth()
        ];

        $statuses = array_column($components, 'status');

        if (in_array('DOWN', $statuses)) {
            $overallStatus = 'DOWN';
        } elseif (in_array('WARN', $statuses)) {
            $overallStatus = 'WARN';
        } else {
            $overallStatus = 'UP';
        }

        return [
            'status' => $overallStatus,
            'components' => $components
        ];
    }

    public function info(array $args = []): array
    {
        $framework = Extra::info();
        $data = json_decode(file_get_contents(Extra::$pathRoot . '/composer.json'), true);
        return [
            'php' => [
                'version' => PHP_VERSION,
                'sapi' => php_sapi_name(),
                'zend_version' => zend_version(),
            ],
            'framework' => [
                'name' => 'Extra',
                'core' => $framework['name'] ?? null,
                'version' => $framework['version'] ?? null,
            ],
            'project' => [
                'name' => $data['name'] ?? '',
                'version' => $data['version'] ?? '',
                'description' => $data['description'] ?? '',
            ]
        ];
    }

    public function metrics(array $args = []): array
    {
        return [
            'cpu' => Health::cpu(),
            'memory' => Health::memory(),
            'disk' => Health::disk(),
            'system' => Health::system(),
            'php' => [
                'version' => PHP_VERSION,
                'sapi' => PHP_SAPI,
                'zend_version' => zend_version(),
                'execution_time' => microtime(true) - $_SERVER['REQUEST_TIME_FLOAT'],
            ],
            'opcache' => function_exists('opcache_get_status')
                ? opcache_get_status(false)
                : null,
            'requests' => [
                'method' => $_SERVER['REQUEST_METHOD'],
                'uri' => $_SERVER['REQUEST_URI'],
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            ],
            'uptime_seconds' => Health::uptimeSeconds(),
        ];
    }

    public function env(array $args = []): array
    {
        return [];
    }

    public function loggers(array $args = []): array
    {
        $allowedLevels = env('LOGGER_LEVEL_ALLOW');
        $allowedLevels = array_map('trim', explode(',', $allowedLevels));
        return [
            'levels' => $allowedLevels,
        ];
    }

    public function mappings(array $args = []): array
    {
        $list = [];
        $declaration = \Flytachi\Kernel\Src\Factory\Mapping\Mapping::scanningDeclaration();
        foreach ($declaration->getChildren() as $item) {
            $list[] = [
                'method' => $item->getMethod() ?: '?',
                'path' => $item->getUrl(),
                'handler' => $item->getClassName() . '->' . $item->getClassMethod(),
                'handlerClass' => $item->getClassName(),
                'handlerMethod' => $item->getClassMethod(),
                'middlewares' => $item->getMiddlewareClassNames(),
                'arguments' => $item->getMethodArgs()
            ];
        }
        return $list;
    }

    protected function diskDb(): array
    {
        return [
            'status' => 'UP',
            'details' => []
        ];
    }

    protected function diskCache(): array
    {
        return [
            'status' => 'UP',
            'details' => []
        ];
    }

    protected function diskHealth(): array
    {
        $diskInfo = Health::disk();
        $usagePercent = $diskInfo['usage_percent'];

        $status = 'UP';
        $warning = null;

        if ($usagePercent >= 90) {
            $status = 'DOWN';
            $warning = 'Disk usage above 90% of total capacity';
        } elseif ($usagePercent >= 80) {
            $status = 'WARN';
            $warning = 'Disk usage above 80% of total capacity';
        }

        return [
            'status' => $status,
            'details' => array_filter([
                'free' => $diskInfo['free'],
                'total' => $diskInfo['total'],
                'usage_percent' => round($usagePercent, 2),
                'warning' => $warning,
            ]),
        ];
    }

    protected function memoryHealth(): array
    {
        $memoryInfo = Health::memory();
        $limit = $memoryInfo['limit'];
        $usage = $memoryInfo['usage'];
        $usagePercent = $limit > 0 ? ($usage / $limit) * 100 : 0;

        $status = 'UP';
        $warning = null;

        if ($limit > 0) {
            if ($usagePercent >= 90) {
                $status = 'DOWN';
                $warning = 'Memory usage above 90% of the limit';
            } elseif ($usagePercent >= 80) {
                $status = 'WARN';
                $warning = 'Memory usage above 80% of the limit';
            }
        }

        return [
            'status' => $status,
            'details' => array_filter([
                'usage' => $usage,
                'peak' => $memoryInfo['peak'],
                'limit' => $limit,
                'usage_percent' => round($usagePercent, 2),
                'warning' => $warning,
            ]),
        ];
    }
}

<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Health;

use Flytachi\Kernel\Extra;

class HealthIndicator implements HealthIndicatorInterface
{
    public function health(array $args = []): array
    {
        return [
            'status' => 'UP',
            'components' => [
//                'db' => $this->db(),
//                'cache' => $this->cache(),
//                'disk' => $this->disk(),
                'memory' => $this->memoryHealth()
            ]
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

    protected function memoryHealth(): array
    {
        $memoryInfo = Health::memory();
        $status = 'UP';
        $warning = null;
        if ($memoryInfo['limit'] > 0 && $memoryInfo['usage'] > 0.9 * $memoryInfo['limit']) {
            $status = 'DOWN';
            $warning = 'Memory usage above 90% of the limit';
        }
        return [
            'status' => $status,
            'details' => array_filter([
                'usage' => $memoryInfo['usage'],
                'peak' => $memoryInfo['peak'],
                'limit' => $memoryInfo['limit'],
                'warning' => $warning,
            ]),
        ];
    }
}

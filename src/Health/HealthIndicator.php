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
//                'db' => $this->db(),
//                'cache' => $this->cache(),
//                'disk' => $this->disk(),
            ]
        ];
    }

    public function info(array $args = []): array
    {
        return [
            'app' => [
                'name' => 'Extra Framework App',
                'version' => '1.2.0',
                'description' => 'Demo API on Extra Framework'
            ],
            'build' => [
                'time' => date('c')
            ],
            'environment' => $_ENV['APP_ENV'] ?? 'development',
            'php' => [
                'version' => PHP_VERSION,
                'sapi' => php_sapi_name()
            ],
            'framework' => [
                'name' => 'Extra',
                'version' => \Flytachi\Kernel\Extra::VERSION
            ]
        ];
    }

    public function metrics(array $args = []): array
    {
        return [
            'os' => Health::os(),
            'cpu' => Health::cpu(),
            'memory' => Health::memory(),
            'disk' => Health::disk(),
            'php' => [
                'version' => PHP_VERSION,
                'sapi' => PHP_SAPI,
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
        return ['status' => 'UP'];
    }

    public function loggers(array $args = []): array
    {
        return ['status' => 'UP'];
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
}

<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Health;

use Flytachi\Kernel\Src\ActuatorItemInterface;
use Flytachi\Kernel\Src\Factory\Middleware\MiddlewareInterface;
use Flytachi\Kernel\Src\Http\Header;
use Flytachi\Kernel\Src\Http\Rendering;
use Flytachi\Kernel\Src\Stereotype\ResponseJson;

final readonly class Health implements ActuatorItemInterface
{
    /**
     * @param class-string<HealthIndicatorInterface> $indicatorClass
     * @param class-string<MiddlewareInterface>|null $middlewareClass
     */
    public function __construct(
        private string $indicatorClass = HealthIndicator::class,
        private ?string $middlewareClass = null
    ) {
    }

    public function run(): void
    {
        Header::setHeaders();
        $data = parseUrlDetail($_SERVER['REQUEST_URI']);

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && str_starts_with($data['path'], '/actuator')) {
            /** @var HealthIndicatorInterface $indicator */
            $indicator = new $this->indicatorClass();

            $metta = trim(str_replace('/actuator', '', $data['path']), '/');
            $mettaExp = explode('/', $metta);
            $method = $mettaExp[0];
            unset($mettaExp[0]);
            $arg = array_values($mettaExp);

            $render = new Rendering();

            try {
                if ($this->middlewareClass !== null) {
                    /** @var MiddlewareInterface $middleware */
                    $middleware = new $this->middlewareClass();
                    $middleware->optionBefore();
                }
                $result = $indicator->{$method}($arg);
                $render->setResource(new ResponseJson($result));
            } catch (\Throwable $e) {
                $render->setResource($e);
            }

            $render->render();
        }
    }

    public static function cpu(): array
    {
        return [
            'load_average' => sys_getloadavg(),
            'core_count' => (int) shell_exec('nproc') ?: 1,
        ];
    }

    public static function memory(): array
    {
        $limit = ini_get('memory_limit');
        return [
            'usage' => memory_get_usage(true),
            'peak' => memory_get_peak_usage(true),
            'limit' => $limit == '-1' ? -1 : self::convertToBytes($limit),
        ];
    }

    private static function convertToBytes(string $val): int
    {
        $val = trim($val);
        $unit = strtolower(substr($val, -1));
        $num = (int) $val;

        return match ($unit) {
            'g' => $num * 1024 * 1024 * 1024,
            'm' => $num * 1024 * 1024,
            'k' => $num * 1024,
            default => (int) $val,
        };
    }

    public static function system(): array
    {
        return [
            'os' => php_uname('s'),
            'release' => php_uname('r'),
            'hostname' => gethostname(),
        ];
    }

    public static function disk(): array
    {
        return [
            'free' => disk_free_space("/"),
            'total' => disk_total_space("/"),
            'usage_percent' => round(
                (1 - disk_free_space("/") /
                    disk_total_space("/")
                ) * 100,
                2
            )
        ];
    }

    public static function uptimeSeconds(): ?int
    {
        return (int) shell_exec('awk \'{print int($1)}\' /proc/uptime') ?: null;
    }
}

<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Health;

use Flytachi\Kernel\Src\ActuatorItemInterface;
use Flytachi\Kernel\Src\Http\Header;
use Flytachi\Kernel\Src\Http\Rendering;
use Flytachi\Kernel\Src\Stereotype\ResponseJson;

final readonly class Health implements ActuatorItemInterface
{
    /**
     * @param class-string $indicatorClass
     */
    public function __construct(
        private string $indicatorClass = HealthIndicator::class
    ) {
    }

    public function run(): void
    {
        Header::setHeaders();
        $data = parseUrlDetail($_SERVER['REQUEST_URI']);

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && str_starts_with($data['path'], '/actuator')) {
            $indicator = new $this->indicatorClass();
            $metta = trim(str_replace('/actuator', '', $data['path']), '/');
            $mettaExp = explode('/', $metta);
            $method = $mettaExp[0];
            unset($mettaExp[0]);
            $arg = array_values($mettaExp);

            $render = new Rendering();
            try {
                $result = $indicator->{$method}($arg);
                $render->setResource(new ResponseJson($result));
            } catch (\Throwable $e) {
                $render->setResource($e);
            }

            $render->render();
        }
    }
}

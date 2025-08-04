<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Console;

use Flytachi\Kernel\Console\Inc\CoreHandle;
use Flytachi\Kernel\Src\ActuatorItemInterface;

class Core extends CoreHandle implements ActuatorItemInterface
{
    public function __construct($args)
    {
        $this->parser($args);
    }

    public function run(): void
    {
        try {
            if (array_key_exists(0, self::$arguments['arguments'])) {
                $cmd = ucwords(self::$arguments['arguments'][0]);
            } else {
                $cmd = 'Help';
            }
            ('Flytachi\Kernel\Console\Command\\' . $cmd)::script(self::$arguments);
        } catch (\Throwable $exception) {
            self::printError($exception);
        }
    }
}

<?php

use Flytachi\Kernel\Src\Health\HealthIndicator;

require_once dirname(__DIR__) . '/vendor/autoload.php';
include_once dirname(__DIR__) . '/Extra.php';

\Flytachi\Kernel\Extra::init(__DIR__);

\Flytachi\Kernel\Src\Actuator::use(
    new \Flytachi\Kernel\Console\Core($argv)
);

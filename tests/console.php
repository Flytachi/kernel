<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';
include_once dirname(__DIR__) . '/Extra.php';

\Flytachi\Kernel\Extra::init(__DIR__);

\Flytachi\Kernel\Src\Actuator::use(
//    new \Flytachi\Kernel\Console\Core($argv)
    new \Flytachi\Kernel\Src\Health\HealthIndicator(),
    new \Flytachi\Kernel\Src\Http\Router(),
);

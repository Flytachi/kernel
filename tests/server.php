<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';
include_once dirname(__DIR__) . '/Extra.php';

\Flytachi\Kernel\Extra::init(__DIR__);

\Flytachi\Kernel\Src\Actuator::use(
    new \Flytachi\Kernel\Src\Health\Health(
    //        indicatorClass: \Flytachi\Kernel\Src\Health\HealthIndicator::class,
    //        middlewareClass: \Flytachi\Kernel\Src\Stereotype\Middleware\SecurityMiddleware::class,
    ),
    new \Flytachi\Kernel\Src\Http\Router(),
);

<?php

require_once dirname(__DIR__) . '/vendor/autoload.php';
include_once dirname(__DIR__) . '/Extra.php';

\Flytachi\Kernel\Extra::init(__DIR__);
new \Flytachi\Kernel\Console\Core($argv);

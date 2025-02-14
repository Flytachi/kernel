<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Stereotype;

use Flytachi\Kernel\Src\Factory\Connection\Repository\Interfaces\RepositoryCrudInterface;
use Flytachi\Kernel\Src\Factory\Connection\Repository\RepositoryCore;
use Flytachi\Kernel\Src\Factory\Connection\Repository\Traits\RepositoryCrudTrait;

abstract class RepositoryCrud extends RepositoryCore implements RepositoryCrudInterface
{
    use RepositoryCrudTrait;
}

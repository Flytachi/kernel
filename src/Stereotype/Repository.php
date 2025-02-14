<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Stereotype;

use Flytachi\Kernel\Src\Factory\Connection\Repository\Interfaces\RepositoryCrudInterface;
use Flytachi\Kernel\Src\Factory\Connection\Repository\Interfaces\RepositoryViewInterface;
use Flytachi\Kernel\Src\Factory\Connection\Repository\RepositoryCore;
use Flytachi\Kernel\Src\Factory\Connection\Repository\Traits\RepositoryCrudTrait;
use Flytachi\Kernel\Src\Factory\Connection\Repository\Traits\RepositoryViewTrait;

abstract class Repository extends RepositoryCore implements RepositoryCrudInterface, RepositoryViewInterface
{
    use RepositoryCrudTrait;
    use RepositoryViewTrait;
}

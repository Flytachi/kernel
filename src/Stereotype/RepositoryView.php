<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Stereotype;

use Flytachi\Kernel\Src\Factory\Connection\Repository\Interfaces\RepositoryViewInterface;
use Flytachi\Kernel\Src\Factory\Connection\Repository\RepositoryCore;
use Flytachi\Kernel\Src\Factory\Connection\Repository\Traits\RepositoryViewTrait;

abstract class RepositoryView extends RepositoryCore implements RepositoryViewInterface
{
    use RepositoryViewTrait;
}

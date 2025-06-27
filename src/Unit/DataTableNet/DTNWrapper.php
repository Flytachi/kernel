<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Unit\DataTableNet;

use Flytachi\Kernel\Src\Factory\Connection\Repository\Interfaces\RepositoryInterface;

class DTNWrapper
{
    final public static function paginator(
        RepositoryInterface $repo,
        DataTableNetRequest $request
    ): DataTableNetResponse {
        return new DataTableNetResponse();
    }
}

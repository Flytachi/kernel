<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Mapping\Annotation;

use Attribute;
use Flytachi\Kernel\Src\Factory\Mapping\MappingRequestInterface;

#[Attribute(Attribute::TARGET_METHOD)]
class PatchMapping extends AbstractMapping implements MappingRequestInterface
{
    protected ?string $call = 'PATCH';
}

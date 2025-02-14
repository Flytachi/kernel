<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Mapping\Annotation;

use Attribute;
use Flytachi\Kernel\Src\Factory\Mapping\MappingRequestInterface;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class RequestMapping extends AbstractMapping implements MappingRequestInterface
{
}

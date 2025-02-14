<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Mapping\OpenApi;

enum DataType
{
    case JSON;
    case FORM;
    case QUERY;
}

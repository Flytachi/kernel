<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Http;

enum Method
{
    case GET;
    case HEAD;
    case POST;
    case PUT;
    case DELETE;
    case CONNECT;
    case OPTIONS;
    case TRACE;
    case PATCH;
}

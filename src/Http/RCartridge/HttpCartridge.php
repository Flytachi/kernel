<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Http\RCartridge;

class HttpCartridge implements RouteCartridgeInterface
{
    public function wrapInput(): array
    {
        $data = parseUrlDetail($_SERVER['REQUEST_URI']);
        $_GET = $data['query'];
        return $data;
    }

    public function wrapOutput(mixed $result): mixed
    {
        return $result;
    }
}

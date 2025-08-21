<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Rpc;

use Flytachi\Kernel\Src\Http\Response\ResponseBase;

class JRpcResponse extends ResponseBase
{
    protected ?int $reqId = null;

    public function defaultHeaders(): array
    {
        return ['Content-Type' => 'application/json'];
    }

    public function getBody(): string
    {
        return json_encode([
            'jsonrpc' => '2.0',
            'id' => $this->reqId,
            'result' => $this->content,
        ]);
    }
}

<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Stereotype;

use Flytachi\Kernel\Src\Http\Response\ContentType;
use Flytachi\Kernel\Src\Http\Response\ResponseBase;

class ResponseJson extends ResponseBase
{
    public function getBody(): string
    {
        $contentType = ContentType::JSON;
        $this->addHeader('Content-Type', $contentType->headerFullValue());
        return $contentType->serialize($this->content);
    }
}

<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Stereotype;

use Flytachi\Kernel\Src\Http\Response\ContentType;
use Flytachi\Kernel\Src\Http\Response\ResponseBase;

class ResponseXml extends ResponseBase
{
    public function getBody(): string
    {
        $contentType = ContentType::XML;
        $this->addHeader('Content-Type', $contentType->headerFullValue());
        return $contentType->serialize($this->content);
    }
}

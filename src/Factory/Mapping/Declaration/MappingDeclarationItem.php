<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Mapping\Declaration;

use Flytachi\Kernel\Src\Factory\Mapping\OpenApi\Schema\Spl;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

class MappingDeclarationItem
{
    private string $method;
    private string $url;
    private string $className;
    private string $classMethod;
    private array $methodArgs;
    private array $middlewareClassNames;

    /**
     * @param string $method
     * @param string $url
     * @param string $className
     * @param string $classMethod
     * @param array $methodArgs
     * @param array $middlewareClassNames
     */
    public function __construct(
        string $method,
        string $url,
        string $className,
        string $classMethod,
        array $methodArgs = [],
        array $middlewareClassNames = []
    ) {
        $this->method = $method;
        $this->url = $url;
        $this->className = $className;
        $this->classMethod = $classMethod;
        $this->methodArgs = $methodArgs;
        $this->middlewareClassNames = $middlewareClassNames;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getClassMethod(): string
    {
        return $this->classMethod;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getMethodArgs(): array
    {
        return $this->methodArgs;
    }

    public function getMiddlewareClassNames(): array
    {
        return $this->middlewareClassNames;
    }

    /**
     * @return array<ReflectionAttribute>
     * @throws ReflectionException
     */
    public function getClassSpl(): array
    {
        $method = new ReflectionClass($this->className);
        return $method->getAttributes(Spl::class, ReflectionAttribute::IS_INSTANCEOF);
    }

    /**
     * @return array<ReflectionAttribute>
     * @throws ReflectionException
     */
    public function getClassMethodSpl(): array
    {
        $method = new ReflectionMethod($this->className, $this->classMethod);
        return $method->getAttributes(Spl::class, ReflectionAttribute::IS_INSTANCEOF);
    }

    /**
     * @return ReflectionMethod
     * @throws ReflectionException
     */
    public function getReflectionMethod(): ReflectionMethod
    {
        return new ReflectionMethod($this->className, $this->classMethod);
    }
}

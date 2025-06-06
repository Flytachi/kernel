<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Mapping;

use Flytachi\Kernel\Extra;
use Flytachi\Kernel\Src\Factory\Mapping\Annotation\RequestMapping;
use Flytachi\Kernel\Src\Factory\Mapping\Declaration\MappingDeclaration;
use Flytachi\Kernel\Src\Factory\Mapping\Declaration\MappingDeclarationItem;
use Flytachi\Kernel\Src\Factory\Middleware\MiddlewareInterface;
use Flytachi\Kernel\Src\Stereotype\ControllerInterface;
use ReflectionClass;
use ReflectionMethod;

class Mapping
{
    /**
     * @return MappingDeclaration
     */
    public static function scanningDeclaration(): MappingDeclaration
    {
        $resources = scanFindAllFile(Extra::$pathRoot, 'php', [
            Extra::$pathRoot . '/vendor'
        ]);
        $reflectionClasses = self::scanReflectionFilter($resources);
        return self::scanDeclarationFilter($reflectionClasses);
    }

    /**
     * @param array $resources
     * @return array<ReflectionClass>
     */
    private static function scanReflectionFilter(array $resources): array
    {
        $reflectionClasses = [];
        foreach ($resources as $resource) {
            $className = ucwords(
                str_replace(
                    '.php',
                    '',
                    str_replace('/', '\\', str_replace(Extra::$pathRoot . '/', '', $resource))
                )
            );

            try {
                $reflectionClass = new ReflectionClass($className);
                if ($reflectionClass->implementsInterface(ControllerInterface::class)) {
                    $reflectionClasses[] = $reflectionClass;
                }
            } catch (\ReflectionException $ex) {
            }
        }
        return $reflectionClasses;
    }

    /**
     * @param array<ReflectionClass> $reflectionClasses
     * @return MappingDeclaration
     */
    private static function scanDeclarationFilter(array $reflectionClasses): MappingDeclaration
    {
        $declaration = new MappingDeclaration();

        foreach ($reflectionClasses as $reflectionClass) {
            // class annotation
            $groupAnnotation = $reflectionClass->getAttributes(RequestMapping::class);
            if (isset($groupAnnotation[0])) {
                $groupAnnotation = $groupAnnotation[0];
                /** @var MappingRequestInterface $mappingGroup */
                $mappingClass = $groupAnnotation->newInstance();
            } else {
                $mappingClass = null;
            }

            // class middleware annotations
            $middlewaresClass = [];
            $groupAnnotationMiddleware = $reflectionClass->getAttributes(
                MiddlewareInterface::class,
                \ReflectionAttribute::IS_INSTANCEOF
            );
            foreach ($groupAnnotationMiddleware as $annotationMiddleware) {
                $middlewaresClass[] = $annotationMiddleware->getName();
            }

            // method annotation
            foreach ($reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC) as $reflectionMethod) {
                if ($reflectionMethod->name != '__construct') {
                    $annotations = $reflectionMethod->getAttributes(
                        MappingRequestInterface::class,
                        \ReflectionAttribute::IS_INSTANCEOF
                    );
                    foreach ($annotations as $annotation) {
                        /** @var MappingRequestInterface $mapping */
                        $mapping = $annotation->newInstance();

                        // method middleware annotations
                        $middlewares = [];
                        $annotationMiddlewares = $reflectionMethod->getAttributes(
                            MiddlewareInterface::class,
                            \ReflectionAttribute::IS_INSTANCEOF
                        );
                        foreach ($annotationMiddlewares as $annotationMiddleware) {
                            $middlewares[] = $annotationMiddleware->getName();
                        }
                        $declarationItem = new MappingDeclarationItem(
                            $mapping->getCallback() ?: '',
                            ($mappingClass != null
                                ? trim($mappingClass->getUrl() . '/' . $mapping->getUrl(), '/')
                                : $mapping->getUrl()
                            ),
                            $reflectionClass->getName(),
                            $reflectionMethod->getName(),
                            [...$middlewaresClass, ...$middlewares]
                        );
                        $declaration->push($declarationItem);
                    }
                }
            }
        }

        $declaration->sorting();
        return $declaration;
    }
}

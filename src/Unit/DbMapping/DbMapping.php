<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Unit\DbMapping;

use Flytachi\DbMapping\Structure\Table;
use Flytachi\DbMapping\Tools\ColumnMapping;
use Flytachi\Kernel\Extra;
use Flytachi\Kernel\Src\Factory\Connection\Config\Common\DbConfigInterface;
use Flytachi\Kernel\Src\Factory\Connection\Repository\Interfaces\RepositoryInterface;
use ReflectionClass;

class DbMapping
{
    public static function scanningDeclaration(): DbMappingDeclaration
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
                if ($reflectionClass->implementsInterface(RepositoryInterface::class)) {
                    $reflectionClasses[] = $reflectionClass;
                }
            } catch (\ReflectionException $ex) {
            }
        }
        return $reflectionClasses;
    }

    /**
     * @param array<ReflectionClass> $reflectionClasses
     * @return DbMappingDeclaration
     */
    private static function scanDeclarationFilter(array $reflectionClasses): DbMappingDeclaration
    {
        $declaration = new DbMappingDeclaration();

        foreach ($reflectionClasses as $reflectionClass) {
            try {
                /** @var RepositoryInterface $repository */
                $repository = $reflectionClass->newInstance();
                /** @var DbConfigInterface $config */
                $config = (new ReflectionClass($repository->getDbConfigClassName()))->newInstance();

                $reflectionClassModel = new ReflectionClass($repository->getModelClassName());
                $columnMap = new ColumnMapping($config->getDriver());

                foreach ($reflectionClassModel->getProperties() as $property) {
                    $columnMap->push($property);
                }
                $declaration->push($config, new Table(
                    name: $repository::$table,
                    columns: $columnMap->getColumns(),
                    schema: $repository->getSchema(),
                ));
            } catch (\ReflectionException $ex) {
            }
        }

        return $declaration;
    }
}

<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Unit\DbMapping;

use Flytachi\DbMapping\Attributes\Entity\Table as EntityTable;
use Flytachi\DbMapping\Structure\Table;
use Flytachi\DbMapping\Tools\ColumnMapping;
use Flytachi\Kernel\Extra;
use Flytachi\Kernel\Src\Factory\Connection\Config\Common\DbConfigInterface;
use Flytachi\Kernel\Src\Factory\Connection\Repository\Interfaces\RepositoryInterface;
use Flytachi\Kernel\Src\Factory\Mapping\Mapping;
use ReflectionAttribute;
use ReflectionClass;

class DbMapping
{
    public static function scanningDeclaration(): DbMappingDeclaration
    {
        $resources = Mapping::scanProjectFiles();
        $reflectionClasses = Mapping::scanRefClasses($resources, RepositoryInterface::class);
        return self::scanDeclarationFilter($reflectionClasses);
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

                $annotationClassModel = $reflectionClassModel
                    ->getAttributes(EntityTable::class, ReflectionAttribute::IS_INSTANCEOF);
                if (empty($annotationClassModel)) {
                    continue;
                }

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

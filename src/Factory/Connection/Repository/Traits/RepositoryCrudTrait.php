<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Connection\Repository\Traits;

use Flytachi\Kernel\Src\Factory\Connection\CDO\CDOException;
use Flytachi\Kernel\Src\Factory\Connection\Qb;
use Flytachi\Kernel\Src\Factory\Connection\Repository\RepositoryException;

trait RepositoryCrudTrait
{
    /**
     * @throws RepositoryException
     */
    public function insert(object|array $model): mixed
    {
        if ($this->isReadonly) {
            throw new RepositoryException('No write access (action insert)');
        }
        try {
            return $this->db()->insert(($this->schema ? $this->schema . '.' : '') . $this::$table, $model);
        } catch (CDOException $exception) {
            throw new RepositoryException($exception->getMessage(),  $exception->getCode(), $exception);
        }
    }

    /**
     * @throws RepositoryException
     */
    public function insertGroup(object ...$models): void
    {
        if ($this->isReadonly) {
            throw new RepositoryException('No write access (action insert)');
        }

        try {
            $this->db()->insertGroup(($this->schema ? $this->schema . '.' : '') . $this::$table, ...$models);
        } catch (CDOException $exception) {
            throw new RepositoryException($exception->getMessage(),  $exception->getCode(), $exception);
        }
    }

    /**
     * @throws RepositoryException
     */
    public function update(object|array $model, Qb $qb): int|string
    {
        if ($this->isReadonly) {
            throw new RepositoryException('No write access (action update)');
        }
        try {
            return $this->db()->update(($this->schema ? $this->schema . '.' : '') . $this::$table, $model, $qb);
        } catch (CDOException $exception) {
            throw new RepositoryException($exception->getMessage(),  $exception->getCode(), $exception);
        }
    }

    /**
     * @throws RepositoryException
     */
    public function delete(Qb $qb): int|string
    {
        if ($this->isReadonly) {
            throw new RepositoryException('No write access (action delete)');
        }
        try {
            return $this->db()->delete(($this->schema ? $this->schema . '.' : '') . $this::$table, $qb);
        } catch (CDOException $exception) {
            throw new RepositoryException($exception->getMessage(),  $exception->getCode(), $exception);
        }
    }
}

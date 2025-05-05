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
        try {
            return $this->db()->insert($this->originTable(), $model);
        } catch (CDOException $exception) {
            throw new RepositoryException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @throws RepositoryException
     */
    public function insertGroup(object ...$models): void
    {
        try {
            $this->db()->insertGroup($this->originTable(), ...$models);
        } catch (CDOException $exception) {
            throw new RepositoryException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @throws RepositoryException
     */
    public function update(object|array $model, Qb $qb): int|string
    {
        try {
            return $this->db()->update($this->originTable(), $model, $qb);
        } catch (CDOException $exception) {
            throw new RepositoryException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @throws RepositoryException
     */
    public function delete(Qb $qb): int|string
    {
        try {
            return $this->db()->delete($this->originTable(), $qb);
        } catch (CDOException $exception) {
            throw new RepositoryException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }
}

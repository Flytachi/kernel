<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Connection\Repository\Interfaces;

use Flytachi\Kernel\Src\Factory\Connection\CDO\CDO;
use Flytachi\Kernel\Src\Factory\Connection\Qb;

interface RepositoryInterface
{
    public function db(): CDO;
    public function getSchema(): ?string;
    public function originTable(): string;
    public function getModelClassName(): string;
    public function getDbConfigClassName(): string;
    public function buildSql(): string;
    public function getSql(?string $param = null): mixed;
    public function cleanCache(?string $param = null): void;
    public function select(string $option): static;
    public function as(string $alias): static;
    public function join(RepositoryInterface $repository, string $on): static;
    public function joinLeft(RepositoryInterface $repository, string $on): static;
    public function joinRight(RepositoryInterface $repository, string $on): static;
    public function where(?Qb $qb): static;
    public function union(RepositoryInterface $repository): static;
    public function groupBy(string $context): static;
    public function having(string $context): static;
    public function orderBy(string $context): static;
    public function limit(int $limit, int $offset = 0): static;
    public function forBy(string $context): static;
}

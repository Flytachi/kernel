<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Connection\Repository;

use Flytachi\Kernel\Src\Factory\Connection\CDO\CDO;
use Flytachi\Kernel\Src\Factory\Connection\ConnectionPool;
use Flytachi\Kernel\Src\Factory\Connection\Qb;
use Flytachi\Kernel\Src\Factory\Connection\Repository\Interfaces\RepositoryInterface;
use Flytachi\Kernel\Src\Factory\Entity\ModelInterface;
use Flytachi\Kernel\Src\Factory\Stereotype;

abstract class RepositoryCore extends Stereotype implements RepositoryInterface
{
    /** @var class-string $dbConfigClassName dbConfig class name (default => DbConfig::class) */
    protected string $dbConfigClassName;
    /** @var class-string $modelClassName object class name (default => \stdClass::class) */
    protected string $modelClassName = \stdClass::class;
    /** @var string|null $schema schema in database */
    protected ?string $schema = null;
    /** @var string $table name of the table in the database */
    public static string $table;
    /** @var array $sqlParts sql parameters */
    protected array $sqlParts = [];

    final public function __construct()
    {
        parent::__construct();
        if (!isset($this->dbConfigClassName)) {
            RepositoryException::throw(static::class . ' $dbConfigClassName must be set by the child class');
        }
        $config = ConnectionPool::getConfigDb($this->dbConfigClassName);
        if ($this->schema == null) {
            $this->schema = $config->getSchema();
        }
        $config->connect();
    }

    public static function entity(?string $as = null): static
    {
        $repository = new static();
        if (!empty($as)) {
            $repository->as($as);
        }
        return $repository;
    }

    /**
     * @return CDO
     */
    final public function db(): CDO
    {
        return ConnectionPool::db($this->dbConfigClassName);
    }

    /**
     * @return string
     */
    final public function getSchema(): string
    {
        return $this->schema;
    }

    /**
     * @return string
     */
    final public function originTable(): string
    {
        return (($this->schema) ? $this->schema . '.' : '') . static::$table;
    }

    /**
     * @throws RepositoryException
     */
    final public function buildSql(): string
    {
        try {
            $parts = [
                'SELECT ' . $this->prepareSelect(),
                'FROM ' . $this->originTable()
            ];

            foreach (['as', 'join', 'where', 'union', 'group', 'having', 'order'] as $key) {
                if (isset($this->sqlParts[$key])) {
                    $parts[] = trim($this->sqlParts[$key]);
                }
            }
            if (isset($this->sqlParts['limit'])) {
                $parts[] = 'LIMIT ' . $this->sqlParts['limit'];
            }
            if (isset($this->sqlParts['offset'])) {
                $parts[] = 'OFFSET ' . $this->sqlParts['offset'];
            }
            if (isset($this->sqlParts['for'])) {
                $parts[] = 'FOR ' . $this->sqlParts['for'];
            }

            $query = implode(' ', $parts);
            $this->logger->debug('Repository build:' . $query);
            return $query;
        } catch (\Throwable $th) {
            throw new RepositoryException($th->getMessage(), previous: $th);
        }
    }

    /**
     * @throws RepositoryException
     */
    final public function getSql(?string $param = null): mixed
    {
        if ($param) {
            return (isset($this->sqlParts[$param])) ? $this->sqlParts[$param] : null;
        } else {
            return $this->buildSql();
        }
    }

    final public function cleanCache(?string $param = null): void
    {
        if ($param) {
            if (isset($this->sqlParts[$param])) {
                unset($this->sqlParts[$param]);
            };
        } else {
            $this->sqlParts = [];
        }
    }

    private function prepareSelect(): string
    {
        if (isset($this->sqlParts['option'])) {
            $this->modelClassName = \stdClass::class;
            return $this->sqlParts['option'];
        } elseif ($this->modelClassName === 'stdClass' || is_subclass_of($this->modelClassName, \stdClass::class)) {
            return '*';
        } else {
            $prefix = isset($this->sqlParts['as']) ? $this->sqlParts['as'] . '.' : '';
            $values = [];
            if (is_subclass_of($this->modelClassName, ModelInterface::class)) {
                $selection = $this->modelClassName::selection();
            }

            foreach (get_class_vars($this->modelClassName) as $name => $val) {
                $values[] = $selection[$name] ?? ($prefix . $name);
            }

            return implode(', ', $values);
        }
    }

    /**
     * @param string $option
     * @return static
     */
    final public function select(string $option): static
    {
        if (!empty($option)) {
            $this->sqlParts['option'] = $option;
        }
        return $this;
    }

    /**
     * @param string $alias
     * @return static
     */
    final public function as(string $alias): static
    {
        if (!empty($alias)) {
            $this->sqlParts['as'] = $alias;
        }
        return $this;
    }

    /**
     * @param RepositoryInterface $repository
     * @param string $on
     * @return static
     */
    final public function join(RepositoryInterface $repository, string $on): static
    {
        $context = $repository->originTable()
            . ' ' . $repository->getSql('as') . " ON(" . $on . ")";
        if (isset($this->sqlParts['join'])) {
            $this->sqlParts['join'] .= ' JOIN ' . $context;
        } else {
            $this->sqlParts['join'] = 'JOIN ' . $context;
        }
        return $this;
    }

    /**
     * @param RepositoryInterface $repository
     * @param string $on
     * @return static
     */
    final public function joinLeft(RepositoryInterface $repository, string $on): static
    {
        $context = $repository->originTable()
            . ' ' . $repository->getSql('as') . " ON(" . $on . ")";
        if (isset($this->sqlParts['join'])) {
            $this->sqlParts['join'] .= ' LEFT JOIN ' . $context;
        } else {
            $this->sqlParts['join'] = 'LEFT JOIN ' . $context;
        }
        return $this;
    }

    /**
     * @param RepositoryInterface $repository
     * @param string $on
     * @return static
     */
    final public function joinRight(RepositoryInterface $repository, string $on): static
    {
        $context = $repository->originTable()
            . ' ' . $repository->getSql('as') . " ON(" . $on . ")";
        if (isset($this->sqlParts['join'])) {
            $this->sqlParts['join'] .= ' RIGHT JOIN ' . $context;
        } else {
            $this->sqlParts['join'] = 'RIGHT JOIN ' . $context;
        }
        return $this;
    }

    /**
     * @param null|Qb $qb
     * @return static
     */
    final public function where(?Qb $qb): static
    {
        if (!is_null($qb)) {
            if ($qb->getQuery()) {
                $this->sqlParts['where'] = 'WHERE ' . $qb->getQuery();
                if (isset($this->sqlParts['binds'])) {
                    $this->sqlParts['binds'] = [...$this->sqlParts['binds'], ...$qb->getCache()];
                } else {
                    $this->sqlParts['binds'] = $qb->getCache();
                }
            }
        }
        return $this;
    }

    /**
     * @param RepositoryInterface $repository
     * @return static
     */
    final public function union(RepositoryInterface $repository): static
    {
        if (isset($this->sqlParts['union'])) {
            $this->sqlParts['union'] .= ' UNION ' . $repository->getSql();
        } else {
            $this->sqlParts['union'] = 'UNION ' . $repository->getSql();
        }
        if (isset($this->sqlParts['binds'])) {
            $this->sqlParts['binds'] = [...$this->sqlParts['binds'], ...$repository->getSql('binds')];
        } else {
            $this->sqlParts['binds'] = $repository->getSql('binds');
        }
        return $this;
    }

    /**
     * @param string $context
     * @return static
     */
    final public function groupBy(string $context): static
    {
        if (!empty($context)) {
            $this->sqlParts['group'] = 'GROUP BY ' . $context;
        }
        return $this;
    }

    /**
     * @param string $context
     * @return static
     */
    final public function having(string $context): static
    {
        if (!empty($context)) {
            $this->sqlParts['having'] = 'HAVING ' . $context;
        }
        return $this;
    }

    /**
     * @param string $context
     * @return static
     */
    final public function orderBy(string $context): static
    {
        if (!empty($context)) {
            $this->sqlParts['order'] = 'ORDER BY ' . $context;
        }
        return $this;
    }

    /**
     * @param int $limit
     * @param int $offset
     * @return static
     */
    final public function limit(int $limit, int $offset = 0): static
    {
        if ($limit < 1) {
            throw new \TypeError('limit < 1');
        }
        if ($offset < 0) {
            throw new \TypeError('offset < 0');
        }
        $this->sqlParts['limit'] = $limit;
        $this->sqlParts['offset'] = $offset;
        return $this;
    }

    /**
     * @param string $context
     * @return static
     */
    final public function forBy(string $context): static
    {
        $this->sqlParts['for'] = $context;
        return $this;
    }
}

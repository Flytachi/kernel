<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Thread\Traits;

use Flytachi\Kernel\Extra;
use Flytachi\Kernel\Src\Thread\Entity\CCondition;
use Flytachi\Kernel\Src\Thread\Entity\CStatus;

trait ClusterStatement
{
    protected function preparation(): void
    {
    }

    public static function threadList(): array
    {
        $keys = Extra::store(static::$EC_THREADS . '/' . static::stmName(), false)
            ->keys();
        foreach ($keys as $key => $path) {
            $keys[$key] = (int) trim($path, '_');
        }
        return $keys;
    }

    public static function threadQty(): int
    {
        $keys = Extra::store(static::$EC_THREADS . '/' . static::stmName(), false)
            ->keys();
        return count($keys);
    }

    protected function threadCount(): int
    {
        $keys = Extra::store(static::$EC_THREADS . '/' . static::stmName(), false)
            ->keys();
        return count($keys);
    }

    final protected function prepare(int $balancer = 1): void
    {
        // start
        /** @var CStatus $status */
        $status = Extra::store(static::$EC_MAIN)->read(static::stmName());
        $status->condition = CCondition::PREPARATION;
        Extra::store(static::$EC_MAIN)->write(static::stmName(), $status);
        $this->logger?->debug("set condition => " . $status->condition->name);

        // preparation
        Extra::store(static::$EC_THREADS . '/' . static::stmName(), false);
        $status->balancer = $balancer;
        $this->balancer = $balancer;
        Extra::store(static::$EC_MAIN)->write(static::stmName(), $status);
        // custom
        $this->preparation();

        // end
        /** @var CStatus $status */
        $status = Extra::store(static::$EC_MAIN)->read(static::stmName());
        $status->condition = CCondition::ACTIVE;
        Extra::store(static::$EC_MAIN)->write(static::stmName(), $status);
        $this->logger?->debug("set condition => " . $status->condition->name);
    }

    final protected function setCondition(CCondition $newCondition): void
    {
        /** @var CStatus $status */
        $status = Extra::store(static::$EC_MAIN)->read(static::stmName());
        $status->condition = $newCondition;
        Extra::store(static::$EC_MAIN)->write(static::stmName(), $status);
        $this->logger?->debug("set condition => " . $newCondition->name);
    }

    final protected function setInfo(array $newInfo): void
    {
        /** @var CStatus $status */
        $status = Extra::store(static::$EC_MAIN)->read(static::stmName());
        $status->info = $newInfo;
        Extra::store(static::$EC_MAIN)->write(static::stmName(), $status);
        $this->logger?->debug("set info => " . json_encode($newInfo));
    }

    protected function preparationThreadBefore(int $pid): void
    {
        Extra::store(static::$EC_THREADS . '/' . static::stmName(), false)
            ->write("_{$pid}_", new CStatus(
                pid: $pid,
                className: static::class,
                condition: CCondition::ACTIVE,
                startedAt: time(),
            ));
        $this->logger?->debug("started");
    }

    protected function preparationThreadAfter(int $pid): void
    {
        Extra::store(static::$EC_THREADS . '/' . static::stmName(), false)
            ->del("_{$pid}_");
        $this->logger?->debug("finished");
    }
}

<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Thread\Traits;

use Flytachi\Kernel\Extra;
use Flytachi\Kernel\Src\Thread\Entity\ProcessCondition;
use Flytachi\Kernel\Src\Thread\Entity\ProcessCStatus;
use Flytachi\Kernel\Src\Thread\Entity\ProcessInfo;
use Flytachi\Kernel\Src\Thread\Entity\ProcessStats;
use Flytachi\Kernel\Src\Thread\Entity\ProcessStatus;

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

    /**
     * @param bool $showStats
     * @return ProcessInfo[]
     */
    public static function threadListInfo(bool $showStats = false): array
    {
        $store = Extra::store(static::$EC_THREADS . '/' . static::stmName(), false);
        $keys = $store->keys();
        foreach ($keys as $key => $path) {
            $pid = (int) trim($path, '_');
            $keys[$key] = new ProcessInfo(
                status: $store->read($path),
                stats: $showStats ? ProcessStats::ofPid($pid) : null
            );
        }
        return $keys;
    }

    public static function threadInfo(int $threadPid, bool $showStats = false): ?ProcessInfo
    {
        $store = Extra::store(static::$EC_THREADS . '/' . static::stmName(), false);
        $status = $store->read("_{$threadPid}_");
        if (!$status) return null;

        return new ProcessInfo(
            status: $status,
            stats: $showStats ? ProcessStats::ofPid($threadPid) : null
        );
    }

    final protected function threadSetCondition(int $threadPid, ProcessCondition $newCondition): void
    {
        $store = Extra::store(static::$EC_THREADS . '/' . static::stmName(), false);
        /** @var ProcessStatus $status */
        $status = $store->read("_{$threadPid}_");
        $status->condition = $newCondition;
        $store->write(static::stmName(), $status);
        $this->logger?->debug("set condition => " . $newCondition->name);
    }

    public static function threadQty(): int
    {
        $keys = Extra::store(static::$EC_THREADS . '/' . static::stmName(), false)
            ->keys();
        return count($keys);
    }

    final protected function prepare(int $balancer = 1): void
    {
        $store = Extra::store(static::$EC_MAIN);
        // start
        /** @var ProcessCStatus $status */
        $status = $store->read(static::stmName());
        $status->condition = ProcessCondition::PREPARATION;
        $store->write(static::stmName(), $status);
        $this->logger?->debug("set condition => " . $status->condition->name);

        // preparation
        Extra::store(static::$EC_THREADS . '/' . static::stmName(), false);
        $status->balancer = $balancer;
        $this->balancer = $balancer;
        $store->write(static::stmName(), $status);
        // custom
        $this->preparation();

        // end
        /** @var ProcessCStatus $status */
        $status = $store->read(static::stmName());
        $status->condition = ProcessCondition::ACTIVE;
        $store->write(static::stmName(), $status);
        $this->logger?->debug("set condition => " . $status->condition->name);
    }

    final protected function setCondition(ProcessCondition $newCondition): void
    {
        $store = Extra::store(static::$EC_MAIN);
        /** @var ProcessCStatus $status */
        $status = $store->read(static::stmName());
        $status->condition = $newCondition;
        $store->write(static::stmName(), $status);
        $this->logger?->debug("set condition => " . $newCondition->name);
    }

    final protected function setInfo(array $newInfo): void
    {
        /** @var ProcessCStatus $status */
        $status = Extra::store(static::$EC_MAIN)->read(static::stmName());
        $status->info = $newInfo;
        Extra::store(static::$EC_MAIN)->write(static::stmName(), $status);
        $this->logger?->debug("set info => " . json_encode($newInfo));
    }

    protected function preparationThreadBefore(int $pid): void
    {
        Extra::store(static::$EC_THREADS . '/' . static::stmName(), false)
            ->write("_{$pid}_", new ProcessStatus(
                pid: $pid,
                condition: ProcessCondition::STARTED,
                startedAt: time()
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

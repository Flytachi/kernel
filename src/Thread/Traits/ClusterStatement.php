<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Thread\Traits;

use Flytachi\Kernel\Src\Unit\File\JSON;

trait ClusterStatement
{
    protected function preparation(): void {}

    public static function threadList(): array
    {
        $files = glob(static::stmThreadsPath() . '/*.json');
        foreach ($files as $key => $path) $files[$key] = (int) basename($path, '.json');
        return $files;
    }

    public static function threadQty(): int
    {
        return count(glob(static::stmThreadsPath() . '/*.json'));
    }

    protected function threadCount(): int
    {
        $files = glob(static::stmThreadsPath() . "/*.json");
        return count($files);
    }

    final protected function prepare(int $balancer = 10): void
    {
        // start
        $data = JSON::read(static::stmPath());
        $data['condition'] = 'preparation';
        JSON::write(static::stmPath(), $data);
        static::$logger->debug("set condition => preparation");

        // preparation
        $pathThreads = static::stmThreadsPath();
        if (!is_dir($pathThreads)) mkdir($pathThreads, recursive: true);
        $data['balancer'] = $balancer;
        $this->balancer = $balancer;
        JSON::write(static::stmPath(), $data);
        // custom
        $this->preparation();

        // end
        $data = JSON::read(static::stmPath());
        $data['condition'] = 'active';
        JSON::write(static::stmPath(), $data);
        static::$logger->debug("set condition => active");
    }

    final protected function setCondition(string $newCondition): void
    {
        $data = JSON::read(static::stmPath());
        $data['condition'] = $newCondition;
        JSON::write(static::stmPath(), $data);
        static::$logger->debug("set condition => " . $newCondition);
    }

    final protected function setInfo(array $newInfo): void
    {
        $data = JSON::read(static::stmPath());
        $data['info'] = $newInfo;
        JSON::write(static::stmPath(), $data);
        static::$logger->debug("set info => " . json_encode($data));
    }

    protected function preparationThreadBefore(int $pid): void
    {
        JSON::write(static::stmThreadsPath() . "/{$pid}.json", [
            'pid' => $pid,
        ]);
        static::$logger->debug("started");
    }

    protected function preparationThreadAfter(int $pid): void
    {
        unlink(static::stmThreadsPath() . "/{$pid}.json");
        static::$logger->debug("finished");
    }
}
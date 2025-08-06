<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Factory\Connection\Config\Common;

use Redis;

abstract class BaseRedisConfig implements RedisConfigInterface
{
    private ?Redis $store = null;

    final public function connect(float $timeout = 1.5, float $readTimeout = 2): void
    {
        if (is_null($this->store)) {
            $this->store = new Redis();
            $this->store->connect($this->host, $this->port, $timeout);
            $this->store->setOption(Redis::OPT_READ_TIMEOUT, $readTimeout);
            if ($this->password) {
                $this->store->auth($this->password);
            }
            $this->store->select($this->databaseIndex);
        }
    }

    final public function disconnect(): void
    {
        $this->store->close();
        $this->store = null;
    }

    final public function reconnect(): void
    {
        $this->disconnect();
        $this->connect();
    }

    /**
     * @return Redis
     */
    final public function connection(): Redis
    {
        $this->connect();
        return $this->store;
    }

    /**
     * @return bool
     */
    final public function ping(): bool
    {
        try {
            $this->connect();
            return $this->store->ping() === '+PONG';
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @return array{
     *     status: bool, latency: float|null, error: string|null
     * }
     */
    final public function pingDetail(): array
    {
        $start = microtime(true);
        $error = null;
        $status = false;

        try {
            $this->connect();
            $result = $this->store->ping();
            $status = $result === true || $result === '+PONG';
        } catch (\Throwable $e) {
            $error = $e->getMessage();
        }

        $latency = (microtime(true) - $start) * 1000;

        return [
            'status' => $status,
            'latency' => round($latency, 2),
            'error' => $error,
        ];
    }
}

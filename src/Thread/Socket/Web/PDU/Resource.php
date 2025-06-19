<?php

declare(strict_types=1);

namespace Flytachi\Kernel\Src\Thread\Socket\Web\PDU;

class Resource
{
    private $connect;
    private ?array $info;
    private array $store = [];

    /**
     * @param $connect
     * @param array|null $info
     */
    public function __construct($connect, ?array $info = null)
    {
        $this->connect = $connect;
        $this->info = $info;
    }

    public function getStore(): array
    {
        return $this->store;
    }

    public function setStore(array $store): void
    {
        $this->store = $store;
    }

    public function getConnect()
    {
        return $this->connect;
    }

    public function getInfo(string $key): mixed
    {
        return $this->info[$key] ?? null;
    }

    public function info(): array
    {
        return $this->info;
    }

    public function __toString(): string
    {
        return (string) $this->connect;
    }
}

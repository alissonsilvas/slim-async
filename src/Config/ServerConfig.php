<?php

declare(strict_types=1);

namespace App\Config;

class ServerConfig
{
    public function __construct(
        private string $host = '0.0.0.0',
        private int $port = 9501,
        private int $workerNum = 4,
        private int $maxRequest = 5000,
        private bool $reloadAsync = true,
        private bool $enableCoroutine = true
    ) {
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getPort(): int
    {
        return $this->port;
    }

    public function getServerOptions(): array
    {
        return [
            'worker_num' => $this->workerNum,
            'max_request' => $this->maxRequest,
            'reload_async' => $this->reloadAsync,
            'enable_coroutine' => $this->enableCoroutine,
        ];
    }
}

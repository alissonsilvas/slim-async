<?php

declare(strict_types=1);

namespace SlimAsync\Server;

use Psr\Http\Server\RequestHandlerInterface;
use SlimAsync\Config\ServerConfig;
use SlimAsync\Converter\SwooleRequestConverter;
use SlimAsync\Converter\SwooleResponseConverter;
use Swoole\Http\Request as SwooleRequest;
use Swoole\Http\Response as SwooleResponse;
use Swoole\Http\Server;

class SwooleServer
{
    private Server $server;
    private SwooleRequestConverter $requestConverter;
    private SwooleResponseConverter $responseConverter;

    public function __construct(
        private ServerConfig $config,
        private RequestHandlerInterface $app
    ) {
        $this->server = new Server($config->getHost(), $config->getPort());
        $this->requestConverter = new SwooleRequestConverter();
        $this->responseConverter = new SwooleResponseConverter();
    }

    public function start(): void
    {
        $this->configureServer();
        $this->setupRequestHandler();
        $this->logServerStart();
        $this->server->start();
    }

    private function configureServer(): void
    {
        $this->server->set($this->config->getServerOptions());
    }

    private function setupRequestHandler(): void
    {
        $this->server->on('request', function (SwooleRequest $swooleRequest, SwooleResponse $swooleResponse): void {
            $this->handleRequest($swooleRequest, $swooleResponse);
        });
    }

    private function handleRequest(SwooleRequest $swooleRequest, SwooleResponse $swooleResponse): void
    {
        $request = $this->requestConverter->convert($swooleRequest);
        $response = $this->app->handle($request);
        $this->responseConverter->convert($response, $swooleResponse);
    }

    private function logServerStart(): void
    {
        echo "✅ Servidor Swoole + Slim rodando em http://{$this->config->getHost()}:{$this->config->getPort()}\n";
    }
}

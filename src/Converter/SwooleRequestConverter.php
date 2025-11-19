<?php

declare(strict_types=1);

namespace App\Converter;

use Psr\Http\Message\ServerRequestInterface;
use Slim\Psr7\Factory\ServerRequestFactory;
use Swoole\Http\Request as SwooleRequest;

class SwooleRequestConverter
{
    public function __construct(
        private ServerRequestFactory $serverRequestFactory = new ServerRequestFactory()
    ) {
    }

    public function convert(SwooleRequest $swooleRequest): ServerRequestInterface
    {
        $request = $this->createBaseRequest($swooleRequest);
        $request = $this->addHeaders($request, $swooleRequest);
        $request = $this->addBody($request, $swooleRequest);
        $request = $this->addQueryParams($request, $swooleRequest);
        $request = $this->addParsedBody($request, $swooleRequest);

        return $request;
    }

    private function createBaseRequest(SwooleRequest $swooleRequest): ServerRequestInterface
    {
        $method = (string) ($swooleRequest->server['request_method'] ?? 'GET');
        $uri = (string) ($swooleRequest->server['request_uri'] ?? '/');
        $serverParams = (array) ($swooleRequest->server ?? []);

        return $this->serverRequestFactory->createServerRequest($method, $uri, $serverParams);
    }

    private function addHeaders(ServerRequestInterface $request, SwooleRequest $swooleRequest): ServerRequestInterface
    {
        $headers = (array) ($swooleRequest->header ?? []);

        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        return $request;
    }

    private function addBody(ServerRequestInterface $request, SwooleRequest $swooleRequest): ServerRequestInterface
    {
        $body = (string) ($swooleRequest->rawContent() ?: '');

        if (!empty($body)) {
            $request->getBody()->write($body);
        }

        return $request;
    }

    private function addQueryParams(ServerRequestInterface $request, SwooleRequest $swooleRequest): ServerRequestInterface
    {
        if (!empty($swooleRequest->get)) {
            $request = $request->withQueryParams($swooleRequest->get);
        }

        return $request;
    }

    private function addParsedBody(ServerRequestInterface $request, SwooleRequest $swooleRequest): ServerRequestInterface
    {
        if (!empty($swooleRequest->post)) {
            $request = $request->withParsedBody($swooleRequest->post);
        }

        return $request;
    }
}

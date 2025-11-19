<?php

declare(strict_types=1);

namespace App\Converter;

use Psr\Http\Message\ResponseInterface;
use Swoole\Http\Response as SwooleResponse;

class SwooleResponseConverter
{
    public function convert(ResponseInterface $response, SwooleResponse $swooleResponse): void
    {
        $this->setStatusCode($response, $swooleResponse);
        $this->setHeaders($response, $swooleResponse);
        $this->setBody($response, $swooleResponse);
    }

    private function setStatusCode(ResponseInterface $response, SwooleResponse $swooleResponse): void
    {
        $swooleResponse->status($response->getStatusCode());
    }

    private function setHeaders(ResponseInterface $response, SwooleResponse $swooleResponse): void
    {
        foreach ($response->getHeaders() as $name => $values) {
            foreach ($values as $value) {
                $swooleResponse->header($name, $value);
            }
        }
    }

    private function setBody(ResponseInterface $response, SwooleResponse $swooleResponse): void
    {
        $swooleResponse->end((string) $response->getBody());
    }
}

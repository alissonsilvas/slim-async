<?php

declare(strict_types=1);

namespace App\Presentation\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

abstract class BaseController
{
    protected function successResponse(Response $response, array $data, int $statusCode = 200): Response
    {
        $body = $response->getBody();
        $body->write(json_encode($data));

        return $response->withStatus($statusCode)->withHeader('Content-Type', 'application/json');
    }

    protected function errorResponse(Response $response, string $message, int $statusCode = 400): Response
    {
        $body = $response->getBody();
        $body->write(json_encode(['error' => $message]));

        return $response->withStatus($statusCode)->withHeader('Content-Type', 'application/json');
    }

    protected function validationErrorResponse(Response $response, string $message): Response
    {
        return $this->errorResponse($response, $message, 400);
    }

    protected function notFoundResponse(Response $response, string $message = 'Resource not found'): Response
    {
        return $this->errorResponse($response, $message, 404);
    }

    protected function conflictResponse(Response $response, string $message): Response
    {
        return $this->errorResponse($response, $message, 409);
    }

    protected function serverErrorResponse(Response $response, string $message = 'Internal server error'): Response
    {
        return $this->errorResponse($response, $message, 500);
    }

    protected function noContentResponse(Response $response): Response
    {
        return $response->withStatus(204);
    }

    protected function getJsonData(Request $request): ?array
    {
        $body = $request->getBody();
        $body->rewind();
        $contents = $body->getContents();

        if (empty($contents)) {
            return null;
        }

        $data = json_decode($contents, true);

        return json_last_error() === JSON_ERROR_NONE ? $data : null;
    }

    protected function handleException(\Exception $e, Response $response): Response
    {
        if ($e instanceof \InvalidArgumentException) {
            return $this->validationErrorResponse($response, $e->getMessage());
        }

        if ($e instanceof \DomainException) {
            return $this->notFoundResponse($response, $e->getMessage());
        }

        return $this->serverErrorResponse($response);
    }

    protected function getPaginationParams(Request $request): array
    {
        $queryParams = $request->getQueryParams();
        $page = max(1, (int) ($queryParams['page'] ?? 1));
        $limit = min(100, max(1, (int) ($queryParams['limit'] ?? 10)));

        return [$page, $limit];
    }
}

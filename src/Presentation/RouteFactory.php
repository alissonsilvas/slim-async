<?php

declare(strict_types=1);

namespace App\Presentation;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

class RouteFactory
{
    public static function createCrudRoutes(
        App $app,
        string $basePath,
        string $controllerClass,
        string $createUseCase,
        string $getUseCase,
        string $updateUseCase,
        string $deleteUseCase,
        string $listUseCase,
        string $createDtoClass,
        string $updateDtoClass
    ): void {
        $container = Container::getInstance();

        // POST /{basePath} - Create
        $app->post($basePath, function (Request $request, Response $response) use ($container, $controllerClass, $createUseCase, $createDtoClass) {
            $controller = new $controllerClass();
            $useCase = $container->get($createUseCase);

            return $controller->create($request, $response, $useCase, $createDtoClass);
        });

        // GET /{basePath}/{id} - Get by ID
        $app->get($basePath . '/{id}', function (Request $request, Response $response, array $args) use ($container, $controllerClass, $getUseCase) {
            $controller = new $controllerClass();
            $useCase = $container->get($getUseCase);

            return $controller->getById($request, $response, $args['id'], $useCase);
        });

        // PUT /{basePath}/{id} - Update
        $app->put($basePath . '/{id}', function (Request $request, Response $response, array $args) use ($container, $controllerClass, $updateUseCase, $updateDtoClass) {
            $controller = new $controllerClass();
            $useCase = $container->get($updateUseCase);

            return $controller->update($request, $response, $args['id'], $useCase, $updateDtoClass);
        });

        // DELETE /{basePath}/{id} - Delete
        $app->delete($basePath . '/{id}', function (Request $request, Response $response, array $args) use ($container, $controllerClass, $deleteUseCase) {
            $controller = new $controllerClass();
            $useCase = $container->get($deleteUseCase);

            return $controller->delete($request, $response, $args['id'], $useCase);
        });

        // GET /{basePath} - List
        $app->get($basePath, function (Request $request, Response $response) use ($container, $controllerClass, $listUseCase) {
            $controller = new $controllerClass();
            $useCase = $container->get($listUseCase);

            return $controller->list($request, $response, $useCase);
        });
    }

    public static function createHealthRoutes(App $app): void
    {
        $app->get('/', function (Request $request, Response $response): Response {
            $response->getBody()->write('✅ Slim + Swoole está rodando!');

            return $response;
        });

        $app->get('/health', function (Request $request, Response $response): Response {
            $healthData = [
                'status' => 'healthy',
                'timestamp' => date('Y-m-d H:i:s'),
                'service' => 'slim-async-api',
                'version' => '1.0.0',
            ];

            $response->getBody()->write(json_encode($healthData));

            return $response->withHeader('Content-Type', 'application/json');
        });

        $app->get('/health/mongo', function (Request $request, Response $response): Response {
            try {
                $mongo = new \App\Infrastructure\Mongo\MongoConnection();
                $collection = $mongo->collection('health_check');

                $result = $collection->insertOne([
                    'message' => 'Health check MongoDB',
                    'timestamp' => new \DateTime(),
                    'service' => 'slim-async-api',
                ]);

                $healthData = [
                    'status' => 'healthy',
                    'database' => 'mongodb',
                    'connection' => 'success',
                    'inserted_id' => (string) $result->getInsertedId(),
                    'timestamp' => date('Y-m-d H:i:s'),
                ];

                $response->getBody()->write(json_encode($healthData));

                return $response->withHeader('Content-Type', 'application/json');
            } catch (\Exception $e) {
                $errorData = [
                    'status' => 'unhealthy',
                    'database' => 'mongodb',
                    'connection' => 'failed',
                    'error' => $e->getMessage(),
                    'timestamp' => date('Y-m-d H:i:s'),
                ];

                $response->getBody()->write(json_encode($errorData));

                return $response->withStatus(503)->withHeader('Content-Type', 'application/json');
            }
        });

        $app->get('/health/async', function (Request $request, Response $response): Response {
            $startTime = microtime(true);

            // Simula operação assíncrona
            usleep(10000);

            $endTime = microtime(true);
            $executionTime = round(($endTime - $startTime) * 1000, 2);

            $healthData = [
                'status' => 'healthy',
                'async' => 'enabled',
                'coroutines' => 'working',
                'execution_time_ms' => $executionTime,
                'timestamp' => date('Y-m-d H:i:s'),
            ];

            $response->getBody()->write(json_encode($healthData));

            return $response->withHeader('Content-Type', 'application/json');
        });
    }
}

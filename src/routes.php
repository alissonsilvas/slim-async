<?php
use App\Infrastructure\Mongo\MongoConnection;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/', function (Request $request, Response $response) {
    $response->getBody()->write("Olá! Slim + Swoole está rodando 🚀");
    return $response;
});

$app->get('/users', function (Request $request, Response $response) {
    // Executa a consulta ao Mongo em coroutine (não bloqueia o servidor)
    $result = null;

    go(function () use (&$result) {
        $mongo = new MongoConnection();
        $collection = $mongo->collection('users');
        $result = $collection->find([]);
    });

    // Atenção: como isso é assíncrono, você pode usar um canal ou await pattern
    // Para exemplo simples, retornamos resposta imediata
    $response->getBody()->write(json_encode(['status' => 'consulta enviada']));
    return $response->withHeader('Content-Type', 'application/json');
});

<?php

use Slim\Factory\AppFactory;
use Slim\Swoole\ServerRequestFactory;
use Swoole\Http\Server;

require __DIR__ . '/vendor/autoload.php';

\Swoole\Runtime::enableCoroutine(true, SWOOLE_HOOK_ALL);

$app = AppFactory::create();

require __DIR__ . '/src/routes.php';

$server = new Server('0.0.0.0', 9501);

// Configurações do servidor
$server->set([
    'worker_num' => 4,          // número de workers
    'max_request' => 5000,      // reinicia worker após 5000 requests (evita leaks)
    'reload_async' => true,
    'enable_coroutine' => true,
]);

// Cria callback para tratar requisições HTTP
$server->on('request', ServerRequestFactory::createRequestCallback($app));

// Log simples no console
echo "✅ Servidor Swoole + Slim rodando em http://127.0.0.1:9501\n";

// Inicia o servidor
$server->start();
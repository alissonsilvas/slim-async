<?php

declare(strict_types=1);

use App\Config\ServerConfig;
use App\Server\SwooleServer;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

\Swoole\Runtime::enableCoroutine(SWOOLE_HOOK_ALL);

$app = AppFactory::create();
$routes = require __DIR__ . '/routes.php';
$routes($app);

$config = new ServerConfig();
$server = new SwooleServer($config, $app);

$server->start();

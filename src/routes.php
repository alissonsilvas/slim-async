<?php

declare(strict_types=1);

use App\Routes\HealthRoutes;
use App\Routes\UserRoutes;
use Slim\App;

/*
 * Registra todas as rotas organizadas por domínio
 */
return function (App $app): void {
    HealthRoutes::register($app);
    UserRoutes::register($app);
};

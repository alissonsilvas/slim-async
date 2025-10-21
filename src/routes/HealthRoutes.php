<?php

declare(strict_types=1);

namespace App\Routes;

use App\Presentation\RouteFactory;
use Slim\App;

class HealthRoutes
{
    public static function register(App $app): void
    {
        RouteFactory::createHealthRoutes($app);
    }
}

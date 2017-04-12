<?php

use App\Controllers\HomeController;
use App\Controllers\TestController;
use Fine\Middlewares\CSRFTokenMiddleware;

$app->setGlobalMiddlewares([
    CSRFTokenMiddleware::class
]);

HomeController::mount(TestController::class, 'test');

$app->mount(HomeController::class, '/home');

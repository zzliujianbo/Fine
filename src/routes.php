<?php

use App\Controllers\HomeController;
use App\Controllers\TestController;

$app->mount(HomeController::class, '/');
$app->mount(TestController::class, '/test');

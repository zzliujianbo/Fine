<?php

require_once '../vendor/autoload.php';

$rootDir = realpath(__DIR__.'/../');

$app = Fine\Application::getInstance($rootDir);

return function () use ($app) {

    require_once '../src/event.php';
    require_once '../src/routes.php';

    $app->run(!env('APP_DEBUG'));
    //$app->run(function(FastRoute\RouteCollector $route){

    //    require '../src/App/routes.php';

    //}, !env('APP_DEBUG'));
};

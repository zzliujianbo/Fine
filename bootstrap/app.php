<?php

require_once '../vendor/autoload.php';

//加载配置文件
$dotenv = new Dotenv\Dotenv('../');
$dotenv->load();

//根目录
$base_path = realpath(__DIR__.'/../');

$app = Fine\Application::getInstance($base_path);

// 加载单例
// 加载事件


return function () use ($app) {

    require_once '../src/event.php';
    require_once '../src/routes.php';

    $app->run(!env('APP_DEBUG'));
    //$app->run(function(FastRoute\RouteCollector $route){

    //    require '../src/App/routes.php';

    //}, !env('APP_DEBUG'));
};

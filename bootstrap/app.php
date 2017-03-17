<?php

require_once '../vendor/autoload.php';

/********** 1.初始化根目录 **********/
$base_path = realpath(__DIR__.'/../');

/********** 2.加载配置文件 **********/
$dotenv = new Dotenv\Dotenv('../src/');
$dotenv->load();

/********** 3.加载Application **********/
$app = Fine\Application::getInstance($base_path);

/********** 4.加载Eloquent ORM **********/
// 参考 https://github.com/illuminate/database

$database = require_once '../src/config/database.php';

$capsule = new Illuminate\Database\Capsule\Manager();

// 添加数据库连接
foreach ($database['connections'] as $key => $value) {
    if ($database['default'] === $key) {
        $capsule->addConnection($value);
    }
    else {
        $capsule->addConnection($value,$key);
    }
}

$capsule->setAsGlobal();

// 启动Eloquent
$capsule->bootEloquent();

// 加载单例
// 加载事件

/********** 5.运行 **********/
return function () use ($app) {

    require_once '../src/event.php';
    require_once '../src/routes.php';

    $app->run(!env('APP_DEBUG'));
    //$app->run(function(FastRoute\RouteCollector $route){

    //    require '../src/App/routes.php';

    //}, !env('APP_DEBUG'));
};

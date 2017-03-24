# Fine
Fine是一款脚手架框架。提供了路由、视图等功能。路由理念参考了`Laravel`和`Python`的`bottle`两个框架理念。支持`bottle`的`mount`挂载方式。路由框架采用的是`fast-route`框架。视图使用的是原生的PHP页面。ORM框架采用的是 Eloquent ORM。

## 版本要求
* PHP 5.3版本以上

## 目录说明
* Fine框架主目录
* public http服务器指向目录
* src 代码存放位置
* src\Common 项目的公用代码
* src\Controllers controller目录
* resources 一些资源文件
* resources\views 视图存放位置
* storage 存储目录
* storage\cache 文件缓存的目录
* storage\session session目录

## 配置文件
配置文件支持`Dotenv`方式。修改`src/.env`文件为你的配置。
```
APP_DEBUG=true      #是否开启DEBUG
APP_TIMEZONE=PRC    #时区

CACHE_DRIVER=file   #缓存存放文件

DB_HOST=127.0.0.1   #数据库服务器
DB_DATABASE=fine    #数据库名字
DB_USERNAME=fine    #数据库用户名
DB_PASSWORD=fine    #数据库密码
```

## 事件调用
修改`src\event.php`文件。
```php

// 添加事件方法
// 可以添加多个名为route的事件方法
$app->bindEvent('route', function () {
    return 'route';
});

// 触发名为route的事件方法
// 可以在此做一些权限验证、一些中间件的方法调用
$responses = $app->triggerEvent('route');
```
## Controller
首先在src\Controllers目录下新建一个controller文件，可以参考HomeController.php文件。

```php
namespace App\Controllers;

use Fine\Controller;
use Fine\Request;
use Stringy\Stringy;

use App\Models\User;

class TestController extends Controller
{

    public static function routes()
    {
        $route = [
            ['method' => 'get', 'uri' => '/', 'action' => 'getIndex'],
        ];
        return $route;
    }

    public function getIndex()
    {
        return 'test';
    }
}
```

然后修改`routes.php`文件，将`TestController`加到主路由中。
```php
$app->mount(TestController::class, '/test');
```

这个controller类中需要一个静态方法routes。作用是为了将本controller类中的action返回给fast-route框架，供路由框架进行分发。
```php
public static function routes()
{
    $route = [
        [
            'method' => 'get',      //http method
            'uri' => '/',           //访问的URL
            'action' => 'getIndex'  //函数名字
        ],
    ];
    return $route;
}
```
其中访问的`URL`路由是由`mount`的`url`+数组中的`uri`联合组成。

## 部署
部署时请将文档目录指向到`public`目录。

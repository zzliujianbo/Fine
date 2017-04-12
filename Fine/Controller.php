<?php

namespace Fine;

use Exception;
use Stringy\Stringy;

abstract class Controller
{

    protected $cache = null;

    //TODO 父类的静态变量在子类中是共享的，如果子类没有重写静态变量，那么其他没有重写静态变量的子类也会受到影响。
    //protected static $middlewares = null;

    //protected static $routes = null;

    private static $controllers = null;

    public function __construct()
    {
        $this->app = app();
        $this->cache = $this->app->getCache();
    }

    public static function mount($controller_class, $prefix = null)
    {
        //if ($controller->getIsMount()) {
        //    throw new Exception('controller [' . $controller. '] is mount');
        //} else {
        //    $controller->mountSuccess();
        //}

        if ($controller_class) {

            if (!$prefix) {
                $prefix = get_controller_prefix($controller_class);
            }

            if (!self::$controllers) {
                self::$controllers = [];
            }

            //self::$controller[trim($prefix, '/')] = $controller_class;
            self::$controllers[static::class][trim($prefix, '/')] = $controller_class;
        } else {
            throw new Exception('controller is null');
        }
    }

    public static function getController()
    {
        return (isset(self::$controllers[static::class]) ? self::$controllers[static::class] : []);
    }

    public static function getRoutes()
    {
        return (isset(static::$routes) ? static::$routes : null);
    }

    public static function getMiddlewares()
    {
        return (isset(static::$middlewares) ? static::$middlewares : null);
    }

    //abstract public static function routes();
}

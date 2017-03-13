<?php

namespace Fine;

use Exception;
use Stringy\Stringy;

abstract class Controller
{

    protected $cache = null;

    public function __construct()
    {
        $this->app = app();
        $this->cache = $this->app->getCache();
    }

    private static $controller = [];

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

            //self::$controller[trim($prefix, '/')] = $controller_class;
            self::$controller[static::class][trim($prefix, '/')] = $controller_class;
        } else {
            throw new Exception('controller is null');
        }
    }

    public static function getController()
    {
        return (isset(self::$controller[static::class]) ? self::$controller[static::class] : []);
    }

    abstract public static function routes();


    public function renderView($view, $data = null)
    {
        return render_view($view, $data);
    }
}

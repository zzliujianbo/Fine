<?php

namespace Fine;

use FastRoute;
use PHPFluent\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;

class Application
{
    protected $basePath;

    protected $routes = [];

    protected $dispatcher;

    public function version()
    {
        return 'Fine (0.0.1)';
    }

    public static function getInstance($basePath)
    {
        $instance = new static($basePath);
        return $instance->addSingleton($instance, 'app');
    }

    private function __construct($basePath)
    {
        $this->basePath = $basePath;
        $this->init();
    }

    private function init()
    {
        date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));
        //$this->initEvent();
        //$this->initCache();
        //$this->initView();
    }

    private function initCache()
    {
        $driver = env('CACHE_DRIVER');
        $doctrine = null;
        if ($driver === 'file') {
            $doctrine = new FilesystemCache($this->basePath . '/storage/cache');
        }

        $cache = [];

        if ($doctrine) {
            $cache = new Cache($doctrine);
        }

        $this->addSingleton($cache, 'cache');
    }

    private function initSession()
    {
        $session_dir = $this->basePath . '/storage/session';
        session_save_path($session_dir);
        session_start();
    }

    private function initView()
    {
        $v = new View($this->basePath . '/src/resources/views/');
        $this->addSingleton($v, 'view');
    }

    public function getCache()
    {
        if (! $this->getSingleton('cache')) {
            $this->initCache();
        }
        return $this->getSingleton('cache');
    }

    public function getView()
    {
        if (! $this->getSingleton('view')) {
            $this->initView();
        }
        return $this->getSingleton('view');
    }

    public function addSingleton($instance, $name)
    {
        return Singleton::setInstance($instance, $name);
    }

    public function getSingleton($name)
    {
        return Singleton::getInstance($name);
    }

    public function bindEvent($name, $callback)
    {
        Event::bind($name, $callback);
    }

    public function triggerEvent($name)
    {
        return Event::fire($name);
    }

    //public function run(bool $routeCache = false) //php7支持，php5不支持此种写法
    public function run($routeCache = false)
    {
        $this->initSession();
        $obj = $this->dispatch($this->getDispatcher($routeCache));
        $this->render($obj);
    }

    public function dispatch($dispatcher)
    {
        // Fetch method and URI from somewhere
        $httpMethod = $this->getMethod();
        $uri = $this->getPathInfo();

        // Strip query string (?foo=bar) and decode URI
        if (false !== $pos = strpos($uri, '?')) {
            $uri = substr($uri, 0, $pos);
        }
        $uri = rawurldecode($uri);

        $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

        switch ($routeInfo[0]) {
        case FastRoute\Dispatcher::NOT_FOUND:
            return 'Fine 404';
        case FastRoute\Dispatcher::METHOD_NOT_ALLOWED:
            $allowedMethods = $routeInfo[1];
            return 'Fine 405';
        case FastRoute\Dispatcher::FOUND:
            return $this->handleFoundRoute($routeInfo);
        }
    }

    protected function handleFoundRoute($routeInfo)
    {
        $handler = $routeInfo[1];
        $vars = $routeInfo[2];

        if(method_exists($handler['controller_class'], $handler['action'])) {
            $controller = new $handler['controller_class']();
            //return $controller->{$handler['action']}($vars);
            return call_user_func_array([$controller, $handler['action']], $vars);
        } else {
            return 'Fine Method 404';
        }
    }

    protected function render($obj)
    {
        if ($obj) {
            if (is_array($obj) ) {
                if (isset($obj['render_type'])) {
                    switch($obj['render_type']) {
                        case 'view':
                            $this->getView()->render($obj['data']['view'], $obj['data']['data']);
                            return;
                    }
                } else {
                    echo to_json($obj);
                }
            } elseif (is_object($obj)) {
                echo to_json($obj);
            } else {
                echo $obj;
            }
        }
    }

    protected function getDispatcher($routeCache = false)
    {
        if ($routeCache) {
            return $this->createCachedDispatcher();
        } else {
            return $this->createSimpleDispatcher();
        }
    }

    protected function createSimpleDispatcher()
    {
        return FastRoute\simpleDispatcher(function ($r) {
            foreach ($this->routes as $route) {
                $r->addRoute($route['method'], $route['uri'], $route['action']);
            }
        });
    }

    protected function createCachedDispatcher()
    {
        return FastRoute\cachedDispatcher(function ($r) {
            foreach ($this->routes as $route) {
                $r->addRoute($route['method'], $route['uri'], $route['action']);
            }
        }, [
            //'cacheFile' => __DIR__ . getenv('CACHE_FILE_DIR') . '/route.cache',
            'cacheFile' => $this->basePath . '/storage/cache/route.cache',
            'cacheDisabled' => true,
        ]);
    }

    public function get($uri, $action)
    {
        $this->addRoute('GET', $uri, $action);
    }

    public function post($uri, $action)
    {
        $this->addRoute('POST', $uri, $action);
    }

    public function mount($controller_class, $prefix = null)
    {
        if (!$prefix) {
            $prefix = get_controller_prefix($controller_class);
        }

        $this->handleMount($controller_class, trim($prefix, '/'));
    }

    private function handleMount($controller_class, $prefix)
    {
        $this->addRouteMap($controller_class, $prefix);
        //$prefix = ($prefix === '/' ? $prefix : $prefix . '/');
        //$prefix = rtrim($prefix, '/') . '/';
        foreach ($controller_class::getController() as $p => $c) {
            $this->handleMount($c, $prefix . '/' . $p);
        }
    }

    private function addRouteMap($controller_class, $prefix)
    {
        //$routeMap = $controller->getRouteMap();
        $routeMap = $controller_class::routes();
        //$prefix =  trim($prefix, '/');
        foreach ($routeMap as $route) {
            //echo '/' . trim($prefix . '/' . ltrim($route['uri'], '/'), '/') . '<br />';
            $this->addRoute(
                strtoupper($route['method']),
                $prefix . '/' . ltrim($route['uri'], '/'),
                ['controller_class' => $controller_class, 'action' => $route['action']]
            );
        }
    }


    protected function addRoute($method, $uri, $action)
    {
        $uri = '/'.trim($uri, '/');

        $this->routes[] = ['method' => $method, 'uri' => $uri, 'action' => $action];
    }

    public function getPathInfo()
    {
        $query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';

        return '/'.trim(str_replace('?'.$query, '', $_SERVER['REQUEST_URI']), '/');
    }

    protected function getMethod()
    {
        if (isset($_POST['_method'])) {
            return strtoupper($_POST['_method']);
        } else {
            return $_SERVER['REQUEST_METHOD'];
        }
    }

}

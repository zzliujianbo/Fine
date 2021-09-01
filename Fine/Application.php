<?php

namespace Fine;

use FastRoute;
use Jenssegers\Blade\Blade;
use PHPFluent\Cache\Cache;
use Doctrine\Common\Cache\FilesystemCache;

class Application
{
    protected $rootUri;

    protected $rootDir;

    protected $cacheDir;

    protected $viewDir;

    protected $sessionDir;

    protected $routes = [];

    protected $dispatcher;

    protected $controller;

    protected $action;

    protected $requestRoute;

    protected $globalMiddlewares;

    public function version()
    {
        return 'Fine (0.0.1)';
    }

    public static function getInstance($rootDir)
    {
        $instance = new static($rootDir);
        return $instance->addSingleton($instance, 'app');
    }

    private function __construct($rootDir)
    {
        $this->rootDir = $rootDir;
        $this->init();
    }

    private function init()
    {
        date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

        $this->rootUri = rtrim($_SERVER['SCRIPT_NAME'], '/index.php');
        $this->cacheDir = $this->rootDir . '/storage/cache';
        $this->viewDir = $this->rootDir . '/src/resources/views';
        $this->sessionDir = $this->rootDir . '/storage/session';
        $this->initSession();
        $this->initENV();
        $this->initORM();
        //$this->initEvent();
        //$this->initCache();
        //$this->initView();
    }

    private function initENV()
    {
        $dotenv = \Dotenv\Dotenv::create($this->rootDir . '/src');
        $dotenv->load();
    }

    private function initCache()
    {
        $driver = env('CACHE_DRIVER');
        $doctrine = null;
        if ($driver === 'file') {
            $doctrine = new FilesystemCache($this->cacheDir);
        }

        $cache = [];

        if ($doctrine) {
            $cache = new Cache($doctrine);
        }

        $this->addSingleton($cache, 'cache');
    }

    private function initSession()
    {
        session_save_path($this->sessionDir);
        session_start();
    }

    private function initView()
    {
        $blade = new Blade($this->viewDir, $this->cacheDir);
        $compiler = $blade->compiler();
        $controls = ['link_a', 'textbox', 'radio', 'checkbox', 'hidden', 'drop_down_list'];
        foreach ($controls as $value) {
            $compiler->directive($value, function($expression) use ($value) {
                return "<?php echo " . $value . "({$expression})?>";
            });
        }
        $this->addSingleton($blade, 'view');
    }

    private function initORM()
    {
        // 参考 https://github.com/illuminate/database

        $database = require_once $this->rootDir . '/src/config/database.php';

        $capsule = new \Illuminate\Database\Capsule\Manager();

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
        $obj = $this->dispatch($this->getDispatcher($routeCache));
        $this->render($obj);
    }

    public function dispatch($dispatcher)
    {
        // Fetch method and URI from somewhere
        //$httpMethod = $this->getMethod();
        $httpMethod = Request::method();
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
        $route = $routeInfo[1];
        $route['params'] = $routeInfo[2];

        if(method_exists($route['controller_class'], $route['action'])) {
            $this->controller = $route['controller_class'];
            $this->action = $route['action'];

            $middlewares = $this->controller::getMiddlewares();

            //if (! $middlewares) {
                //$middlewares[] = new Middleware();
                //$middlewares[] = [
                //    'class' => Middleware::class,
                //    'only' => null,
                //    'except' => null
                //];
            //}

            $realMiddlewares = $this->globalMiddlewares;
            if($middlewares){
                foreach ($middlewares as $middleware) {
                    if (isset($middleware['only']) && ! in_array($this->action, $middleware['only'])) {
                        continue;
                    }
                    elseif (isset($middleware['except']) && in_array($this->action, $middleware['except'])) {
                        continue;
                    }
                    $realMiddlewares[] = $middleware['class'];
                }
            }

            if(isset($route['middleware'])){
                $realMiddlewares[] = $route['middleware'];
            }

            if (! $realMiddlewares) {
                $realMiddlewares[] = Middleware::class;
            }

            $this->requestRoute = [
                'route' => $route,
                'middlewareWarpper' => [
                    'middlewares' => $realMiddlewares,
                    'index' => 0
                ]
            ];

            //return (new $realMiddlewares[0]())->handle($this->requestRoute);
            $middleware = new $realMiddlewares[0]();
            $result = $middleware->handle($this->requestRoute);
            return $result;

            //if($middlewares){
            //    $middlewaresLength = count($middlewares);
            //    for ($i=0; $i < $middlewaresLength; $i++) {
            //        $middleware = new $middlewares[$i]($i + 1 <= $middlewaresLength ? $middlewares[$i + 1] : null);
            //        $result = $middleware.handle($routeInfo);
            //        if ($result) {
            //            return $result;
            //        }
            //    }
            //}

            //$controller = new $handler['controller_class']();
            //return $controller->{$handler['action']}($vars);
            //return call_user_func_array([$controller, $handler['action']], $vars);
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
                        echo $this->getView()->make($obj['data']['view'], $obj['data']['data'] ?: []);
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
                $r->addRoute($route['method'], $route['uri'], $route['route']);
            }
        });
    }

    protected function createCachedDispatcher()
    {
        return FastRoute\cachedDispatcher(function ($r) {
            foreach ($this->routes as $route) {
                $r->addRoute($route['method'], $route['uri'], $route['route']);
            }
        }, [
            //'cacheFile' => __DIR__ . getenv('CACHE_FILE_DIR') . '/route.cache',
            'cacheFile' => $this->cacheDir . '/route.cache',
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
        //$routeMap = $controller_class::routes();
        //$routeMap = $controller_class::$routes;
        $routeMap = $controller_class::getRoutes();
        //$prefix =  trim($prefix, '/');
        foreach ($routeMap as $route) {
            //echo '/' . trim($prefix . '/' . ltrim($route['uri'], '/'), '/') . '<br />';
            $uri = $prefix . '/' . ltrim($route['uri'], '/');
            $route['controller_class'] = $controller_class;
            $this->addRoute(
                strtoupper($route['method']),
                $uri,
                $route
            );
        }
    }


    protected function addRoute($method, $uri, $route)
    {
        $uri = '/'.trim($this->rootUri . '/' . $uri, '/');
        $route['uri'] = $uri;
        $this->routes[] = ['method' => $method, 'uri' => $uri, 'route' => $route];
    }

    public function getPathInfo()
    {
        $query = isset($_SERVER['QUERY_STRING']) ? $_SERVER['QUERY_STRING'] : '';

        return '/'.trim(str_replace('?'.$query, '', $_SERVER['REQUEST_URI']), '/');
    }

    public function getRootUri()
    {
        return $this->rootUri;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getRequestRoute()
    {
        return $this->requestRoute;
    }

    public function setGlobalMiddlewares(array $middlewares)
    {
        $this->globalMiddlewares = $middlewares;
    }
}

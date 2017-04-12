<?php

namespace Fine;

//abstract class Middleware
class Middleware
{
    //传递，静态，存放，闭包

    //abstract public function handle($routeInfo);

    public function handle($requestRoute)
    {
        return $this->next($requestRoute);
    }

    protected function next($requestRoute)
    {
        $route = $requestRoute['route'];
        $middlewares = $requestRoute['middlewareWarpper']['middlewares'];
        $index = &$requestRoute['middlewareWarpper']['index'];

        if (++$index < count($middlewares)) {
            $middleware =new $middlewares[$index]();
            //$requestRoute['middlewareWarpper']['index'] = $index;
            return $middleware->handle($requestRoute);
            //return new $middleware()->handle($route);
        }

        $controller = new $route['controller_class']();
        //return $controller->{$route['route']['action']}($vars);
        $result = call_user_func_array([$controller, $route['action']], $route['params']);
        return $result;
    }
}

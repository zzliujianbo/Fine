<?php
namespace Fine\Middlewares;

use Fine\Request;
use Fine\Middleware;
use Fine\Exceptions\CSRFTokenException;


class CSRFTokenMiddleware extends Middleware
{
    public function __construct()
    {
        if (! Request::session('csrf_token')) {
            $_SESSION['csrf_token'] = token();
        }
    }

    public function handle($requestRoute)
    {
        if (! $this->isReading()) {
            $sessionToken = Request::session('csrf_token');
            $token = Request::input('csrf_token') ?: Request::server('X_CSRF_TOKEN');

            if (! $sessionToken ||
             ! $token ||
             ! hash_equals($sessionToken, $token)) {
                throw new CSRFTokenException('token error');
            }
        }
        return $this->next($requestRoute);
    }

    protected function isReading()
    {
        return in_array(Request::method(), ['HEAD', 'GET', 'OPTIONS']);
    }
}

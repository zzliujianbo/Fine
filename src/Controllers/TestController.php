<?php


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

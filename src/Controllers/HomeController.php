<?php


namespace App\Controllers;

use Fine\Controller;
use Fine\Request;
use Stringy\Stringy;

use App\Models\User;

class HomeController extends Controller
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
        return view('home.index');
    }
}

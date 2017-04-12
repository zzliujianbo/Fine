<?php


namespace App\Controllers;

use Fine\Controller;
use Fine\Request;
use Stringy\Stringy;

use App\Models\User;

class HomeController extends Controller
{

    static $routes = [
        ['method' => 'get', 'uri' => '/', 'action' => 'getIndex'],
    ];

    public function getIndex()
    {
        return view('home.index');
    }
}

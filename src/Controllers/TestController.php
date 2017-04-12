<?php


namespace App\Controllers;

use Fine\Controller;
use Fine\Request;
use Stringy\Stringy;

use App\Models\User;

class TestController extends Controller
{

    static $routes = [
        ['method' => 'get', 'uri' => '/', 'action' => 'getIndex'],
    ];

    public function getIndex()
    {
        return 'test';
    }
}

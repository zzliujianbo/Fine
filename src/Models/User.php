<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    protected $primaryKey = 'id';
    
    //表名
    protected $table = 'users';
}

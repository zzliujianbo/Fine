<?php

namespace Fine;

class Singleton {

    //静态变量保存全局实例
    //private static $_instance = null;

    //私有构造函数，防止外界实例化对象
    //protected function __construct()
    //{
    //    // NOOP
    //}

    ////私有克隆函数，防止外办克隆对象
    //final private function __clone()
    //{
    //    // NOOP
    //}

    //final private function __wakeup()
    //{
    //    // NOOP
    //}

    ////静态方法，单例统一访问入口
    //static public function getInstance() {
    //    //if (is_null ( static::$_instance ) || isset ( static::$_instance )) {
    //    //    static::$_instance = new static();
    //    //}
    //    //return static::$_instance;

    //    static $instances = [];
    //    // Get the name of the calling class. The calling class is the concrete
    //    // class using this trait.
    //    $className = static::class;
    //    if (false === isset($instances[$className])) {
    //        $instances[$className] = new static();
    //    }
    //    var_dump($instances);
    //    return $instances[$className];
    //}

    //public function getName() {
    //    echo 'hello world!';
    //}


    // XXX: https://github.com/thesmart/php-singleton

    protected static $instances = [];

    private static function &getRef($name) {
        $name = $name ? $name : '_';
        if (!isset(self::$instances[$name])) {
            self::$instances[$name] = null;
        }
        return self::$instances[$name];
    }

    public static function hasInstance($name) {
        $name = $name ? $name : '_';
        return isset(self::$instances[$name]);
    }

    public static function getInstance($name) {
        if (self::hasInstance($name)) {
            return self::getRef($name);
        }
        return null;
    }

    public static function setInstance($instance, $name) {
        $ref = &self::getRef($name);
        $ref = $instance;
        return $ref;
    }

    public static function clearInstance($name = null) {
        $name = $name ? $name : '_';
        unset(self::$instances[$name]);
    }

    public static function getAllInstances() {
        return self::$instances;
    }

}

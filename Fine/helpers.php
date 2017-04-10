<?php

use Stringy\Stringy;
use Fine\Singleton;

if (! function_exists('env')) {
    function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'empty':
            case '(empty)':
                return '';

            case 'null':
            case '(null)':
                return;
        }

        $str = Stringy::create($value);
        if ($str->startsWith('"') && $str->endsWith('"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}

if (! function_exists('app')) {
    function app()
    {
        return Singleton::getInstance('app');
    }
}
if (! function_exists('view')) {
    function view($view, $data = null)
    {
        return ['render_type' => 'view', 'data' => ['view' => $view, 'data' => $data]];
    }
}

if (! function_exists('get_controller_prefix')) {
    function get_controller_prefix($controller_class_name)
    {
        //$str = Stringy::create(get_class($controller));
        $str = Stringy::create($controller_class_name);
        return $str->substr($str->indexOfLast('\\') + 1)->toLowerCase()->removeRight('controller');
    }
}

if (! function_exists('to_json')) {
    function to_json($data)
    {
        return json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}

if (! function_exists('curl_request')) {
    function curl_request($url, $data=array())
    {
        $ch = curl_init();
        if (is_array($data) && $data) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    // HTTPS跳过证书检查（不安全，最好检验证书是否存在）
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        //curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        return curl_exec($ch);
    }
}

if (! function_exists('curl_json')) {
    function curl_json($url, $data=array())
    {
        if (! $url) {
            return '';
        }

        $json_str = '';
        if (is_array($data)) {
            $json_str = json_encode($data, JSON_UNESCAPED_UNICODE);
        } else {
            $json_str = $data;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json_str);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);    // HTTPS跳过证书检查（不安全，最好检验证书是否存在）
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);  // 连接服务器超时时间
        //curl_setopt($ch, CURLOPT_TIMEOUT, 2);         // 连接服务器成功之后，缓存（下载）超时时间
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($json_str)
            )
        );
        return curl_exec($ch);
    }
}

if (! function_exists('random_str')) {
    function random_str($length = 16) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }
}

if (! function_exists('encode_html')) {
    function encode_html(&$str)
    {
        //htmlspecialchars($string,ENT_QUOTES);
        if (is_string($str)) {
            $str = htmlentities($str, ENT_QUOTES, 'UTF-8');
        }
        return $str;
    }
}

if (! function_exists('decode_html')) {
    function decode_html($str)
    {
        if (is_string($str)) {
            return html_entity_decode($str, ENT_QUOTES, 'UTF-8');
        }
        return $str;
    }
}

if (! function_exists('rootUri')) {
    function root_uri()
    {
        return app()->getRootUri();
    }
}

if (! function_exists('url')) {
    function url($uri)
    {
        if(stripos($uri, 'http') !== 0) {
            $uri = root_uri() . $uri;
        }
        return $uri;
    }
}

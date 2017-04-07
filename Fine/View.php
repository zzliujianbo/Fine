<?php

namespace Fine;

class View {

    protected $viewDir;

    protected $title;

    public function __construct($view_Dir)
    {
        $this->viewDir = $view_Dir;
    }

    public function render($filename, $data = null)
    {
        if ($data) {
            // $data = (array) $data;
             $data = is_object($data) ? get_object_vars($data) : $data;

            if (is_array($data)) {
                foreach ($data as $key => $value) {
                    if (is_string($value)) {
                        $this->{$key} = $this->encode($value);
                    //} elseif (is_array($value)){
                    // array_walk_recursive
                    } else {
                        $this->{$key} = $value;
                    }
                }
            } else {
                echo to_json($data);
            }
        }

        //require_once $this->viewPath . trim($filename, '/') . '.view.php';
        $this->contains($filename);
    }
    public function header($filename, $title = null, array $css = null, array $js = null)
    {
        $this->title = $title;
        require_once $this->viewPath($filename);
    }

    public function footer($filename, array $js = null)
    {
        require_once $this->viewPath($filename);
    }

    public function contains($filename, array $params = null)
    {
        require_once $this->viewPath($filename);
    }

    public function viewPath($filename)
    {
        return $this->viewDir . trim($filename, '/') . '.view.php';
    }

    public function encode($str)
    {
        //htmlspecialchars($string,ENT_QUOTES);
        //return htmlentities($str, ENT_QUOTES, 'UTF-8');
        return encodeHTML($str);
    }

    public function decode($str)
    {
        //return html_entity_decode($str, ENT_QUOTES, 'UTF-8');
        return decodeHTML($str);
    }
}

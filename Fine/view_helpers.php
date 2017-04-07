<?php
if (! function_exists('link_a')) {
    function link_a($text, $url, $class = null, $style = null, $attr = [])
    {
        $attr = BuildTag::mergeCss($class, $style, $attr);
        $attr['href'] = $url;
        BuildTag::render('a', $attr, $text, true);
    }
}

if (! function_exists('textbox')) {
    function textbox($name, $value, $class = null, $style = null, $attr = [])
    {
        $attr = BuildTag::mergeAttr($name, $value, $class, $style, $attr);
        BuildTag::render('input', $attr);
    }
}

class BuildTag
{
    public static function mergeCss($class, $style = null, $attr = null)
    {
        return BuildTag::mergeAttr(null, null, $class, $style, $attr);
    }

    public static function mergeAttr($name, $value = null, $class = null, $style = null, $attr = null)
    {
        if(! $attr) {
            $attr = [];
        }

        if($name) {
            $attr['id'] = $attr['name'] = $name;
        }

        if($value) {
            $attr['value'] = $value;
        }

        if($class) {
            $attr['class'] = $class;
        }

        if($style) {
            $attr['style'] = $style;
        }
        return $attr;
    }


    public static function render($tagName, $attr = null, $content = null, $closure = false)
    {
        $html = '<';
        $html .=$tagName;

        if($attr){
            foreach ($attr as $key => $value) {
                $html .= ' ' . $key .'="' . $value . '"';
            }
        }

        if ($closure){
            $html .= '>';
            if ($content){
                $html .= $content;
            }
            $html .= '</' . $tagName . '>';
        } else {
            $html .= ' />';
        }
        echo $html;
    }
}

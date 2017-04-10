<?php
if (! function_exists('link_a')) {
    function link_a($text, $url, $class = null, $style = null, $attr = null)
    {
        $attr = BuildTag::mergeCss($class, $style, $attr);
        $attr['href'] = url($url);
        return BuildTag::render('a', $attr, $text, true);
    }
}

if (! function_exists('textbox')) {
    function textbox($name, $value, $class = null, $style = null, $attr = null)
    {
        $attr = BuildTag::mergeAttr($name, $value, $class, $style, $attr);
        $attr['type'] = 'text';
        return BuildTag::render('input', $attr);
    }
}

if (! function_exists('radio')) {
    function radio($name, $value, $checked = false, $class = null, $style = null, $attr = null)
    {
        $attr = BuildTag::mergeAttr($name, $value, $class, $style, $attr);
        $attr['type'] = 'radio';
        if($checked) {
            $attr['checked'] = 'checked';
        }
        return BuildTag::render('input', $attr);
    }
}

if (! function_exists('checkbox')) {
    function checkbox($name, $value, $checked = false, $class = null, $style = null, $attr = null)
    {
        $attr = BuildTag::mergeAttr($name, $value, $class, $style, $attr);
        $attr['type'] = 'checkbox';
        if($checked) {
            $attr['checked'] = 'checked';
        }
        return BuildTag::render('input', $attr);
    }
}

if (! function_exists('hidden')) {
    function hidden($name, $value, $class = null, $style = null, $attr = null)
    {
        $attr = BuildTag::mergeAttr($name, $value, $class, $style, $attr);
        $attr['type'] = 'hidden';
        return BuildTag::render('input', $attr);
    }
}

if (! function_exists('drop_down_list')) {
    function drop_down_list($name, $options = null, $class = null, $style = null, $attr = null)
    {
        $attr = BuildTag::mergeAttr($name, null, $class, $style, $attr);
        $optionHtml = '';
        if($options) {
            if(is_array($options)) {
                foreach ($options as $item) {
                    $optionHtml .= '<option value="'. $item['value'] .'"' . (isset($item['selected']) ? ' selected=selected' : '') . '>' . $item['text'] . '</option>';
                }
            }
            elseif (is_string($options)) {
                $optionHtml .= $options;
            }
        }
        return BuildTag::render('select', $attr, $optionHtml, true);
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
        return $html;
    }
}

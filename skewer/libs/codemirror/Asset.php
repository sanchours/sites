<?php
namespace skewer\libs\codemirror;

use yii\web\AssetBundle;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/libs/codemirror/web/';
    public $css = [
        'codemirror.css',
        'addon/hint/show-hint.css',
        'addon/display/fullscreen.css'
    ];
    public $js = [
        'codemirror.js',
        'mode/xml/xml.js',
        'mode/javascript/javascript.js',
        'mode/css/css.js',
        'mode/htmlmixed/htmlmixed.js',
        'addon/display/fullscreen.js',

        'addon/hint/show-hint.js',
        'addon/hint/css-hint.js',
        'addon/hint/javascript-hint.js',

        'addon/emmet/emmet.min.js'
    ];
    public $depends = [
    ];
}

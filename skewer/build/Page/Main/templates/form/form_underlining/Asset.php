<?php

namespace skewer\build\Page\Main\templates\form\form_underlining;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Main/templates/form/form_underlining/web/';

    public $css = [
        'css/form_underlining.css',
    ];
    public $cssOptions = [
        'position' => View::POS_HEAD,
    ];

//    public $js = [
//
//    ];
}

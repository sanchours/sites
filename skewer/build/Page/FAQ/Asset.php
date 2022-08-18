<?php

namespace skewer\build\Page\FAQ;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/FAQ/web/';

    public $css = [
        'css/faq.css',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];

    public function init()
    {
        parent::init();
    }
}

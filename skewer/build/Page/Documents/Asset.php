<?php

namespace skewer\build\Page\Documents;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/GuestBook/web/';

    public $css = [
        'css/guestbook.css',
        'css/guestbook-theme-quote.css',
        'css/guestbook-theme-border.css',
        'css/guestbook-theme-shadow.css',
        'css/guestbook-theme-car-bg.css',
        'css/guestbook-theme-car-border.css',
        'css/guestbook-theme-car-single.css',
    ];

    public $js = [
        'js/guestbook.init.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];

    public $depends = [
        'skewer\build\Page\Forms\Asset',
        'skewer\libs\owlcarousel\Asset',
        '\skewer\components\rating\Asset',
    ];

    public function init()
    {
        parent::init();
    }
}

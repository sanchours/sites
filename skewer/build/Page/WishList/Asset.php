<?php

namespace skewer\build\Page\WishList;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/WishList/web/';

    public $js = [
        'js/wish.js',
    ];

    public $css = [
        'css/wishlist.css',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}

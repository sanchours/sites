<?php
namespace skewer\libs\justifiedGallery;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle{

    public $sourcePath = '@vendor/skewer_team/justified-gallery/dist/';

    public $css = [
        'css/justifiedGallery.css'
    ];

    public $js = [
        'js/jquery.justifiedGallery.js'
    ];

    public $jsOptions = [
        'position'=>View::POS_HEAD
    ];

}
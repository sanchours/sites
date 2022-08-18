<?php
namespace skewer\libs\fancybox;


use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle{

    public $sourcePath = '@skewer/libs/fancybox/web/';

    public $css = [
        'custom.css'
    ];

    public $jsOptions = [
        'position'=>View::POS_HEAD
    ];

    public $depends = [
        'newerton\fancybox3\FancyBoxAsset'
    ];

    public function init(){

        $this->js[] = 'fancybox-' . \Yii::$app->language . '.js';

        parent::init();
    }
}
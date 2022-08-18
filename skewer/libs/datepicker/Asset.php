<?php
namespace skewer\libs\datepicker;


use yii\web\AssetBundle;
use yii\web\View;

class Asset extends  AssetBundle{

    public $sourcePath = '@skewer/libs/datepicker/web/';
    public $css = [
        'css/jquery-ui-datepicker.min.css',
        'css/jquery-ui-datepicker-theme.css',
    ];
    public $js = [
        'js/jquery-ui-datepicker.js',
    ];

    public $jsOptions = [
        'position'=>View::POS_HEAD
    ];

    public $depends = [
        'yii\web\JqueryAsset'
    ];

    public function init(){

        $this->js[] = 'js/jquery-ui-datepicker-' . \Yii::$app->language . '.js';

        parent::init();

    }
}
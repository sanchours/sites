<?php

namespace skewer\libs\ext_js;


class Asset extends \yii\web\AssetBundle
{
    public $sourcePath = '@skewer/libs/ext_js/web/';

    public $css = [
        'css/ext-all.css',
        'css/custom.css',
        'js/ux/css/CheckHeader.css',
    ];

    public $js = [
        'js/fixDate.js',
        'js/ext-all.js',
        //'js/ext-all-debug.js', // for debug ExtJS file
        'js/ie-detect.js',
    ];

    public function init() {
        $this->js[] = 'js/ext-'.\Yii::$app->i18n->getTranslateLanguage().'.js';
        parent::init();
    }
}

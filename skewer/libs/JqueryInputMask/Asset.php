<?php

namespace skewer\libs\JqueryInputMask;

use yii\web\AssetBundle;

class Asset extends AssetBundle{

    public $sourcePath = '@vendor/robinherbots/jquery.inputmask/';

    public $js = [
        'dist/min/jquery.inputmask.bundle.min.js',
    ];


    public $depends = [
        'yii\web\JqueryAsset',
    ];

}
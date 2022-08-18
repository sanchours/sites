<?php

namespace skewer\components\forms\components\protection;

use yii\web\AssetBundle;
use yii\web\View;

class AssetBlockJs extends AssetBundle
{
    public $sourcePath = '@skewer/components/forms/components/protection/web/';

    public $js = [
        'js' => 'js/blockJs.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];
}

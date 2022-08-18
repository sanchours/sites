<?php

namespace skewer\modules\rest\docs;

use yii\web\AssetBundle;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/modules/rest/docs/web';

    public $css = [
        'css/base.css',
    ];

    public $js = [
        'js/init.js',
    ];
}

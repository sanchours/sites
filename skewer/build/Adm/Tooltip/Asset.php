<?php

namespace skewer\build\Adm\Tooltip;

use yii\web\AssetBundle;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Adm/Tooltip/web';

    public $js = [
        'Tooltip.js',
    ];
}

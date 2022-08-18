<?php

namespace skewer\build\Design\Frame;

use yii\web\AssetBundle;

class AssetEditor extends AssetBundle
{
    public $sourcePath = '@skewer/build/Design/Frame/web';
    public $css = [
        'css/frame.css',
    ];
    public $js = [
        'js/admin.js',
    ];
    public $depends = [
        'skewer\components\ext\Asset',
        'skewer\libs\codemirror\Asset',
        'skewer\libs\CKEditor\Asset',
    ];
}

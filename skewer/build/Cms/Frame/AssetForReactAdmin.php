<?php

namespace skewer\build\Cms\Frame;

use yii\web\AssetBundle;

/**
 * Class AssetForReactAdmin.
 */
class AssetForReactAdmin extends AssetBundle
{
    public $sourcePath = '@skewer/build/Cms/Frame/web';
    public $css = [
        'css/admin.css',
    ];
    public $js = [
//        'js/admin.js'
    ];
    public $depends = [
//        'skewer\components\ext\Asset',
        'skewer\libs\codemirror\Asset',
        'skewer\libs\CKEditor\AssetForReactAdmin',
    ];
}

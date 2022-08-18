<?php

namespace skewer\components\ext;

use yii\web\AssetBundle;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/components/ext/web';
    public $css = [
        'css/admin.css',
    ];
    public $depends = [
        'skewer\libs\ext_js\Asset',
    ];
}

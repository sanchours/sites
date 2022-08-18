<?php

namespace skewer\modules\rest\docs;

use yii\web\AssetBundle;

class SwaggerUIAsset extends AssetBundle
{
    public $sourcePath = '@vendor/swagger-api/swagger-ui/dist';

    public $css = [
        'swagger-ui.css',
    ];

    public $js = [
        'swagger-ui.js',
        'swagger-ui-bundle.js',
        'swagger-ui-standalone-preset.js',
    ];
}

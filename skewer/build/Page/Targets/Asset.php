<?php

namespace skewer\build\Page\Targets;

use skewer\components\targets\Api;
use yii\helpers\Url;
use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Targets/web/';

    public $css = [];

    public $js = [
        'js/targets.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];

    public $depends = [];

    public function publish($am)
    {
        if ($this->sourcePath !== null && !isset($this->basePath, $this->baseUrl)) {
            list($this->basePath, $this->baseUrl) = $am->publish($this->sourcePath, $this->publishOptions);
        }

        if (isset($this->basePath, $this->baseUrl) && ($converter = $am->getConverter()) !== null) {
            foreach ($this->js as $i => $js) {
                if (Url::isRelative($js)) {
                    $this->js[$i] = Api::convert($js, $this->basePath);
                }
            }
        }
    }
}

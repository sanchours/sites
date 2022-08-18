<?php
/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 22.01.2016
 * Time: 12:03.
 */

namespace skewer\build\Page\Main;

use yii\web\AssetBundle;

class BaseAsset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/Main/web/';

    public $js = [
        'js/adaptive.js',
        'js/ecommerce.js',
        'js/accordion.js',
        'js/pageInit.js',
    ];

    public $depends = [
        'skewer\libs\fancybox\Asset',
    ];

    public function init()
    {
        parent::init();
    }
}

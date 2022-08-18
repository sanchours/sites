<?php

namespace skewer\build\Page\CatalogViewer;

use skewer\build\Page\WishList;
use yii\web\AssetBundle;
use yii\web\View;

class AssetDetail extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/CatalogViewer/web/';

    public $css = [
        'css/owl-carousel-catalogtheme.css',
        'css/owl-carousel-relatedtheme.css',
    ];

    public $js = [
        'js/detail.js',
        'js/fotorama.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];

    public $depends = [
        'skewer\build\Page\CatalogViewer\Asset',
        'skewer\ext\fotorama\Asset',
        'skewer\libs\owlcarousel\Asset',
    ];

    public function init()
    {
        if (WishList\WishList::isModuleOn()) {
            $this->depends[] = WishList\Asset::className();
        }

        parent::init();
    }
}

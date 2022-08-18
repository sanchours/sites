<?php

namespace skewer\build\Page\CatalogViewer;

use skewer\base\site\Type;
use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/CatalogViewer/web/';

    public $css = [
        'css/catalog.css',
        'css/tab.css',
        'css/catalog__param.css',
        'css/owl-carousel-collectiontheme.css',
        'css/owl-carousel-maintheme.css',
        'css/catalog_media.css',
    ];

    public $js = [
        'js/catalog.js',
        'js/fotorama.js',
        'js/rating.js',
        'js/quick_view.js',
    ];

    public $jsOptions = [
        'position' => View::POS_HEAD,
    ];

    public $depends = [];

    public function init()
    {
        $this->depends[] = 'skewer\ext\fotorama\Asset';

        if (Type::isShop()) {
            $this->depends[] = 'skewer\build\Page\Cart\Asset';
        }

        $this->depends[] = 'skewer\libs\owlcarousel\Asset';
        $this->depends[] = 'skewer\ext\jqueryui\Asset';
        $this->depends[] = 'skewer\components\rating\Asset';

        parent::init();
    }
}

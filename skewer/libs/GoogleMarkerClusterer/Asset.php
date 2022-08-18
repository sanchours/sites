<?php
namespace skewer\libs\GoogleMarkerClusterer;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle{

    public $sourcePath = '@skewer/libs/GoogleMarkerClusterer/web/';


    public $js = [
        'js/markerclusterer.js'
    ];

    public $jsOptions = [
        'position'=>View::POS_END
    ];


}
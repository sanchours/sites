<?php

namespace skewer\build\Page\BlindVersion;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle
{
    public $sourcePath = '@skewer/build/Page/BlindVersion/web/';

    public $js = [
        'js/blind.js',
    ];

    public $css = [
    ];

    public $cssOptions = [
        'position' => View::POS_END,
    ];

    public function init()
    {
        if (Api::isBlindVersion()) {
            array_push($this->css, 'css/sp_custom.css');

            switch (Api::getBlindParam('svColor')) {
                case 'black':
                    array_push($this->css, 'css/sp_black.css');
                    break;
                case 'blue':
                    array_push($this->css, 'css/sp_blue.css');
                    break;
                case 'white':
                    array_push($this->css, 'css/sp_white.css');
                    break;
            }
        }

        parent::init();
    }
}

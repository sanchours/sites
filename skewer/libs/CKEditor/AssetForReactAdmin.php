<?php
namespace skewer\libs\CKEditor;

use yii\web\AssetBundle;

/**
 * Class Asset
 * @package skewer\libs\CKEditor
 * #react
 */
class AssetForReactAdmin extends AssetBundle
{
    public $sourcePath = '@skewer/libs/CKEditor/web/';
    public $css = [
        'css/only_wys.css',
    ];
    public $js = [
        'ckeditor.js',
//        'ckInit.js'
    ];
    public $depends = [
        'skewer\components\content_generator\Asset'
    ];
}

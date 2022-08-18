<?php
/**
 * This is the template for generating a install class file.
 *
 * @var yii\web\View
 * @var $generator \skewer\generators\page_module\Generator
 */
    $className = $generator->moduleName;
    $fullClassName = $generator->getModulePath();
    $ns = 'skewer\build\Page\\' . $className;
    $nameDict = $generator->nameDict;

echo "<?php\n";
?>

namespace <?= $ns; ?>;

use yii\web\AssetBundle;
use yii\web\View;

class Asset extends AssetBundle {

    //public $sourcePath = '@skewer/build/Page/<?= $className; ?>/web/';

    public $css = [];

    public $js = [];

    public $jsOptions = [
        'position'=>View::POS_HEAD
    ];

}

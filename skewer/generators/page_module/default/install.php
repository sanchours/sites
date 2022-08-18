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

use skewer\components\config\InstallPrototype;
use skewer\components\catalog\Dict;


/**
 * Class Install
 * @package skewer\build\Page\<?= $className; ?>
 */
class Install extends InstallPrototype {

    public function init() {
        return true;
    }

    public function install() {
        Dict::setBanDelDict('<?= $nameDict; ?>');
        return true;
    }

    public function uninstall() {
        Dict::enableDelDict('<?= $nameDict; ?>');
        return true;
    }

}

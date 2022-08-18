<?php
/**
 * This is the template for generating a install class file.
 *
 * @var yii\web\View
 * @var $generator \skewer\generators\tool_module\Generator
 */
    $moduleName = $generator->moduleName;
    $fullClassName = $generator->getModulePath();
    $ns = 'skewer\build\Tool\\' . $moduleName;

echo "<?php\n";
?>

namespace <?= $ns; ?>;
use skewer\components\config\InstallPrototype;

/**
 * Class Install
 * @package skewer\build\Tool\<?= $moduleName; ?>
 */
class Install extends InstallPrototype {

    public function init() {
        return true;
    }// func

    public function install() {

        return true;
    }// func

    public function uninstall() {

        return true;
    }// func

}// class

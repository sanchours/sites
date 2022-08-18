<?php
/**
 * This is the template for generating a install class file.
 *
 * @var yii\web\View
 * @var $generator \skewer\generators\adm_module\Generator
 */
    $moduleName = $generator->moduleName;
    $fullClassName = $generator->getModulePath();
    $ns = 'skewer\build\Adm\\' . $moduleName;

echo "<?php\n";
?>

namespace <?= $ns; ?>;
use skewer\components\config\InstallPrototype;

/**
 * Class Install
 * @package skewer\build\Adm\<?= $moduleName; ?>
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

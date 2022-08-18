<?php
/**
 * This is the template for generating config file.
 *
 * @var yii\web\View
 * @var $generator \skewer\generators\page_ar_module\Generator
 */
$className = $generator->moduleName;

$fullClassName = $generator->getModulePath();
$ns = 'skewer\build\Page\\' . $className;
$sLayer = skewer\base\site\Layer::PAGE;

echo "<?php\n";
?>
use skewer\base\site\Layer;

$aConfig['name']     = "<?= $className; ?>";
$aConfig['version']  = '1.0';
$aConfig['title']    = "<?= $className; ?>";
$aConfig['description']  = 'Модуль из словаря системы';
$aConfig['revision'] = '0001';
$aConfig['layer']     = "<?= $sLayer; ?>";

return $aConfig;

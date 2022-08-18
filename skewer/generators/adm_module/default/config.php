<?php
/**
 * This is the template for generating config file.
 *
 * @var yii\web\View
 * @var $generator \skewer\generators\adm_module\Generator
 */
$moduleName = $generator->moduleName;
$moduleTitle = $generator->moduleTitle;
$moduleDescription = $generator->moduleDescription;
$sLayer = skewer\base\site\Layer::ADM;

echo "<?php\n";
?>
use skewer\base\site\Layer;

$aConfig['name']     = "<?= $moduleName; ?>";
$aConfig['version']  = '1.0';
$aConfig['title']    = "<?= $moduleTitle; ?>";
$aConfig['description']  = '<?=$moduleDescription; ?>';
$aConfig['revision'] = '0001';
$aConfig['layer']     = "<?= $sLayer; ?>";

return $aConfig;

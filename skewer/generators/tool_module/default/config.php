<?php
/**
 * This is the template for generating config file.
 *
 * @var yii\web\View
 * @var $generator \skewer\generators\tool_module\Generator
 */
$moduleName = $generator->moduleName;
$moduleTitle = $generator->moduleTitle;
$moduleDescription = $generator->moduleDescription;
$moduleGroup = $generator->moduleGroup;
$sLayer = skewer\base\site\Layer::TOOL;

echo "<?php\n";
?>
use skewer\base\site\Layer;

$aConfig['name']     = "<?= $moduleName; ?>";
$aConfig['version']  = '1.0';
$aConfig['title']    = "<?= $moduleTitle; ?>";
$aConfig['description']  = '<?=$moduleDescription; ?>';
$aConfig['revision'] = '0001';
$aConfig['layer']     = "<?= $sLayer; ?>";
$aConfig['group']     = "<?= $moduleGroup; ?>";

return $aConfig;

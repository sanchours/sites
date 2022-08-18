<?php
/**
 * This is the template for generating a install class file.
 *
 * @var yii\web\View
 * @var $generator \skewer\generators\page_ar_module\Generator
 */
    $moduleName = $generator->moduleName;
    $moduleTitle = $generator->moduleTitle;
    $aNameARs = $generator->aNameARs;
    $descARs = $generator->getNameColumnsARs();
    $fullClassName = $generator->getModulePath();
    $ns = 'skewer\build\Page\\' . $moduleName;

echo "<?php\n";
?>

$aLanguage = array();

<?php foreach ($descARs as $column):?>
$aLanguage['ru']['title_<?=$column; ?>'] = '<?=$column; ?>';
<?php endforeach; ?>

<?php foreach ($descARs as $column):?>
$aLanguage['en']['field_<?=$column; ?>'] = '<?=$column; ?>';
<?php endforeach; ?>

return $aLanguage;
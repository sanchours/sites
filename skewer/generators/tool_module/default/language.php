<?php
/**
 * This is the template for generating a install class file.
 *
 * @var yii\web\View
 * @var $generator \skewer\generators\tool_module\Generator
 */
    $moduleName = $generator->moduleName;
    $moduleTitle = $generator->moduleTitle;
    $aNameARs = $generator->aNameARs;
    $descARs = $generator->getNameColumnsARs();
    $fullClassName = $generator->getModulePath();
    $ns = 'skewer\build\Tool\\' . $moduleName;

echo "<?php\n";
?>

$aLanguage = array();

$aLanguage['ru']['<?=$moduleName; ?>.Tool.tab_name'] = '<?=$moduleTitle; ?>';
$aLanguage['ru']['error_row_not_found'] = "Запись [{0}] не найдена";
$aLanguage['ru']['error_sort'] = 'Ошибка! Неверно заданы параметры сортировки';
$aLanguage['ru']['list'] = 'Список <?=$generator->nameAR; ?>';
$aLanguage['ru']['new_item'] = 'Добавление элемента <?=$generator->nameAR; ?>';
$aLanguage['ru']['edit_item'] = 'Редактора элемента <?=$generator->nameAR; ?>: "{0}"';

<?php foreach ($aNameARs as $item):
    $lowerItem = mb_strtolower($item); ?>
$aLanguage['ru']['button_<?=$lowerItem; ?>'] = '<?=$item; ?>';
$aLanguage['ru']['list_<?=$lowerItem; ?>'] = 'Список <?=$item; ?>';
$aLanguage['ru']['new_item_<?=$lowerItem; ?>'] = 'Добавление элемента <?=$item; ?>';
$aLanguage['ru']['edit_item_<?=$lowerItem; ?>'] = 'Редактор элемента <?=$item; ?>: "{0}"';
<?php endforeach; ?>

<?php foreach ($descARs as $column):?>
$aLanguage['ru']['field_<?=$column; ?>'] = '<?=$column; ?>';
<?php endforeach; ?>


$aLanguage['en']['<?=$moduleName; ?>.Tool.tab_name'] = '<?=$moduleName; ?>';
$aLanguage['en']['error_row_not_found'] = "Row [{0}] not found";
$aLanguage['en']['error_sort'] = 'Error! Wrong sort parameters are specified';
$aLanguage['en']['list'] = '<?=$generator->nameAR; ?> list';
$aLanguage['en']['new_item'] = '<?=$generator->nameAR; ?> adding';
$aLanguage['en']['edit_item'] = '<?=$generator->nameAR; ?> edit: "{0}"';

<?php foreach ($aNameARs as $item):
    $lowerItem = mb_strtolower($item); ?>
$aLanguage['en']['button_<?=$lowerItem; ?>'] = '<?=$item; ?>';
$aLanguage['en']['list_<?=$lowerItem; ?>'] = '<?=$item; ?> list';
$aLanguage['en']['new_item_<?=$lowerItem; ?>'] = '<?=$item; ?> adding';
$aLanguage['en']['edit_item_<?=$lowerItem; ?>'] = '<?=$item; ?> edit: "{0}"';
<?php endforeach; ?>

<?php foreach ($descARs as $column):?>
$aLanguage['en']['field_<?=$column; ?>'] = '<?=$column; ?>';
<?php endforeach; ?>

return $aLanguage;
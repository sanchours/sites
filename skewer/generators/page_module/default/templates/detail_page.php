<?php
/**
 * This is the template for generating detail page file.
 *
 * @var \yii\web\View
 * @var $generator \skewer\generators\page_module\Generator
 */
use skewer\generators\page_module\Api;

$className = $generator->moduleName;
    $aDictField = Api::getArrayPrototypeView($generator->nameDict);
    echo "<?php\n";
?>
    /**
    * Structure: <?=$className . "\n"; ?>
    * @var array() $aNameField - наименования строк
    * @var int $id - not displayed
<?php foreach ($aDictField as $oField):?>
    <?=$oField->getComment(); ?>
<?php endforeach; ?>
    */
?>
<h1><?= '<?= '; ?> $title; ?></h1>


<?php foreach ($aDictField as $oField): ?>
<?php if (!in_array($oField->sName, Api::$aNotShow)): ?>
    <?= '<?php '; ?> if ($<?= $oField->sName; ?>): ?>
        <?= $oField->getCode() . "\n"; ?>
    <?= '<?php '; ?> endif; ?>
<?php endif; ?>
<?php endforeach; ?>

<p class="dict__linkback">
    <a rel="nofollow" href="#" onclick="history.go(-1);return false;">
        <?= '<?= '; ?> \Yii::t('page', 'back'); ?>
    </a>
</p>

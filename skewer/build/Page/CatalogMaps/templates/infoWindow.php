<?php
/**
 * @var \yii\base\View
 *  @var array $aGood
 *  @var bool $reedMore
 */
use yii\helpers\ArrayHelper;

?>
<?php foreach ($aGood['fields'] as $aField): ?>

    <?php if (ArrayHelper::getValue($aField, 'attrs.show_in_map') && ArrayHelper::getValue($aField, 'html_map')): ?>
        <div><?php if (ArrayHelper::getValue($aField, 'attrs.show_title_in_map')) :?><strong><?=ArrayHelper::getValue($aField, 'title'); ?>:</strong> <?endif; ?><?=ArrayHelper::getValue($aField, 'html_map'); ?></div>
    <?php endif; ?>
<?php endforeach; ?>

<?php if ($reedMore): ?>
    <a href="<?=$aGood['url']; ?>"><?=Yii::t('page', 'readmore'); ?></a>
<?php endif; ?>

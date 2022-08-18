<?php

/** @var $aUsageInCategoryViewer array */
/** @var $aUsageInDesignMode array */
?>
<?php if ($aUsageInDesignMode): ?>
    <u><?=Yii::t('fonts', 'design_params'); ?>:</u>
    <?php foreach ($aUsageInDesignMode as $item): ?>
        <p style="margin-left: 20px;"><?=$item; ?></p>
    <?php endforeach; ?>
<?php endif; ?>
<?php if ($aUsageInCategoryViewer): ?>
    <u><?=Yii::t('fonts', 'design_params_category_viewer'); ?>:</u>
    <?php foreach ($aUsageInCategoryViewer as $item): ?>
        <p style="margin-left: 20px;"><?=$item; ?></p>
    <?php endforeach; ?>
<?php endif; ?>

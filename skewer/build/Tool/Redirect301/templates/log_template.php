<?php

/**
 *  @var array aLogParams
 */
?>
<div style="margin-left: 30px;">
    <?php if (isset($aLogParams['status'])): ?>
        <p><?=Yii::t('redirect301', 'status'); ?>: <?=$aLogParams['status']; ?></p>
    <?php endif; ?>

    <?php if (isset($aLogParams['start'])): ?>
        <p><?=Yii::t('redirect301', 'start'); ?>: <?=$aLogParams['start']; ?></p>
    <?php endif; ?>

    <?php if (isset($aLogParams['finish'])):?>
        <p><?=Yii::t('redirect301', 'finish'); ?>: <?=$aLogParams['finish']; ?></p>
    <?endif; ?>

    <?php if (isset($aLogParams['newRecords'])):?>
        <p>Добавлено: <?=$aLogParams['newRecords']; ?> записей</p>
    <?endif; ?>

    <?php if (isset($aLogParams['linkFile'])):?>
        <p><a href="<?=$aLogParams['linkFile']; ?>">Ссылка на файл</a></p>
    <?php endif; ?>


</div>
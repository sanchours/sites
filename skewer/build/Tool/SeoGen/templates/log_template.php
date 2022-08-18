<?php

/**
 *  @var array aLogParams
 */
?>
<div style="margin-left: 30px;">
    <?php if (isset($aLogParams['status'])): ?>
        <p><?=Yii::t('seoGen', 'status'); ?>: <?=$aLogParams['status']; ?></p>
    <?php endif; ?>

    <?php if (isset($aLogParams['start'])): ?>
        <p><?=Yii::t('seoGen', 'start'); ?>: <?=$aLogParams['start']; ?></p>
    <?php endif; ?>

    <?php if (isset($aLogParams['finish'])):?>
        <p><?=Yii::t('seoGen', 'finish'); ?>: <?=$aLogParams['finish']; ?></p>
    <?endif; ?>

    <?php if (isset($aLogParams['newRecords'])):?>
        <p>Добавлено: <?=$aLogParams['newRecords']; ?> записей</p>
    <?endif; ?>

    <?php if (isset($aLogParams['updateRecords'])):?>
        <p>Обновлено: <?=$aLogParams['updateRecords']; ?> записей</p>
    <?endif; ?>

    <?php if (isset($aLogParams['type_operation']) and ($aLogParams['type_operation'] == 'export')): ?>

        <?php if (empty($aLogParams['bDataExist'])): ?>
            <p>Данных для экспорта в указанных разделах нет!</p>
        <?php elseif (isset($aLogParams['linkFile'])):?>
            <p><a href="<?=$aLogParams['linkFile']; ?>">Ссылка на файл</a></p>
        <?php endif; ?>

    <?php endif; ?>

    <?php if (!empty($aLogParams['create_section'])): ?>
        <hr>
        <div>
            <p><b><?=Yii::t('seoGen', 'create_section'); ?>:</b></p>
            <table>
                <?php foreach ($aLogParams['create_section'] as $item):?>
                    <tr>
                        <td><?=$item; ?></td>
                    </tr>
                <?php endforeach; ?>

            </table>
        </div>
    <?endif; ?>

    <?php if (!empty($aLogParams['error_list'])):?>
    <div style="margin-top: 15px;">
        <p><b>Ошибки:</b></p>
        <table>
            <?php foreach ($aLogParams['error_list'] as $error):?>
            <tr>
                <td><?=$error; ?></td>
            </tr>
            <?endforeach; ?>
        </table>
    </div>
    <?endif; ?>


    <?php if (!empty($aLogParams['notAdded'])):?>
        <div style="margin-top: 15px;">
            <p><b>Не добавленные разделы:</b></p>
            <table>
                <?php foreach ($aLogParams['notAdded'] as $section):?>
                    <tr>
                        <td><?=$section; ?></td>
                    </tr>
                <?endforeach; ?>
            </table>
        </div>
    <?php endif; ?>



    <?php if (!empty($aLogParams['notUpdated'])):?>
        <div style="margin-top: 15px;">
            <p><b>Не обновленные разделы:</b></p>
            <table>
                <?php foreach ($aLogParams['notUpdated'] as $section):?>
                    <tr>
                        <td><?=$section; ?></td>
                    </tr>
                <?endforeach; ?>
            </table>
        </div>
    <?php endif; ?>




</div>
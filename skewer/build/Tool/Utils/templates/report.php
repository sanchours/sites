<?php
/**
 * @var int
 * @var int $iRefreshCount
 * @var int $iTotalCount
 * @var int $iTotalCountError
 * @var string $sProgress
 */
?>
<style>

    .alert{
        margin: 5px 0px;
        padding: 10px 10px;
    }

    .alert .inner_container{
        font-size: 14px;
    }
    .alert .title{
        margin-bottom: 5px;
        font-size: 14px;
        font-weight: 700;
    }

    .error{
        background: #FF4E38;
        border-left: 5px solid #B93D30;
    }

</style>

<table style="width: 100%;">
    <tr>
        <td><b><?=Yii::t('utils', 'count_records_processed_for_iteration'); ?></b></td>
        <td><b><?=Yii::t('utils', 'total_count_records_processed'); ?></b></td>
        <td><b><?=Yii::t('utils', 'count_all_records'); ?></b></td>
        <td><b><?=Yii::t('utils', 'execution_status'); ?></b></td>
    </tr>
    <tr>
        <td><?=$iRefreshCountByIteration; ?></td>
        <td><?=$iRefreshCount; ?></td>
        <td><?=$iTotalCount; ?></td>
        <td><?=$sProgress; ?></td>
    </tr>
</table>
<?php if ($iTotalCountError): ?>
    <div class="alert error">
        <div class="title">
            <?=Yii::t('utils', 'error_update_status'); ?>
        </div>
        <div class="inner_container">
            <?=Yii::t('utils', 'error_update_description'); ?>
        </div>
    </div>
<?php endif; ?>
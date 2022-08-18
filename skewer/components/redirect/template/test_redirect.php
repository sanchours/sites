<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 14.07.2016
 * Time: 16:51.
 */?>
<?php if (count($items) > 0) { ?>
<table border="1">
    <tr>
        <td style="font-size: 20px; padding-left: 100px;padding-right: 100px"><?=Yii::t('redirect301', 'source_url'); ?></td>
        <td style="font-size: 20px; padding-left: 100px;padding-right: 100px"><?=Yii::t('redirect301', 'url_target'); ?></td>

    </tr>
    <?php  foreach ($items as $item) { ?>
        <tr>
            <td style="font-size: 12px; padding-left: 10px;padding-right: 10px"><?=$item['old']; ?></td>
            <td style="font-size: 12px; padding-left: 10px;padding-right: 10px"><?=$item['new']; ?></td>

        </tr>
    <?php } ?>
</table>
<?php } else { ?>
    <strong><?=Yii::t('redirect301', 'no_data_for_test'); ?></strong>
<?php } ?>

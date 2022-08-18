<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 14.07.2016
 * Time: 16:51.
 */?>
<?=$results; ?>
<br>
<?=$sLimitHasError; ?>
<?php if (count($log['items']) > 0) { ?>
    <table border="1">
        <tr>
            <td style="font-size: 20px; padding-left: 100px;padding-right: 100px">E-mail</td>
            <td style="font-size: 20px; padding-left: 100px;padding-right: 100px"><?=Yii::t('subscribe', 'import_status'); ?></td>
        </tr>
        <?php  foreach ($log['items'] as $key => $item) { ?>
            <tr>
                <td style="font-size: 12px; padding-left: 10px;padding-right: 10px"><?=$key; ?></td>
                <td style="font-size: 12px; padding-left: 10px;padding-right: 10px"><?=$item; ?></td>
            </tr>
        <?php } ?>
    </table>
<?php } ?>
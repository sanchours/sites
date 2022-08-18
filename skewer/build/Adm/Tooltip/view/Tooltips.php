<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 07.04.2017
 * Time: 12:51.
 */
?>
<div style="display:none">
    <?php foreach ($tooltips as $tooltip) {?>
        <div id="js_tooltip_<?=$tooltip['id']; ?>">
            <?=$tooltip['text']; ?>
        </div>
    <?php } ?>
</div>

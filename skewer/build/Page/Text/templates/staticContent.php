<?php
/**
 * @var array
 */
use skewer\components\design\Design;

?>


<?php if (!empty($source['show_val'])): ?>
    <div class="b-editor"<?php if (Design::modeIsActive()): ?> sktag="editor" skeditor="<?=$source['group']; ?>/source"<?php endif; ?>>
        <?=$source['show_val']; ?>
    </div>
<?php endif; ?>







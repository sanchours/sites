<?php

use skewer\components\design\Design;

?>
<?php if (isset($Languages) && $Languages): ?>
<div class="b-lang <?php if (Design::modeIsActive()): ?>g-ramaborder js-designDrag-right" sktag="modules.language"<?php endif; ?>">
    <?php if (Design::modeIsActive()): ?>
        <div class="b-desbtn"><span>Language</span><ins></ins></div>
    <?php endif; ?>
    <?php foreach ($Languages as $lang): ?>
        <div class="lang__item">
            <a class="lang__title <?php if (isset($lang['current']) && $lang['current']): ?>lang__title-on<?php endif; ?>"
               href="<?php if (isset($lang['current']) && $lang['current']): ?>javascript:return false;<?php else: echo '[' . $lang['main'] . ']'; endif; ?>">
                <?php if (is_file(WEBPATH . $lang['icon'])): ?>
                    <img alt="" src="<?= $lang['icon']; ?>" />
                <?php else: ?>
                    <span><?= $lang['icon']; ?></span>
                <?php endif; ?>
            </a>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
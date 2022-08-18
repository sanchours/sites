<?php

namespace skewer\build\Page\Main\templates\footer\foot_fashion;

use skewer\base\section\Page;
use skewer\components\design\Design;

/*
 * @var array $copyright
 * @var array $copyright_dev
 * @var array $contacts
 * @var array $footertext4
 * @var array $footertext5
 * @var string $bottomMenu
 */

Asset::register($this);

// var_dump( 'foot_fashion' );

?>

<div class="l-footerbox"<?= Design::write(' sktag="page.footer"'); ?>>
    <div class="footerbox__wrapper js_dnd_wraper">

        <div class="l-grid">
            <div class="grid__item1<?php if (Design::modeIsActive()): ?> g-ramaborder js-designDrag-<?= Design::get('page.footer.grid1', 'h_position'); ?>" sktag="page.footer.grid1" skeditor="copyright/source<?php endif; ?>">
                <?php if (Design::modeIsActive()): ?>
                    <div class="b-desbtn"><span>copyright</span><ins></ins></div>
                <?php endif; ?>
                <?= str_replace('[Year]', date('Y'), Page::getShowVal('copyright', 'source')); ?>
            </div>
            <div class="grid__item2 hide-on-tablet hide-on-mobile<?php if (Design::modeIsActive()): ?> g-ramaborder js-designDrag-<?= Design::get('page.footer.grid2', 'h_position'); ?>" sktag="page.footer.grid2" skeditor="counters/source<?php endif; ?>">
                <?php if (Design::modeIsActive()): ?>
                    <div class="b-desbtn"><span>counters</span><ins></ins></div>
                <?php endif; ?>
                <div class="b-counter">
                    <!--noindex--><?= Page::getShowVal('counters', 'source'); ?><!--/noindex-->
                </div>
            </div>
            <div class="grid__item3<?php if (Design::modeIsActive()): ?> g-ramaborder js-designDrag-<?= Design::get('page.footer.grid3', 'h_position'); ?>" sktag="page.footer.grid3" skeditor="contacts/source<?php endif; ?>">
                <?php if (Design::modeIsActive()): ?>
                    <div class="b-desbtn"><span>contacts</span><ins></ins></div>
                <?php endif; ?>
                <?= Page::getShowVal('contacts', 'source'); ?>
            </div>
            <div class="grid__item4 hide-on-mobile">
                <?= Page::getShowVal('footertext4', 'source'); ?>
            </div>
            <div class="grid__item5<?php if (Design::modeIsActive()): ?>" sktag="page.footer.grid5" skeditor="footertext5/source<?php endif; ?>">
                <?= Page::getShowVal('footertext5', 'source'); ?>
            </div>
            <div class="grid__item7<?php if (Design::modeIsActive()): ?> g-ramaborder js-designDrag-<?= Design::get('page.footer.grid7', 'h_position'); ?>" sktag="page.footer.grid7<?php endif; ?>">
                <?php if (Design::modeIsActive()): ?>
                    <div class="b-desbtn"><span>copyright-dev</span><ins></ins></div>
                <?php endif; ?>
                <?= str_replace('[Year]', date('Y'), Page::getShowVal('copyright_dev', 'source')); ?>
            </div>

        </div>
        <div class="footerbox__left"></div>
        <div class="footerbox__right"></div>
    </div>
</div>


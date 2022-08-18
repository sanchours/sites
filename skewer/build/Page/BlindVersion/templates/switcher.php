<?php
/**
 * @var bool
 * @var bool $blind_mode_off
 */
?>
<div class="b-blind hide-on-tablet hide-on-mobile">
    <!--                <div class="blind__img">-->
    <!--                    <img src="/images/head-blind.png" alt="" />-->
    <!--                </div>-->
    <div class="blind__content">
        <?php if ($blind_mode_on): ?>
            <a href="?display_mode=simple_mode"><span class="blind__text-2">Обычная версия</span></a>
        <?php endif; ?>
        <?php if ($blind_mode_off): ?>
            <a href="?display_mode=blind_mode"><span class="blind__text-1">Версия для слабовидящих</span></a>
        <?php endif; ?>
    </div>

</div>
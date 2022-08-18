<?php

/**
 * Шаблон вывода заны с тремя колонками.
 *
 * @var array контент центральной колонки
 * @var array $right контент правой колонки
 * @var array $left контент левой колонки
 * @var array $layoutName имя зоны вывода
 */
use skewer\components\design\Design;

/** @var bool $showRight Флаг отображения правой колонки */
$showRight = (isset($right) && $right);
$showLeft = (isset($left) && $left);

?>

<div class="l-main l-main--<?= $layoutName; ?>">
    <div class="main__wrapper">

        <div class="l-column <?php if (!$showRight): ?>l-column--lc<?php endif; ?> <?php if (!$showLeft): ?>l-column--cr<?php endif; ?>">
            <div class="column__center">
                <div class="column__center-indent"<?= Design::write(' sklayout="content"'); ?>>
                    <?= (isset($content)) ? $content : ''; ?>
                </div>
            </div>
            <?php if ($showLeft):?>
                <div class="column__left"<?= Design::write(' sklayout="left"'); ?>>
                    <div class="column__left-indent">
                        <?= $left; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($showRight): ?>
                <div class="column__right"<?= Design::write(' sklayout="right"'); ?>>
                    <div class="column__right-indent"><?= $right; ?></div>
                </div>
            <?php endif; ?>
            <div class="column__center-bg">
                <div class="column__center-inside"></div>
            </div>
            <?php if (isset($left)): ?>
                <div class="column__left-bg">
                    <div class="column__left-inside"></div>
                </div>
            <?php endif; ?>
            <?php if ($showRight): ?>
                <div class="column__right-bg">
                    <div class="column__right-inside"></div>
                </div>
            <?php endif; ?>
        </div>
        <div class="main__left"></div>
        <div class="main__right"></div>
    </div>
</div>

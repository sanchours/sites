<?php

/**
 * Шаблон вывода заны с единственной колонкой.
 *
 * @var array контент центральной колонки
 * @var array $layoutName имя зоны вывода
 */
?>

<div class="l-main l-main--<?= $layoutName; ?>">
    <div class="main__wrapper">

        <?= (isset($content)) ? $content : ''; ?>

        <div class="main__left"></div>
        <div class="main__right"></div>
    </div>
</div>

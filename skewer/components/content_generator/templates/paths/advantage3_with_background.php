<?php

use skewer\components\content_generator\Asset;

?>

<div class="gc-advantage3-with-bg">
    <div class="advantage__wrapper">
        <div class="advantage3-with-bg__flex-container">
            <div class="advantage3-with-bg__item">
                <div class="advantage3-with-bg__imgbox">
                    <img src="<?= Asset::getAssetImg('/img/cap10.png'); ?>" alt="">
                </div>
                <div class="advantage3-with-bg__title">Преимущество 1</div>
                <div class="advantage3-with-bg__text">Lorem ipsum dolor sit amet consectetur</div>
            </div>
            <div class="advantage3-with-bg__item">
                <div class="advantage3-with-bg__imgbox">
                    <img src="<?= Asset::getAssetImg('/img/cap10.png'); ?>" alt="">
                </div>
                <div class="advantage3-with-bg__title">Преимущество 2</div>
                <div class="advantage3-with-bg__text">Lorem ipsum dolor sit amet consectetur</div>
            </div>
            <div class="advantage3-with-bg__item">
                <div class="advantage3-with-bg__imgbox">
                    <img src="<?= Asset::getAssetImg('/img/cap10.png'); ?>" alt="">
                </div>
                <div class="advantage3-with-bg__title">Преимущество 3</div>
                <div class="advantage3-with-bg__text">Lorem ipsum dolor sit amet consectetur</div>
            </div>
        </div>
    </div>
    <div class="advantage3-with-bg__bgfon">
        <img src="<?= Asset::getAssetImg('/img/advantage.jpg'); ?>" alt="">
    </div>
</div>
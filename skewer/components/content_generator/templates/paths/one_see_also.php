<?php
use skewer\components\content_generator\Asset;

?>

<div class="gc-see-also1">
    <div class="b-title-h2">Заголовок H2</div>
    <div class="see-also1__content">
        <div class="see-also1__item">
            <div class="see-also1__imgbox">
                <img src="<?=Asset::getAssetImg('/img/cap14.jpg'); ?>" alt="">
            </div>
            <div class="see-also1__text">Смотри также раз</div>
        </div>
        <div class="see-also1__item">
            <div class="see-also1__imgbox">
                <img src="<?=Asset::getAssetImg('/img/cap14.jpg'); ?>" alt="">
            </div>
            <div class="see-also1__text">Смотри также два</div>
        </div>
        <div class="see-also1__item">
            <div class="see-also1__imgbox">
                <img src="<?=Asset::getAssetImg('/img/cap14.jpg'); ?>" alt="">
            </div>
            <div class="see-also1__text">Смотри также три</div>
        </div>
    </div>
</div>
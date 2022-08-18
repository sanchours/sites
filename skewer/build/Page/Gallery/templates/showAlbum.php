<?php

use skewer\build\Page\Gallery\Asset;
use skewer\components\design\Design;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/*
 * @var $_objectId
 * @var $description
 * @var $images
 * @var $openAlbum
 * @var $aAlbums
 * @var $protect
 * @var $transitionEffect
 */
Asset::register(Yii::$app->view);
?>

<div class="b-galbox" <?php if (Design::modeIsActive()): ?>sktag="modules.gallery" sklabel="<?=$_objectId; ?>" <?php endif; ?> >
    <?php if (!empty($description)): ?>
        <div class="galbox__contentbox"><?= Html::encode($description); ?></div>
    <?php endif; ?>

    <?php if (!empty($images)): ?>
        <div class="js-get-data" data-fancybox-protect="<?= $protect; ?>" data-fancybox-transition-effect="<?= $transitionEffect; ?>"></div>
        <?php foreach ($images as $aImage):?>
            <?php if ($openAlbum) {
    $sGalId = 1;
} else {
    $sGalId = $aImage['album_id'];
} ?>
            <div class="galbox__item">
                <div class="galbox__pic js-fancybox-data">
                    <a data-fancybox="<?=$sGalId; ?>" data-fancybox-group="gallery" class="js-gallery_resize" href="<?=ArrayHelper::getValue($aImage, 'images_data.med.file', ''); ?>" title="<?=Html::encode(ArrayHelper::getValue($aImage, 'title', '')); ?> <?=Html::encode(ArrayHelper::getValue($aImage, 'description', '')); ?>">
                        <img src="<?=ArrayHelper::getValue($aImage, 'images_data.preview.file'); ?>" alt="<?=Html::encode(ArrayHelper::getValue($aImage, 'alt_title', '')); ?>">
                    </a>
                </div>
                <?php if (!empty($aImage['title'])): ?>
                    <div class="galbox__title hide-on-mobile">
                        <?=Html::encode(ArrayHelper::getValue($aImage, 'title', '')); ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

    <?php else: ?>
        <p></p>
    <?php endif; ?>



    <div class="g-clear"></div>

    <?php if (!$openAlbum):?>
        <p class="galbox__linkback"><a href="javascript: history.go(-1);" rel="nofollow"><?= \Yii::t('page', 'back'); ?></a></p>
    <?php endif; ?>
</div>

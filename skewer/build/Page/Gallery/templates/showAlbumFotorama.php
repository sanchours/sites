<?php

use skewer\build\Page\Gallery\AssetFotorama;
use skewer\components\design\Design;
use skewer\components\gallery\Album;
use skewer\components\gallery\Format;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

/*
 * @var $_objectId
 * @var $description
 * @var $images
 * @var $openAlbum
 * @var $aAlbums
 */
AssetFotorama::register(\Yii::$app->view);

?>

<div class="b-galbox b-galbox-fotorama"  <?php if (Design::modeIsActive()): ?>sktag="modules.gallery" sklabel="<?=$_objectId; ?>" <?endif; ?> >
    <?php if (!empty($description)): ?>
        <div class="galbox__contentbox"><?= Html::encode($description); ?></div>
    <?endif; ?>

    <?php if (!empty($images)): ?>

        <?php
        list($iMaxWidth, $iMaxHeight) = Album::getDimensions4Fotorama($aAlbums, 'med', $images);
        list($iThumbWidth, $iThumbHeight) = Album::getDimensions4Fotorama($aAlbums, 'mini', $images);

        if (!$iThumbWidth || !$iThumbHeight) {
            if (is_array($aAlbums)) {
                $iAlbumId = array_shift($aAlbums);
            } else {
                $iAlbumId = $aAlbums;
            }
            if (($iProfileId = Album::getProfileId($iAlbumId)) !== false) {
                if ($aFormat = Format::getByName('mini', $iProfileId)) {
                    $iThumbWidth = (int) ArrayHelper::getValue($aFormat, '0.width', 0);
                    $iThumbHeight = (int) ArrayHelper::getValue($aFormat, '0.height', 0);
                }
            }
        }

        ?>

        <div class="js-fotorama"  <?php if ($iMaxWidth): ?>data-max-width="<?=$iMaxWidth; ?>px" <?php endif; ?><?php if ($iThumbHeight):?>data-thumbheight="<?=$iThumbHeight; ?>"<?endif; ?> <?php if ($iThumbWidth):?>data-thumbwidth="<?=$iThumbWidth; ?>"<?endif; ?>  >
            <?php foreach ($images as $aImage):?>

                <?php if (($sBigPath = ArrayHelper::getValue($aImage->images_data, 'med.file', ''))): ?>
                    <a data-img="<?= $sBigPath; ?>"
                       data-thumb="<?= ArrayHelper::getValue($aImage->images_data, 'mini.file', ''); ?>"
                       href="<?= $sBigPath; ?>"
                       alt="<?=Html::encode($aImage['alt_title']); ?>"
                       title="<?php if (!empty($aImage['title'])):?><?=Html::encode($aImage['title']); ?><?endif; ?><?php if (!empty($aImage['description'])):?><?=Html::encode($aImage['description']); ?><?endif; ?>" >
                    </a>
                <?endif; ?>

            <?php endforeach; ?>
        </div>

    <?php else: ?>
        <p></p>
    <?endif; ?>



    <div class="g-clear"></div>

    <?php if (!$openAlbum):?>
        <p class="galbox__linkback"><a href="javascript: history.go(-1);" rel="nofollow"><?= \Yii::t('page', 'back'); ?></a></p>
    <?php endif; ?>
</div>

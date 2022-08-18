<!--gallery plugin used http://miromannino.github.io/Justified-Gallery/-->
<?php

use skewer\build\Page\Gallery\AssetJustifiedGallery;
use skewer\components\design\Design;
use yii\helpers\Html;

/*
 * @var $_objectId
 * @var $description
 * @var $images
 * @var $openAlbum
 * @var $aAlbums
 * @var $protect
 * @var $transitionEffect
 * @var string $justifiedGalleryConfig
 * @var string $AlbumAlias
 */

AssetJustifiedGallery::register(\Yii::$app->view);
?>


<div class="b-galbox b-galbox--inline" <?php if (Design::modeIsActive()): ?>sktag="modules.gallery" sklabel="<?=$_objectId; ?>" <?endif; ?> >
    <?php if (!empty($description)): ?>
        <div class="galbox__contentbox"><?= Html::encode($description); ?></div>
    <?endif; ?>

    <?php if (!empty($images)): ?>

        <div class="galbox__items">
            <div class="js-gallery-tile js-get-data justified-gallery" data-config='<?=$justifiedGalleryConfig; ?>' data-all_images_loaded="<?=$bAllImagesLoaded; ?>" data-albumalias="<?=$AlbumAlias; ?>">
            <?= \Yii::$app->getView()->renderPhpFile(
    __DIR__ . DIRECTORY_SEPARATOR . 'listImages.php',
    ['images' => $images, 'openAlbum' => $openAlbum]
); ?>
            </div>
            <div class="js_anchor_4justifiedGallery"></div>
        </div>

    <?php else: ?>
        <p></p>
    <?endif; ?>



    <div class="g-clear"></div>

    <?php if (!$openAlbum):?>
        <p class="galbox__linkback"><a href="javascript: history.go(-1);" rel="nofollow"><?= \Yii::t('page', 'back'); ?></a></p>
    <?php endif; ?>
</div>

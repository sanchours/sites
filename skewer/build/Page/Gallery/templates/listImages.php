<?php
/** @var $images */
/** @var $openAlbum */
use skewer\build\Adm\Gallery\Api;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

?>
<?php foreach ($images as $aImage):?>
    <?php
    $sVerticalPhoto = ArrayHelper::getValue($aImage, 'images_data.preview_ver.file');
    if ($sVerticalPhoto && file_exists(WEBPATH . $sVerticalPhoto)) {
        $sSelectedFormat = 'preview_ver';
        $sSrc = $sVerticalPhoto;
    } else {
        $sSrc = ArrayHelper::getValue($aImage, 'images_data.med.file');
        $sSelectedFormat = 'med';
    }
    ?>
    <?php if ($openAlbum) {
        $sGalId = 1;
    } else {
        $sGalId = $aImage['album_id'];
    } ?>
    <a data-fancybox="<?=$sGalId; ?>" data-fancybox-group="gallery" class="js-gallery-link js-gallery_resize" href="<?=ArrayHelper::getValue($aImage, 'images_data.med.file', ''); ?>" title="<?=Html::encode(ArrayHelper::getValue($aImage, 'title', '')); ?><?=Html::encode(ArrayHelper::getValue($aImage, 'description', '')); ?>">
        <img data-images='<?= json_encode(Api::getFormats4GalleryTileByPhoto($aImage)); ?>' class="js-gallery-pic"
             src="<?=$sSrc; ?>"
             alt="<?= Html::encode(ArrayHelper::getValue($aImage, 'alt_title', '')); ?>"
             height="<?= ArrayHelper::getValue($aImage, "images_data.{$sSelectedFormat}.height"); ?>"
             width="<?= ArrayHelper::getValue($aImage, "images_data.{$sSelectedFormat}.width"); ?>">
    </a>
<?php endforeach; ?>
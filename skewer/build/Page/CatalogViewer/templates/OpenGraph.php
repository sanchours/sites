<?php

use skewer\base\section\Parameters;
use skewer\components\design\Design;
use skewer\components\gallery\Album;
use skewer\components\seo;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

/*
 * @var array $aGood
 * @var  \skewer\components\seo\SeoPrototype $oSeoComponent
 */

?>


<?php
    $oSeoComponent->initSeoData();
    if (!$oSeoComponent || !($sOgPhoto = Album::getFirstActiveImage($oSeoComponent->seo_gallery, 'format_openGraph'))) {
        if (!empty($aGood['fields']['gallery']['first_img']['images_data']['big']['file'])) {
            $sOgPhoto = $aGood['fields']['gallery']['first_img']['images_data']['big']['file'];
        } else {
            $iGalleryId = (int) Parameters::getValByName(Yii::$app->sections->root(), seo\Api::GROUP_PARAM_MICRODATA, 'photoOpenGraph');
            $sOgPhoto = Album::getFirstActiveImage($iGalleryId, 'format_openGraph');

            if (!$sOgPhoto) {
                $sOgPhoto = Design::getLogo();
            }
        }
    }

    if (!empty($oSeoComponent->description)) {
        $sOgDescription = $oSeoComponent->description;
    } else {
        $sOgDescription = ArrayHelper::getValue($aGood, 'fields.announce.value', '');
        if (!$sOgDescription) {
            $sOgDescription = $oSeoComponent->parseField('description', ['sectionId' => $oSeoComponent->getSectionId()]);
        }
    }

    if (!empty($oSeoComponent->title)) {
        $sOgTitle = $oSeoComponent->title;
    } else {
        $sOgTitle = ArrayHelper::getValue($aGood, 'title', '');
        if (!$sOgTitle) {
            $sOgDescription = $oSeoComponent->parseField('title', ['sectionId' => $oSeoComponent->getSectionId()]);
        }
    }

    $aImageSize = @getimagesize(WEBPATH . $sOgPhoto);
    $iMaxLength = (int) Parameters::getValByName(Yii::$app->sections->root(), seo\Api::GROUP_PARAM_MICRODATA, 'sum_symbols');

?>

<meta property="og:type" content="product" />
<meta property="og:url" content="<?= ArrayHelper::getValue($aGood, 'canonical_url', ''); ?>" />
<meta property="og:title" content="<?= seo\Api::prepareRawString($sOgTitle); ?>" />
<meta property="og:description" content="<?= ($iMaxLength) ? StringHelper::truncate(seo\Api::prepareRawString($sOgDescription), $iMaxLength) : seo\Api::prepareRawString($sOgDescription); ?>" />
<meta property="og:image" content="<?=\skewer\base\site\Site::httpDomain() . $sOgPhoto; ?>" />
<?php if ($aImageSize) : ?>
    <meta property="og:image:width" content="<?= $aImageSize[0]; ?>" />
    <meta property="og:image:height" content="<?= $aImageSize[1]; ?>" />
<?php endif; ?>
<?php if ($sPrice = ArrayHelper::getValue($aGood, 'fields.price.value', '')) :?>
    <meta prefix="product: <?=WEBPROTOCOL; ?>ogp.me/ns/product#" property="product:price:amount" content="<?= $sPrice; ?>" />
    <meta prefix="product: <?=WEBPROTOCOL; ?>ogp.me/ns/product#" property="product:price:currency" content="<?= \skewer\base\SysVar::get('catalog.currency_type', ''); ?>" />
<?php endif; ?>
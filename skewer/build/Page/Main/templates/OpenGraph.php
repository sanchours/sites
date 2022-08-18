<?php

use skewer\base\section\Page;
use skewer\base\section\Parameters;
use skewer\base\site\Site;
use skewer\components\design\Design;
use skewer\components\gallery\Album;
use skewer\components\seo;
use yii\helpers\StringHelper;

/*
 * @var \skewer\base\section\models\TreeSection $oTree
 * @var seo\SeoPrototype $oSeoComponent
 */

?>

<?php

    if (!$sOgSiteName = Site::getSiteTitle()) {
        $sOgSiteName = Site::httpDomain();
    }

    $oSeoComponent->initSeoData();
    $sOgTitle = (!empty($oSeoComponent->title)) ? $oSeoComponent->title : $oTree->title;

    if (!empty($oSeoComponent->description)) {
        $sOgDescription = $oSeoComponent->description;
    } else {
        $sOgDescription = (Page::getShowVal('staticContent', 'source')) ? Page::getShowVal('staticContent', 'source') : '';
        if (!$sOgDescription) {
            $sOgDescription = $oSeoComponent->parseField('description', ['sectionId' => $oSeoComponent->getSectionId()]);
        }
    }

    if (!$oSeoComponent || !($sOgPhoto = Album::getFirstActiveImage($oSeoComponent->seo_gallery, 'format_openGraph'))) {
        $iGalleryId = (int) Parameters::getValByName(Yii::$app->sections->root(), seo\Api::GROUP_PARAM_MICRODATA, 'photoOpenGraph');
        $sOgPhoto = Album::getFirstActiveImage($iGalleryId, 'format_openGraph');

        if (!$sOgPhoto) {
            $sOgPhoto = Design::getLogo();
        }
    }

    $aImageSize = @getimagesize(WEBPATH . $sOgPhoto);
    $iMaxLength = (int) Parameters::getValByName(Yii::$app->sections->root(), seo\Api::GROUP_PARAM_MICRODATA, 'sum_symbols');
?>

<meta property="og:type" content="website" />
<meta property="og:site_name" content="<?= seo\Api::prepareRawString($sOgSiteName); ?>" />
<meta property="og:url" content="<?= Site::httpDomain() . $oTree->alias_path; ?>" />
<meta property="og:title" content="<?= seo\Api::prepareRawString($sOgTitle); ?>" />
<meta property="og:description" content="<?= ($iMaxLength) ? StringHelper::truncate(seo\Api::prepareRawString($sOgDescription), $iMaxLength) : seo\Api::prepareRawString($sOgDescription); ?>" />
<meta property="og:image" content="<?= Site::httpDomain() . $sOgPhoto; ?>" />
<?php if ($aImageSize) : ?>
    <meta property="og:image:width" content="<?= $aImageSize[0]; ?>" />
    <meta property="og:image:height" content="<?= $aImageSize[1]; ?>" />
<?php endif; ?>

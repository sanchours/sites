<?php
/**
 * Created by PhpStorm.
 * User: ermak
 * Date: 06.07.2018
 * Time: 17:14.
 */

namespace skewer\build\Page\News;

use skewer\base\router\Router;
use skewer\base\section\Parameters;
use skewer\base\site;
use skewer\base\SysVar;
use skewer\build\Adm\News\models\News;
use skewer\components\design\Design;
use skewer\components\gallery\Album;
use skewer\components\seo;
use yii\helpers\StringHelper;

/**
 * Новостной OpenGraph
 * Class OpenGraph.
 */
class OpenGraph
{
    public static function setOpenGraph(News $oNews, seo\SeoPrototype $oSeoComponent)
    {
        $oSeoComponent->initSeoData();
        $sOgTitle = (!empty($oSeoComponent->title)) ? $oSeoComponent->title : $oNews->title;

        if (!empty($oSeoComponent->description)) {
            $sOgDescription = $oSeoComponent->description;
        } else {
            $sOgDescription = $oNews->announce;
            if (!$sOgDescription) {
                $sOgDescription = $oSeoComponent->parseField('description', ['sectionId' => $oSeoComponent->getSectionId()]);
            }
        }

        if (!$oSeoComponent || !($sOgPhoto = Album::getFirstActiveImage($oSeoComponent->seo_gallery, 'format_openGraph'))) {
            if (!SysVar::get('News.galleryStatus') || !($sOgPhoto = Album::getFirstActiveImage($oNews->gallery['gallery_id'], 'big'))) {
                $iGalleryId = (int) Parameters::getValByName(\Yii::$app->sections->root(), seo\Api::GROUP_PARAM_MICRODATA, 'photoOpenGraph');
                $sOgPhoto = Album::getFirstActiveImage($iGalleryId, 'format_openGraph');

                if (!$sOgPhoto) {
                    $sOgPhoto = Design::getLogo();
                }
            }
        }

        $aImageSize = @getimagesize(WEBPATH . $sOgPhoto);
        $iMaxLength = (int) Parameters::getValByName(\Yii::$app->sections->root(), seo\Api::GROUP_PARAM_MICRODATA, 'sum_symbols');

        if ($iMaxLength) {
            $sOgDescription = StringHelper::truncate(seo\Api::prepareRawString($sOgDescription), $iMaxLength);
        } else {
            $sOgDescription = seo\Api::prepareRawString($sOgDescription);
        }

        return [
            'title' => seo\Api::prepareRawString($sOgTitle),
            'description' => $sOgDescription,
            'url' => site\Site::httpDomain() . Router::rewriteURL($oNews->getUrl()),
            'image' => site\Site::httpDomain() . $sOgPhoto,
            'image_width' => $aImageSize[0],
            'image_height' => $aImageSize[1],
            'WEBPROTOCOL' => WEBPROTOCOL,
            'publication_date' => \Yii::$app->getFormatter()->asDate($oNews->publication_date, 'php:d-m-Y'),
            'last_modified_date' => \Yii::$app->getFormatter()->asDate($oNews->last_modified_date, 'php:d-m-Y'),
        ];
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: na
 * Date: 01.09.2017
 * Time: 14:18.
 */

namespace skewer\build\Page\News;

use skewer\base\SysVar;
use skewer\build\Adm\News\models\News;
use skewer\build\Adm\News\Seo;
use skewer\build\Adm\News\ShowType;
use skewer\components\gallery\Photo;
use skewer\components\GalleryOnPage\GetGalleryEvent;

class Api
{
    public static function className()
    {
        return get_called_class();
    }

    public static function registerGallery(GetGalleryEvent $oEvent)
    {
        $oEvent->addGalleryList([
            \skewer\build\Page\News\gallery\GalleryOnNews::className(),
        ]);
    }

    /**
     * Выводить галерею в списке?
     *
     * @return bool
     */
    public static function bShowGalleryInList()
    {
        return in_array(SysVar::get('News.galleryStatus', ShowType::DISABLE), [ShowType::PREVIEW, ShowType::BOTH]);
    }

    /**
     * Выводить галерею на детальной?
     *
     * @return bool
     */
    public static function bShowGalleryInDetail()
    {
        return in_array(SysVar::get('News.galleryStatus', ShowType::DISABLE), [ShowType::DETAIL, ShowType::BOTH]);
    }

    /**
     * Парсинг списка новостей.
     *
     * @param News[] $aNews
     * @param int $iSectionId
     *
     * @return array
     */
    public static function parseList($aNews, $iSectionId)
    {
        $aOut = [];

        foreach ($aNews as $oNew) {
            $aOut[] = self::parseOne($oNew, $iSectionId);
        }

        return $aOut;
    }

    /**
     * Парсинг одной новости.
     *
     * @param News $oNews
     * @param int $iSectionId
     *
     * @return News
     */
    public static function parseOne(News $oNews, $iSectionId)
    {
        $photos = Photo::getFromAlbum($oNews->gallery, true) ?: [];

        $oSeo = new Seo(0, $iSectionId, $oNews);

        foreach ($photos as $image) {
            if (!$image->alt_title) {
                $image->alt_title = $oSeo->parseField('altTitle', [
                    'sectionId' => $iSectionId,
                    'label_number_photo' => $image->priority,
                ]);
            }

            if (!$image->title) {
                $image->title = $oSeo->parseField('nameImage', [
                    'sectionId' => $iSectionId,
                    'label_number_photo' => $image->priority,
                ]);
            }

            $oSeo->clearLabelsFromEntity();
        }

        $oNews->gallery = [
            'gallery_id' => $oNews->gallery,
            'images' => $photos,
            'first_img' => reset($photos),
        ];

        return $oNews;
    }
}

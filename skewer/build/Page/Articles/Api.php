<?php

namespace skewer\build\Page\Articles;

use skewer\base\section\Parameters;
use skewer\base\SysVar;
use skewer\build\Adm\Articles\Seo;
use skewer\build\Adm\News\ShowType;
use skewer\build\Page\Articles\Model\ArticlesRow;
use skewer\components\gallery\Photo;
use yii\helpers\ArrayHelper;

class Api
{
    const SHOW_TYPE_MAIN = 'typeShowMain';
    const SHOW_TYPE_LIST = 'typeShowList';

    const NAME_SYSVAR_PARAM = 'Articles.galleryStatusArticles';

    public static $aTypeShowArticles =
        [
            'list' => [
                'title' => 'Articles.field_list',
                'file' => 'list.twig',
            ],
            'columns' => [
                'title' => 'Articles.field_columns',
                'file' => 'columns.twig',
            ],
            'vertical' => [
                'title' => 'Articles.field_vertical',
                'file' => 'vertical.twig',
            ],
        ];

    /**
     * Получение массива шаблонов с заголовками.
     *
     * @return array
     */
    public static function getArray4TypeShow()
    {
        return ArrayHelper::getColumn(self::$aTypeShowArticles, 'title');
    }

    /**
     * Получение шаблона для отображения статей.
     *
     * @param $sectionId
     * @param $zone
     *
     * @return mixed
     */
    public static function getTemplate4TypeShow($sectionId, $zone)
    {
        if ($zone == 'left' || $zone == 'right') {
            return self::$aTypeShowArticles['vertical']['file'];
        }
        $sParamName = ($sectionId == \Yii::$app->sections->main()) ? self::SHOW_TYPE_MAIN : self::SHOW_TYPE_LIST;
        $typeShow = Parameters::getValByName(\Yii::$app->sections->languageRoot(), 'articles', $sParamName, true);
        $sTemplate = ($typeShow) ? self::$aTypeShowArticles[$typeShow]['file'] : self::$aTypeShowArticles['list']['file'];

        return $sTemplate;
    }

    /**
     * Выводить галерею в списке?
     *
     * @return bool
     */
    public static function bShowGalleryInList()
    {
        return in_array(SysVar::get(self::NAME_SYSVAR_PARAM, ShowType::DISABLE), [ShowType::PREVIEW, ShowType::BOTH]);
    }

    /**
     * Выводить галерею на детальной?
     *
     * @return bool
     */
    public static function bShowGalleryInDetail()
    {
        return in_array(SysVar::get(self::NAME_SYSVAR_PARAM, ShowType::DISABLE), [ShowType::DETAIL, ShowType::BOTH]);
    }

    /**
     * Парсинг списка статей.
     *
     * @param ArticlesRow[] $aArticles
     * @param int $iSectionId
     *
     * @return array
     */
    public static function parseList($aArticles, $iSectionId)
    {
        $aOut = [];

        foreach ($aArticles as $oArticle) {
            $aOut[] = self::parseOne($oArticle, $iSectionId);
        }

        return $aOut;
    }

    /**
     * Парсинг одной статьи.
     *
     * @param ArticlesRow $oArticle
     * @param int $iSectionId
     *
     * @return ArticlesRow
     */
    public static function parseOne(ArticlesRow $oArticle, $iSectionId)
    {
        $photos = Photo::getFromAlbum($oArticle->gallery, true) ?: [];

        $oSeo = new Seo(0, $iSectionId, $oArticle);

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

        $oArticle->gallery = [
            'gallery_id' => $oArticle->gallery,
            'images' => $photos,
            'first_img' => reset($photos),
        ];

        return $oArticle;
    }
}

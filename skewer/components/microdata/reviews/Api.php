<?php

namespace skewer\components\microdata\reviews;

use skewer\base\section\Parameters;
use skewer\base\site_module;
use skewer\build\Adm\News\models\News;
use skewer\build\Page\Articles\Api as ArticlesApi;
use skewer\build\Page\Articles\Model\ArticlesRow;
use skewer\build\Page\News\Api as NewsApi;
use skewer\components\gallery\Photo;
use skewer\components\seo;
use yii\helpers\ArrayHelper;

/**
 * Class Api
 * Класс для работы с микроразметкой отзывов(отзывов к товарам и разделам).
 */
class Api
{
    /**
     * Построит html микроразметки отзывов товара.
     *
     * @param array $aReviews - массив отзывов
     *
     * @return string
     */
    public static function buildHtml4GoodReviews($aReviews)
    {
        return self::buildHtmlByArrayReviews($aReviews, false);
    }

    /**
     * Построит html микроразметки отзывов раздела.
     *
     * @param array $aReviews - массив отзывов
     *
     * @return string
     */
    public static function buildHtml4SectionReviews($aReviews)
    {
        return self::buildHtmlByArrayReviews($aReviews, true);
    }

    /**
     * Построит микроразметку для отзывов.
     *
     * @param array $aReviews - массив отзывов
     * @param bool $bIncludeMicrodataOrganization - включать микроразметку организации(!!!нужна только для отзывов к разделу)
     *
     * @return string
     * */
    private static function buildHtmlByArrayReviews($aReviews, $bIncludeMicrodataOrganization = false)
    {
        $aParseData = [
            'items' => $aReviews,
            'bIsReview4Good' => !$bIncludeMicrodataOrganization,
        ];

        if ($bIncludeMicrodataOrganization) {
            $aParseData['aOrganization'] = self::getOrganizationData();
        }

        $sHtml = site_module\Parser::parseTwig('MicroDataReviews.twig', $aParseData, __DIR__ . \DIRECTORY_SEPARATOR . 'templates');

        return $sHtml;
    }

    /**
     * Построит html микроразметки новости.
     *
     * @param News $oNews - объект новости
     *
     * @return string
     */
    public static function microData4News(News $oNews)
    {
        $aLogo = Photo::getLogoInfo();

        $aImage = $aLogo;

        if (NewsApi::bShowGalleryInDetail() && $oNews->gallery['first_img']) {
            $aImage = [
                'src' => ArrayHelper::getValue($oNews->gallery, 'first_img.images_data.big.file'),
                'width' => ArrayHelper::getValue($oNews->gallery, 'first_img.images_data.big.width'),
                'height' => ArrayHelper::getValue($oNews->gallery, 'first_img.images_data.big.height'),
            ];
        }

        if ((new \DateTime($oNews->last_modified_date))->getTimestamp() > 0) {
            $dateModified = $oNews->last_modified_date;
        } else {
            $dateModified = $oNews->publication_date;
        }

        $aData = [
            'aOrganization' => self::getOrganizationData(),
            'WEBPROTOCOL' => WEBPROTOCOL,
            'oNews' => $oNews,
            'author' => \Yii::$app->request->absoluteUrl,
            'aLogo' => $aLogo,
            'aImage' => $aImage,
            'dateModified' => $dateModified,
        ];

        return site_module\Parser::parseTwig('News.twig', $aData, __DIR__ . \DIRECTORY_SEPARATOR . '/templates');
    }

    /**
     * Построит html микроразметки статьи.
     *
     * @param ArticlesRow $oArticlesRow - объект статьи
     *
     * @return string
     */
    public static function microData4Articles(ArticlesRow $oArticlesRow)
    {
        $aLogo = Photo::getLogoInfo();

        $aImage = $aLogo;

        if (ArticlesApi::bShowGalleryInDetail() && $oArticlesRow->gallery['first_img']) {
            $aImage = [
                'src' => ArrayHelper::getValue($oArticlesRow->gallery, 'first_img.images_data.big.file'),
                'width' => ArrayHelper::getValue($oArticlesRow->gallery, 'first_img.images_data.big.width'),
                'height' => ArrayHelper::getValue($oArticlesRow->gallery, 'first_img.images_data.big.height'),
            ];
        }

        $aData = [
            'aOrganization' => self::getOrganizationData(),
            'aLogo' => $aLogo,
            'aImage' => $aImage,
            'WEBPROTOCOL' => WEBPROTOCOL,
            'oArticlesRow' => $oArticlesRow,
            'author' => $oArticlesRow->author ? $oArticlesRow->author : \Yii::$app->request->getAbsoluteUrl(),
        ];

        return site_module\Parser::parseTwig('Articles.twig', $aData, __DIR__ . \DIRECTORY_SEPARATOR . '/templates');
    }

    /**
     * Получить данные об организации.
     *
     * @return array Массив с ключами 'address', 'phone', 'name'
     */
    public static function getOrganizationData()
    {
        return [
            'address' => Parameters::getValByName(\Yii::$app->sections->root(), seo\Api::GROUP_PARAM_MICRODATA, 'OrganizationAddress') ?: '',
            'phone' => Parameters::getValByName(\Yii::$app->sections->root(), seo\Api::GROUP_PARAM_MICRODATA, 'OrganizationPhone') ?: '',
            'name' => Parameters::getValByName(\Yii::$app->sections->root(), seo\Api::GROUP_PARAM_MICRODATA, 'OrganizationName') ?: '',
        ];
    }
}

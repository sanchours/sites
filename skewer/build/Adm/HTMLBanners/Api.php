<?php

namespace skewer\build\Adm\HTMLBanners;

use skewer\base\section\Page;
use skewer\base\section\Tree;
use skewer\components\auth\Auth;
use skewer\components\i18n\Languages;
use yii\helpers\ArrayHelper;

/**
 * Class Api.
 */
class Api
{
    /**
     * Конфигурационный массив для положений.
     *
     * @var array
     */
    protected static $aBannerLocations = [
        'left' => [
            'name' => 'left',
            'title' => 'position_left',
            'pos' => 1, ],
        'right' => [
            'name' => 'right',
            'title' => 'position_right',
            'pos' => 2, ],
        'content_top' => [
            'name' => 'content_top',
            'title' => 'position_top',
            'pos' => 3, ],
        'content_bottom' => [
            'name' => 'content_bottom',
            'title' => 'position_bottom',
            'pos' => 4, ],
    ];

    /*
     * извлекает по дереву разделов все секции
     * */
    public static function getSectionList()
    {
        $iPolicyId = Auth::getPolicyId('public');
        $aSections = [\Yii::$app->sections->root() => \Yii::t('search', 'all_site')];

        if ($aLanguages = Languages::getAllActiveNames()) {
            if ($aLanguages) {
                foreach ($aLanguages as $sLang) {
                    if (count($aLanguages) > 1) {
                        $aSections[\Yii::$app->sections->getValue(Page::LANG_ROOT, $sLang)] = Tree::getSectionTitle(\Yii::$app->sections->getValue(Page::LANG_ROOT, $sLang), true);
                    }

                    $aSectionsFromTopMenu = Tree::getSectionList(\Yii::$app->sections->getValue('topMenu', $sLang), $iPolicyId);
                    $aSectionsFromLeftMenu = Tree::getSectionList(\Yii::$app->sections->getValue('leftMenu', $sLang), $iPolicyId);

                    $aSections = array_replace(
                        $aSections,
                        ArrayHelper::map($aSectionsFromTopMenu, 'id', 'title'),
                        ArrayHelper::map($aSectionsFromLeftMenu, 'id', 'title')
                    );
                }
            }
        }

        return $aSections;
    }

    /**
     * @static
     *
     * @return array
     */
    public static function getBannerLocations()
    {
        return ArrayHelper::map(self::$aBannerLocations, 'name', static function ($banner) {
            return \Yii::t('HTMLBanners', $banner['title']);
        });
    }

    /**
     * @static
     *
     * @param mixed $banner
     *
     * @return array
     */
    public static function getBannerLocation($banner)
    {
        return \Yii::t('HTMLBanners', self::$aBannerLocations[$banner['location']]['title']);
    }

    /**
     * @static
     *
     * @return array
     */
    public static function getBannerLocationsPos()
    {
        return ArrayHelper::map(self::$aBannerLocations, 'name', 'pos');
    }

    /**
     * @static
     *
     * @return array
     */
    public static function getSectionTitle()
    {
        return Tree::getSectionsTitle(\Yii::$app->sections->root(), true);
    }
}// class

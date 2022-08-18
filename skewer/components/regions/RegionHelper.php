<?php

namespace skewer\components\regions;

use skewer\base\site\Site;
use skewer\build\Tool\Labels\models\Labels;
use skewer\components\config\Exception;
use skewer\components\config\installer\Api as ApiInstaller;
use skewer\components\regions\models\RegionLabels;
use skewer\components\regions\models\Regions;
use yii\helpers\ArrayHelper;

class RegionHelper
{
    /**
     * Массив данных текущего региона.
     *
     * @var array
     */
    private static $currentRegion = [];

    /**
     * Домен дефолтного региона.
     *
     * @var string
     */
    private static $defaultDomainOfRegion;

    const REQUEST_SELECT_REGION = 'selectRegion';

    /**
     * Возвращает текущий выбранный или дефолтный регион.
     *
     * @throws Exception
     *
     * @return array
     */
    public static function getDataSelectedRegion()
    {
        if (!self::$currentRegion) {
            $region = CookieRegion::getRegionCookies();

            $activeRegion = Regions::getActiveRegionByDomain($region);

            if ($activeRegion === null) {
                $activeRegion = Regions::getDefaultRegion();
            }

            self::$currentRegion = $activeRegion ? $activeRegion->toArray() : [];
        }

        return self::$currentRegion;
    }

    /**
     * Отдает домен по умолчанию.
     *
     * @throws Exception
     *
     * @return mixed|string
     */
    public static function getDefaultRegionSubDomain()
    {
        if (!isset(self::$defaultDomainOfRegion)) {
            $defaultRegion = Regions::getDefaultRegion();
            self::$defaultDomainOfRegion = ArrayHelper::getValue(
                $defaultRegion,
                'domain'
            );
        }

        return self::$defaultDomainOfRegion;
    }

    /**
     * Проверка установки модуля.
     *
     * @return bool
     */
    public static function isInstallModuleRegion()
    {
        $installer = new ApiInstaller();

        $paramRegion = new ParamForRegion();

        return $installer->isInstalled('Regions', 'Page')
            && $installer->isInstalled('Regions', 'Tool')
            && $paramRegion->hasInstallParam();
    }

    /**
     * Возвращает набор меток для замены.
     *
     * @throws Exception
     *
     * @return null|array
     */
    public static function getReplaceData()
    {
        //Id региона
        $selectedRegion = self::getDataSelectedRegion();
        $idRegion = ArrayHelper::getValue($selectedRegion, 'id');

        //Значения
        $valuesRegion = RegionLabels::getReplaceLabelForRegion($idRegion);

        $labels = Labels::getAll();
        /** @var Labels $label */
        foreach ($labels as $label) {
            $labelsForReplace['pattern'][] = '/\[\[' . $label->alias . '\]\]/i';
            $labelsForReplace['replaces'][] = ArrayHelper::getValue($valuesRegion, $label->id, $label->default);
        }

        return $labelsForReplace ?? null;
    }

    /**
     * Возвращает полный домен  по поддомену.
     *
     * @param string $sSubDomain
     *
     * @return string
     */
    public static function getFullDomain($sSubDomain)
    {
        if ($sSubDomain) {
            $sSubDomain .= '.';
        }

        return $sSubDomain . Site::domain();
    }

    /**
     * Возвращат поддомен по полному домену.
     *
     * @param $sDomain
     *
     * @return bool|string
     */
    public static function getSubDomain($sDomain)
    {
        $sSubDomain = mb_strstr($sDomain, Site::domain(), true);
        if ($sSubDomain) {
            $sSubDomain = mb_substr($sSubDomain, 0, -1);
        }

        return $sSubDomain;
    }

    /**
     * @param $path
     *
     * @throws Exception
     *
     * @return string
     */
    public static function getCanonical($path)
    {
        $region = self::getDataSelectedRegion();
        $domain = ArrayHelper::getValue($region, 'domain', Site::domain());
        if ($domain) {
            $domain = "{$domain}." . Site::domain();
        }

        return WEBPROTOCOL . $domain . $path;
    }

    /**
     * Регистрация региона.
     *
     * @throws \skewer\components\config\Exception
     */
    public static function checkRegion()
    {
        $defaultRegion = RegionHelper::getDefaultRegionSubDomain();
        $currentSubDomain = RegionHelper::getSubDomain($_SERVER['HTTP_HOST']);

        $selectedRegion = \Yii::$app->request->get(RegionHelper::REQUEST_SELECT_REGION);
        if (Regions::isActiveDomain($selectedRegion)) {
            self::setCookieAndRedirectToDomain($selectedRegion, $currentSubDomain != $selectedRegion);
            return;
        }

        $regionCookie = CookieRegion::getRegionCookies();
        if ($regionCookie !== null) {
            self::findRegionWithCookie($regionCookie, $currentSubDomain, $defaultRegion);
            return;
        }

        if (!Regions::isActiveDomain($currentSubDomain)) {
            self::resetCurrentDomainToDefault();
            return;
        }

        self::findRegionWithoutCookie($currentSubDomain, $defaultRegion);
    }

    /**
     * @param $currentSubDomain
     * @param $defaultRegion
     *
     * @throws Exception
     */
    private static function findRegionWithoutCookie($currentSubDomain, $defaultRegion)
    {
        // если зашли на поддомен и он существует, то остаемся на нем, иначе ищем подходящий домен по местоположению
        if ($currentSubDomain != $defaultRegion && Regions::isActiveDomain($currentSubDomain)) {
            self::setCookieAndRedirectToDomain($currentSubDomain, false);
            CookieRegion::setShowFancybox();
        } else {
            //Поиск поддомена по utm + гео
            $subDomain = SubDomain::findSubDomain();
            self::setCookieAndRedirectToDomain($subDomain, false);
        }
    }

    private static function findRegionWithCookie($regionCookie, $currentSubDomain, $defaultRegion)
    {
        if (Regions::isActiveDomain($regionCookie)) {
            self::setCookieAndRedirectToDomain($regionCookie, $currentSubDomain != $regionCookie);
        } else {
            //Проверка активности текущего поддомена
            if (Regions::isActiveDomain($currentSubDomain)) {
                self::setCookieAndRedirectToDomain($currentSubDomain, false);
            } else {
                //Дефолтный поддомен
                CookieRegion::removeShowFancybox();
                self::setCookieAndRedirectToDomain($defaultRegion, $currentSubDomain != $defaultRegion);
            }
        }
    }

    private static function setCookieAndRedirectToDomain($domain, $redirect = true)
    {
        CookieRegion::setDomainOfRegion($domain);

        if ($redirect) {
            self::redirectToDomain(RegionHelper::getFullDomain($domain));
        }
    }

    private static function resetCurrentDomainToDefault()
    {
        CookieRegion::removeShowFancybox();
        self::redirectToDefaultDomain();
    }

    private static function redirectToDefaultDomain()
    {
        $defaultRegion = Regions::getDefaultRegion();
        if ($defaultRegion instanceof Regions) {
            $fullDomain = RegionHelper::getFullDomain($defaultRegion->domain);
            self::redirectToDomain($fullDomain);
        }
    }

    /**
     * @param string $fullDomain
     */
    private static function redirectToDomain(string $fullDomain)
    {
        $url = WEBPROTOCOL . $fullDomain . \Yii::$app->request->url;

        $redirect = \Yii::$app->response->redirect($url, 302);
        $redirect->headers->add('Origin', Site::httpDomain());
        $redirect->headers->add('Access-Control-Allow-Origin', '*');

        $redirect->send();
        \Yii::$app->end();
    }
}

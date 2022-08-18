<?php

namespace skewer\build\Tool\Domains;

use skewer\build\Tool\Domains\models\Domain;
use skewer\components\seo;

/**
 * API работы с резевным копированием
 */
class Api
{
    /**
     * Домен.
     *
     * @var null|string
     */
    private static $sDomain = null;

    /**
     * Сборс закешированных значений
     * Нужен для тестирования.
     */
    public static function clearCache()
    {
        self::$sDomain = null;
    }

    public static function getAllDomains()
    {
        if (
            !$domains = Domain::find()
                ->asArray()
                ->all()
        ) {
            $domains = [];
        }

        return [
            'items' => $domains,
            'count' => count($domains),
        ];
    }

    /**
     * Отдает имя основного домена или false, если он не привязан.
     *
     * @return bool|string
     */
    public static function getMainDomain()
    {
        if (static::$sDomain !== null) {
            return static::$sDomain;
        }

        if ($domain = Domain::findOne(['prim' => 1])) {
            return static::$sDomain = $domain->domain;
        }

        return static::$sDomain = false;
    }

    /**
     * Отдает имя текущего домена.
     * Если привязан основной, но используется сейчас другой, будет возвращен основной.
     *
     * @return string
     */
    public static function getCurrentDomain()
    {
        $sMainDomain = self::getMainDomain();

        return $sMainDomain ? $sMainDomain : $_SERVER['HTTP_HOST'];
    }

    /**
     * получение массива редиректов на основной домен с вторичных.
     *
     * @static
     *
     * @return array
     */
    public static function getRedirectItems()
    {
        $aOut = [];

        $aItems = self::getAllDomains();

        $aPrimItem = false;

        if ($aItems['count']) {
            foreach ($aItems['items'] as $aItem) {
                if ($aItem['prim']) {
                    $aPrimItem = $aItem;
                }
            }

            if ($aPrimItem) {
                foreach ($aItems['items'] as $aItem) {
                    if ($aItem['d_id'] != $aPrimItem['d_id']) {
                        $aOut[] = ['old_url' => $aItem['domain'], 'new_url' => $aPrimItem['domain']];
                    }
                }
            }
        }

        return $aOut;
    }

    public static function syncDomains($aDomains)
    {
        if (!$aDomains || !is_array($aDomains) || !count($aDomains)) {
            $aRealDomain = self::getAllDomains();
            $aRealDomain = $aRealDomain['items'] ?? [];

            foreach ($aRealDomain as $aCurDomain) {
                Domain::deleteAll(['d_id' => $aCurDomain['d_id']]);
            }

            seo\Service::setNewDomainToSiteMap();

            seo\Service::updateRobotsTxt(false);
            \skewer\build\Tool\Redirect301\Api::makeHtaccessFile();

            return true;
        }

        $aRealDomain = self::getAllDomains();
        $aRealDomain = $aRealDomain['items'] ?? [];

        $aUpdDomains = [];
        $aDelDomains = [];

        foreach ($aDomains as $aCurDomain) {
            $flag = false;
            foreach ($aRealDomain as $aCurRealDomain) {
                if ($aCurDomain['domain'] == $aCurRealDomain['domain']) {  // upd
                    $item = $aCurRealDomain;
                    $item['prim'] = $aCurDomain['prim'];
                    $item['domain_id'] = $aCurDomain['domain_id'];
                    $aUpdDomains[] = $item;
                    $flag = true;
                }
            }
            if (!$flag) { // new
                $item = [];
                $item['domain'] = $aCurDomain['domain'];
                $item['prim'] = $aCurDomain['prim'];
                $item['domain_id'] = $aCurDomain['domain_id'];
                $aUpdDomains[] = $item;
            }
        }

        foreach ($aRealDomain as $aCurRealDomain) {
            $flag = false;
            foreach ($aDomains as $aCurDomain) {
                if ($aCurDomain['domain'] == $aCurRealDomain['domain']) {
                    $flag = true;
                }
            }
            if (!$flag || !count($aDomains)) {
                $aDelDomains[] = $aCurRealDomain['d_id'];
            }
        }

        foreach ($aUpdDomains as $aCurDomain) {
            $domain = new Domain();

            if (isset($aCurDomain['d_id'])) {
                if (!$domain = Domain::findOne($aCurDomain['d_id'])) { // если найдется - обновим, иначе пропустим
                    continue;
                }
            }

            $domain->setAttributes($aCurDomain);
            $domain->save();
        }

        foreach ($aDelDomains as $aCurDomain) {
            Domain::deleteAll(['d_id' => $aCurDomain]);
        }

        seo\Service::setNewDomainToSiteMap();

        \skewer\build\Tool\Redirect301\Api::makeHtaccessFile();

        seo\Service::updateRobotsTxt(self::getCurrentDomain());

        return true;
    }

    public static function addDomain($sDomain, $iPrim = 0, $iDomainId = 0)
    {
        $newDomain = new Domain();

        $newDomain->setAttributes(
            [
                'domain_id' => $iDomainId,
                'domain' => $sDomain,
                'prim' => $iPrim,
            ]
        );

        $newDomain->save();

        return true;
    }

    public static function delDomain($sDomain)
    {
        Domain::deleteAll(['domain' => $sDomain]);

        return true;
    }
}

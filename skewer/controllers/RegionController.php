<?php

namespace skewer\controllers;

use skewer\base\site\Site;
use skewer\components\config\Exception;
use skewer\components\regions\models\Regions;
use skewer\components\regions\RegionHelper;
use skewer\components\seo\Robots;
use skewer\components\seo\Sitemap;

class RegionController extends Prototype
{
    /**
     * @throws Exception
     *
     * @return bool|void
     */
    public function init()
    {
        if (parent::init() && RegionHelper::isInstallModuleRegion()) {
            // Проверяем на существование и активность поддомена
            $subDomain = RegionHelper::getSubDomain($_SERVER['HTTP_HOST']);

            if (!Regions::isActiveDomain($subDomain)) {
                $fullDomain = RegionHelper::getFullDomain(
                    RegionHelper::getDefaultRegionSubDomain()
                );

                \Yii::$app->response->redirect(
                    WEBPROTOCOL . $fullDomain . '/' . \Yii::$app->request->url,
                    301
                )->send();
            }
        }
    }

    public function actionShowRobots()
    {
        $host = $_SERVER['HTTP_HOST'];
        $patterns = [];

        $patterns[] = '/^[\S]*sk[\d]{3}.ru$/';
        $patterns[] = '/^[\S]*sktest.ru$/';
        $patterns[] = '/^[\S]*twinslab.ru$/';

        $bDenyAll = false;

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $host)) {
                // запретим всё
                $bDenyAll = true;
            }
        }

        $testDomain = \Yii::$app->getParam('test_domains');

        if ($testDomain && is_array($testDomain)) {
            if (array_search($host, $testDomain) !== false) {
                $bDenyAll = true;
            }
        }

        header('Content-type: text/plain; charset=utf-8');

        $robotsPath = Robots::getFullFilePath();
        // если домен тестовый или файла robots.txt нет в корне, то доступ запрещаем
        if ($bDenyAll || !file_exists($robotsPath)) {
            exit("User-agent: *\nDisallow: /");
        }

        $robotsContent = '';
        if (file_exists($robotsPath)) {
            $robotsContent = file_get_contents($robotsPath);
            $robotsContent = $this->replaceDomain($robotsContent);
        }

        exit($robotsContent);
    }

    public function actionShowMainSitemap()
    {
        $sitemapContent = '';
        $sitemapPath = Sitemap::getFullFilePath();
        if (file_exists($sitemapPath)) {
            $sitemapContent = file_get_contents($sitemapPath);
            $sitemapContent = $this->replaceDomain($sitemapContent);
        }

        header('Content-type: text/xml; charset=utf-8');

        exit($sitemapContent);
    }

    public function actionShowSitemapFile()
    {
        $sitemapContent = '';

        $sitemapFile = \Yii::$app->request->get('file', '');
        $sitemapFilePath = Sitemap::getDirPath() . $sitemapFile;

        if (file_exists($sitemapFilePath)) {
            $sitemapContent = file_get_contents($sitemapFilePath);
            $sitemapContent = $this->replaceDomain($sitemapContent);
        }

        header('Content-type: text/xml; charset=utf-8');

        exit($sitemapContent);
    }

    /**
     * Заменяет исходные пути к разделам на.
     *
     * @param $text
     *
     * @return mixed
     */
    public function replaceDomain($text)
    {
        return str_replace(Site::domain(), $_SERVER['HTTP_HOST'], $text);
    }
}

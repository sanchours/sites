<?php

namespace skewer\build\Page\Regions;

use skewer\base\site\Site;
use skewer\base\site_module;
use skewer\components\regions\CookieRegion;
use skewer\components\regions\models\Regions;
use skewer\components\Regions\RegionHelper;

class Module extends site_module\page\ModulePrototype
{
    /** @var string Класс модификатор */
    public $classModifier = '';
    public $template = 'regions.twig';

    /**
     * @throws \skewer\components\config\Exception
     *
     * @return int
     */
    public function execute()
    {
        if (!CookieRegion::wasShownFancybox()) {
            CookieRegion::setShowFancybox();

            $this->setData(CookieRegion::SHOW_FANCYBOX, 1);
        }

        // данные по текущему региону
        $currentRegion = RegionHelper::getDataSelectedRegion();

        // список регионов
        $regionsWithoutCurrent = Regions::getRegionsWithoutCurrent(
            $currentRegion['domain']
        );

        $this->setData('classModifier', $this->classModifier);
        $this->setData('currentRegion', $currentRegion);

        $this->setData(
            'regionsWithoutCurrent',
            $this->setUrlForRegions($regionsWithoutCurrent)
        );

        $this->setTemplate($this->template);

        return psComplete;
    }

    private function setUrlForRegions($regions)
    {
        $siteDomain = Site::domain();
        $path = \Yii::$app->request->pathInfo;
        $selectParam = RegionHelper::REQUEST_SELECT_REGION;

        foreach ($regions as $key => $region) {
            $domain = $region['domain'] ? "{$region['domain']}." : '';

            $regions[$key]['url'] = WEBPROTOCOL . "{$domain}{$siteDomain}/{$path}?{$selectParam}={$region['domain']}";
        }

        return $regions;
    }
}

<?php

namespace skewer\build\Page\MainBanner;

use skewer\base\site_module;
use skewer\build\Tool\Slider\Api;
use skewer\build\Tool\Slider\models;
use skewer\helpers\Adaptive;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * Модуль вывода банера в шапке.
 */
class Module extends site_module\page\ModulePrototype
{
    /** @var string шаблон баннера */
    public $template = 'banner.twig';

    public function init()
    {
        $this->setParser(parserTwig);
    }

    public function execute()
    {
        $aBanner = models\BannersMain::getFirstActiveBanner4Section($this->sectionId());

        if (!$aBanner) {
            return psBreak;
        }

        \Yii::$app->router->setLastModifiedDate($aBanner['last_modified_date']);

        $aSlides = models\BannerSlides::getSlides4Banner($aBanner['id']);

        $aBannerTools = Api::getAllTools($aBanner);

        $this->setData('configArray', $aBannerTools);
        $this->setData('config', Json::htmlEncode($aBannerTools));
        $this->setData('aMinHeight', Json::htmlEncode(ArrayHelper::getValue($aBannerTools, 'height_limits', '')));

        $this->setData('banner', $aSlides);
        $this->setData('aDimensionsFirstImage', Api::getDimensionsFirstImage($aSlides));
        $this->setData('adaptiveBreakpoints', Json::htmlEncode(Adaptive::getBreakpoints()));

        $this->setTemplate($this->template);

        return psComplete;
    }
}

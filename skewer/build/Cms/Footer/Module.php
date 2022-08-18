<?php

namespace skewer\build\Cms\Footer;

use skewer\base\site\Site;
use skewer\base\SysVar;
use skewer\build\Cms;
use yii\i18n\Formatter;

/**
 * Class Module.
 */
class Module extends Cms\Frame\ModulePrototype
{
    //** Вывод даты гарантийного обслуживания */
    private $sSwitch = 'show';
    const END_REGISTER = 'end';
    const NO_DATE_REGISTER = 'no';

    public function execute($defAction = '')
    {
        $bWarrantySupport = SysVar::get('Page.warranty_support');
        if ($bWarrantySupport) {
            $sDataEnd = SysVar::get('Page.data_end_service');
            if ($sDataEnd) {
                $difTime = strtotime($sDataEnd) - strtotime(date('Y-m-d'));
                if ($difTime > 0) {
                    $oFormat = new Formatter();
                    $oFormat->locale = \Yii::$app->i18n->getTranslateLanguage();
                    $sDataSupport = $oFormat->asDuration($difTime);
                } else {
                    $this->sSwitch = self::END_REGISTER;
                }
            } else {
                $this->sSwitch = self::NO_DATE_REGISTER;
            }
        }

        // отдать перекрывающий инициализационный параметр для модуля
        $this->setJSONHeader('init', [
            'html' => $this->renderTemplate('view.twig', [
                'hideCopyright' => SysVar::get('Page.hide_adm_copyright'),
                'logoImg' => $this->getModuleWebDir() . '/img/logo.png',
                'version' => Site::getCmsVersion(),
                'sDataSupport' => (isset($sDataSupport)) ? $sDataSupport : '',
                'sSwitch' => $this->sSwitch,
                'bWarrantySupport' => $bWarrantySupport,
            ]),
            'hideCopyright' => SysVar::get('Page.hide_adm_copyright'),
            'logoImg' => $this->getModuleWebDir() . '/img/logo.png',
            'version' => Site::getCmsVersion(),
            'sDataSupport' => (isset($sDataSupport)) ? $sDataSupport : '',
            'sSwitch' => $this->sSwitch,
            'bWarrantySupport' => $bWarrantySupport,
        ]);

        $this->setModuleLangValues([
          'link_developer',
          'end_service',
          'end_href',
          'support_rates',
          'end_service_rates',
          'end_service_rates_link',
          'need_tech_support',
          'need_active_support',
          'help',
          'link_help'
        ]);


        return psComplete;
    }

    // func
}// class

<?php

namespace skewer\build\Page\SEOMetatags;

use skewer\base\site;
use skewer\base\site_module;
use skewer\components\seo;

/**
 * Модуль вывода метотегов для страницы
 * Class Module.
 */
class Module extends site_module\page\ModulePrototype
{
    /**
     * Выполнение модуля.
     *
     * @return int
     */
    public function execute()
    {
        $oRootModule = site\Page::getRootModule();

        // ждем выполнения главного модуля
        $oContentModule = site\Page::getMainModuleProcess();
        if ($oContentModule && !$oContentModule->isComplete()) {
            return psWait;
        }

        // Перекрытые, уже распарсенные метки
        $aOverridenLabels = [];
        foreach (seo\SeoPrototype::getFieldList() as $sField => $sLabel) {
            if ($sValue = $this->getEnvParam($sLabel)) {
                $aOverridenLabels[$sLabel] = $sValue;
            }
        }

        /** @var seo\SeoPrototype $oSeoComponent */
        if ($oSeoComponent = $this->getEnvParam(seo\Api::SEO_COMPONENT, null)) {
            $oSeoComponent->initSeoData();

            foreach ($oSeoComponent::getFieldList() as $sField => $sLabel) {
                if (isset($aOverridenLabels[$sLabel])) {
                    $oRootModule->setData($sLabel, $aOverridenLabels[$sLabel]);
                } elseif (in_array($sField, seo\SeoPrototype::getField4Parsing())) {
                    $sValue = (!empty($oSeoComponent->{$sField}))
                        ? $oSeoComponent->{$sField}
                        : $oSeoComponent->parseField($sField, ['sectionId' => $this->sectionId()]);

                    $oRootModule->setData($sLabel, $sValue);
                } else {
                    $oRootModule->setData($sLabel, $oSeoComponent->{$sField});
                }
            }
        }

        $oRootModule->setData(seo\Api::OPENGRAPH, $this->getEnvParam(seo\Api::OPENGRAPH, ''));

        return psRendered;
    }
}

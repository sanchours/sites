<?php

namespace skewer\build\Tool\Maps;

use skewer\base\SysVar;
use skewer\build\Page\CatalogMaps\Api;
use skewer\build\Tool;
use yii\helpers\ArrayHelper;

/**
 * Модуль глобальных настроек карт
 * Class Module.
 */
class Module extends Tool\LeftList\ModulePrototype
{
    protected function actionInit()
    {
        $typeMap = SysVar::get($this->languageCategory . '.type_map');
        return $this->getSettingsByMap($typeMap);
    }

    protected function actionChangeTypeCart()
    {
        $typeMap = ArrayHelper::getValue(
            $this->get('formData', []),
            'typeMap',
            null
        );

        return $this->getSettingsByMap($typeMap);
    }

    private function getSettingsByMap($typeMap)
    {
        $function = 'getInterfaceSettings' . ucfirst($typeMap);

        if (method_exists($this, $function)) {
            return $this->{$function}();
        }
        return $this->getInterfaceSettings();
    }

    private function getInterfaceSettings()
    {
        return $this->render(new view\Index([
            'languageCategory' => $this->languageCategory,
        ]));
    }

    private function getInterfaceSettingsYandex()
    {
        $settingsMap = new SettingsMap(
            Api::providerYandexMap,
            $this->languageCategory
        );

        return $this->render(new view\YandexSettings([
            'languageCategory' => $this->languageCategory,
            'settingsMap' => $settingsMap->getAttributes(),
        ]));
    }

    private function getInterfaceSettingsGoogle()
    {
        $settingsMap = new SettingsMap(
            Api::providerGoogleMap,
            $this->languageCategory
        );

        return $this->render(new view\GoogleSettings([
            'languageCategory' => $this->languageCategory,
            'settingsMap' => $settingsMap->getAttributes(),
        ]));
    }

    /**
     * Сохранение используемого типа карты.
     * @throws \Exception
     */
    protected function actionSaveTypeMap()
    {
        $typeMap = $this->getInDataVal('typeMap', '');

        $settings = new SettingsMap($typeMap, $this->languageCategory);
        $settings->setAttributes($this->getInData());

        if (!$settings->validate()) {
            throw new \Exception(current($settings->getFirstErrors()));
        }
        $settings->save();

        $this->actionInit();
    }
}

<?php

namespace skewer\build\Adm\ParamSettings;

use skewer\base\section\Parameters;

/**
 * Админский модуль настроек специальных параметров, которые собираются из классов ParamSettings в инсталлированных
 * модулях слоя Page.
 * Class Module.
 */
class Module extends \skewer\build\Adm\Editor\Module
{
    /**
     * Запрос доступных для редактирования параметров.
     *
     * @return \skewer\base\section\models\ParamsAr[]
     */
    protected function getAvailItems()
    {
        $language = Parameters::getLanguage($this->sectionId());
        return Api::getParameters($language);
    }

    /**
     * Сохранение данных.
     */
    public function actionSave()
    {
        // Выполнить сохранение параметров в БД
        parent::actionSave();

        /* Если потребуется узнать в каких именно полях и какие параметры были изменены, то можно в этом блоке
        запросить текущие состояния параметров через getAvailItems() с установленным в true параметром
        bGetParamsAsArray и сравнить их с теми что приходят в POST.
        Или модифицировать метод parent::actionSave() добавив в него проверку изменения параметров */

        // Дёрнуть у установленных модулей, с параметрами для редактирования, метод saveData()
        foreach (Api::getModulesParamsObjects() as $oParamsObject) {
            $oParamsObject->saveData();
        }

        $this->actionLoadItems();
    }

    /** {@inheritdoc} */
    protected function hasSeoFields($iSectionId)
    {
        return false;
    }

    /** {@inheritdoc} */
    protected function getFieldSectionLinkData()
    {
        return false;
    }

    /** {@inheritdoc} */
    protected function getGroups()
    {
        return Api::getModulesGroups();
    }

    /** {@inheritdoc} */
    protected function sortItems($aItems)
    {
        $aItemsByGroups = Api::getModulesGroups();
        $aItemsSort = [];

        // Разнос элементов по отсортированным группам
        foreach ($aItems as &$oItem) {
            if (isset($aItemsByGroups[$oItem->group]) and is_array($aItemsByGroups[$oItem->group])) {
                $aItemsByGroups[$oItem->group][$oItem->id] = $oItem;
            } else {
                $aItemsByGroups[$oItem->group] = [$oItem->id => $oItem];
            }
        }

        // Сбор элементов с групп
        foreach ($aItemsByGroups as &$mItems) {
            if (is_array($mItems)) {
                $aItemsSort += $mItems;
            }
        }

        return $aItemsSort;
    }
}

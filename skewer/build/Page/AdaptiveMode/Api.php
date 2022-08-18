<?php

namespace skewer\build\Page\AdaptiveMode;

use skewer\base\section;

/** Апи для работы с режимом адаптивности */
class Api
{
    /** Название области страницы для блоков адаптивного меню */
    const ADP_MENU_LAYOUT_NAME = 'adaptive_menu';

    /** Название группы параметров блока режима адаптивности и кнопки адаптивного меню */
    const ADP_MENU_BLOCK_MODE = 'AdaptiveMode';
    /** Название группы параметров блока верхнего меню адаптивного меню */
    const ADP_MENU_BLOCK_TOP_MENU = 'adaptive_menu_topMenu';
    /** Название группы параметров блока левого меню адаптивного меню */
    const ADP_MENU_BLOCK_LEFT_MENU = 'adaptive_menu_leftMenu';
    /** Название группы параметров блока каталожного адаптивного меню */
    const ADP_MENU_BLOCK_CATALOG_MENU = 'adaptive_menu_catalogMenu';

    /** Установка/обновление параметра раздела */
    public static function setSectionParam($aData)
    {
        if (!isset($aData['parent']) or !isset($aData['name']) or !isset($aData['group'])) {
            return;
        }

        $oParam = section\Parameters::getByName($aData['parent'], $aData['group'], $aData['name']);
        if (!$oParam) {
            $oParam = section\Parameters::createParam($aData);
        } else {
            $oParam->setAttributes($aData);
        }

        $oParam->save();
    }

    /** Установка/обновление языкового параметра */
    public static function setLanguageParam($aData)
    {
        isset($aData['parent']) or $aData['parent'] = \Yii::$app->sections->languageRoot();
        self::setSectionParam($aData);

        $aData['parent'] = \Yii::$app->sections->tplNew();
        $aData['access_level'] = section\params\Type::paramLanguage;
        $aData['value'] = $aData['show_val'] = '';
        self::setSectionParam($aData);
    }
}

<?php

namespace skewer\generators\page_module;

use skewer\base\site\Layer;
use skewer\components\catalog\Dict;
use skewer\components\catalog\model\FieldTable;
use skewer\generators\view\PrototypeView;
use skewer\generators\view\StringView;

/**
 * Интерфейс для взаимодействия с прототипом
 */
class Api
{
    /**
     * @var array поля, не выводимые пользователю
     */
    public static $aNotShow = ['id', 'alias', 'priority'];

    /**
     * Получение массива прототипов.
     *
     * @param int $nameDict id сущности
     *
     * @return PrototypeView[]
     */
    public static function getArrayPrototypeView($nameDict)
    {
        $idDict = Dict::getDictIdByName($nameDict, Layer::TOOL);
        $aDictField = FieldTable::getFieldById($idDict);
        $aObjectPrototype = [];
        foreach ($aDictField as $aField) {
            $aObjectPrototype[] = self::getViewObject($aField);
        }

        return $aObjectPrototype;
    }

    /**
     * Получение объектов прототипа.
     *
     * @param array $aField
     *
     * @return PrototypeView
     */
    private static function getViewObject($aField)
    {
        $sClassName = '\skewer\generators\view\\' . ucfirst($aField['editor']) . 'View';

        if (!class_exists($sClassName)) {
            $sClassName = StringView::className();
        }

        $oPrototype = new $sClassName($aField);

        if (!$oPrototype instanceof PrototypeView) {
            throw new $sClassName("Класс {$sClassName} должен наследоваться от PrototypeView");
        }

        return $oPrototype;
    }

    /**
     * Получение псевдонимов для использования в модуле.
     *
     * @param string $nameDict - тип данной строки
     *
     * @return array $aUses
     */
    public static function getUses($nameDict)
    {
        $aDictField = self::getArrayPrototypeView($nameDict);
        $aUses = [];
        foreach ($aDictField as $oField) {
            $aUses = array_merge($aUses, $oField->getUses());
        }
        $aUses = array_unique($aUses);

        return $aUses;
    }
}

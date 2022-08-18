<?php

namespace skewer\components\forms\components\handlerType;

use yii\base\UserException;

class Api
{
    /**
     * Функция по получанию объекта типа обработчика по наименованию.
     *
     * @param $nameField
     *
     * @throws UserException
     *
     * @return mixed|Prototype $oClassField
     */
    public static function getClassHandlerType($nameField)
    {
        $sFullPathClass = __NAMESPACE__ . '\\' . $nameField;
        if (class_exists($sFullPathClass)) {
            /**@param Prototype $oClassField */
            $oClassType = new $sFullPathClass();

            return $oClassType;
        }
        throw new UserException('Такого типа обработчика форм не существует');
    }

    /**
     * Виджет для вывода текста для типа обработчика формы.
     *
     * @param $aItem
     * @param $sField
     *
     * @throws \Exception
     *
     * @return string
     */
    public static function getTypeTitle($aItem, $sField)
    {
        $sClassName = __NAMESPACE__ . '\\' . $aItem[$sField];
        if (class_exists($sClassName)) {
            /** @var Prototype $oClass */
            $oClass = new $sClassName();

            return $oClass->getTitle();
        }

        throw new \Exception('Не существует такого типа обработчика [' . $sClassName . ']');
    }
}

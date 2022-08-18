<?php

namespace skewer\components\ext;

use Exception;
use yii\web\ServerErrorHttpException;

/**
 * Вспомогательный класс для автопостроителя.
 *
 * @class: Ext
 * @author: sapozhkov
 */
class Api
{
    /**
     * Инициализация.
     *
     * @static
     */
    public static function init()
    {
        if (!defined('IS_UNIT_TEST') or !IS_UNIT_TEST) {
            \skewer\components\ext\Asset::register(\Yii::$app->view); // сам ExtJS подтянется как зависимый
        }
    }

    /**
     * Отдает объект даботы с полями, формируя его из того, что подали на вход.
     *
     * @param array|field\Prototype $mField данные с описанием элемента
     *
     * @throws Exception
     *
     * @return field\Prototype
     */
    public static function makeFieldObject($mField)
    {
        if (is_object($mField)) {
            // должен быть наследником прототипа Ext поля
            if (!$mField instanceof field\Prototype) {
                throw new Exception('Ошибка создания поля автопостроителя. Объект не является наследником ExtBuilder\\Field\\Prototype');
            }

            return $mField;
        }

        // если массив
        if (is_array($mField)) {
            $sType = $mField['view'] ?? '';
            if ($sType) {
                $sClassName = ExtFT::getPossibleEditorClass($sType);
                if (class_exists($sClassName)) {
                    $oEditor = new $sClassName();
                    if (!$oEditor instanceof field\Prototype) {
                        throw new ServerErrorHttpException(sprintf(
                            'Класс [%s] должен быть унаследован от [%s]',
                            get_class($oEditor),
                            'ext\field\Prototype'
                        ));
                    }

                    $oEditor->setBaseDesc($mField);

                    return $oEditor;
                }
            }

            // создаем объект из массивов
            return new field\ByArray($mField);
        }

        throw new Exception('Ошибка создания поля автопостроителя. Неверный формат даннх инициализации.');
    }
}

Api::init();

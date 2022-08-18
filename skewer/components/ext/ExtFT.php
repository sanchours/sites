<?php

namespace skewer\components\ext;

use skewer\base\ft as ft;
use skewer\base\ui;
use skewer\helpers\Transliterate;

/**
 * Набор общих методов для завязки ft и ext автопостроителя.
 */
class ExtFT
{
    /**
     * Отдает букву типа для запроса по полю.
     *
     * @static
     *
     * @param $oField
     *
     * @return string ( s / i )
     */
    public static function getLetterType(ft\model\Field $oField)
    {
        switch ($oField->getDatatype()) {
            case 'tinyint':
            case 'int': return 'i';
            default: return 's';
        }
    }

    /**
     * Отдает возможное имя класса для редактора поля по имени редактора.
     *
     * @param $sEditorName
     *
     * @return string
     */
    public static function getPossibleEditorClass($sEditorName)
    {
        // это преобразование для php7 там такие имена классов запрещены,
        // поэтому заданные идут с суффоксами
        if (in_array($sEditorName, ['string', 'int', 'float'])) {
            $sEditorName .= '_field';
        }

        return 'skewer\components\ext\field\\' . Transliterate::toCamelCase($sEditorName);
    }

    /**
     * Создает объект поля для вывода в интерфейсе.
     *
     * @param array $aParams
     * @param ft\model\Field $oField
     * @param ft\Model $oModel
     *
     * @throws ft\exception\Model
     *
     * @return field\Prototype
     */
    public static function createFieldByFt($aParams, ft\model\Field $oField, ft\Model $oModel)
    {
        // предположительное имя класса обработчика
        $sClassName = self::getPossibleEditorClass($oField->getEditorName());

        // ищем класс
        if (class_exists($sClassName)) {
            $oIfaceField = new $sClassName();
        } else {
            // проверить - возможно осуществима обработка по умолчанию
            if (!in_array($oField->getEditorName(), self::getSimpleEditorList())) {
                throw new ft\exception\Model(sprintf(
                    'Нет редактора [%s] для поля [%s]',
                    $oField->getEditorName(),
                    $oField->getName()
                ));
            }

            $oIfaceField = new field\ByArray();
        }

        if (!$oIfaceField instanceof field\Prototype) {
            throw new ft\exception\Model(sprintf(
                'Класс [%s] должен быть унаследован от [%s]',
                get_class($oIfaceField),
                'ExtBuilder\Field\Prototype'
            ));
        }

        $oIfaceField->setBaseDesc($aParams);
        $oIfaceField->setDescObj($oField, $oModel);

        return $oIfaceField;
    }

    /**
     * Создает объект поля для вывода в интерфейсе.
     *
     * @param array $aParams
     * @param ui\form\Field $oField
     *
     * @throws ft\exception\Model
     *
     * @return field\Prototype
     */
    public static function createFieldByUi($aParams, ui\form\Field $oField)
    {
        // предположительное имя класса обработчика
        $sClassName = self::getPossibleEditorClass($oField->getEditor());

        // ищем класс
        if (class_exists($sClassName)) {
            $oIfaceField = new $sClassName();
        } else {
            // проверить - возможно осуществима обработка по умолчанию
            if (!in_array($oField->getEditor(), self::getSimpleEditorList())) {
                throw new ft\exception\Model(sprintf(
                    'Нет редактора [%s] для поля [%s]',
                    $oField->getEditor(),
                    $oField->getName()
                ));
            }

            $oIfaceField = new field\ByArray();
        }

        if (!$oIfaceField instanceof field\Prototype) {
            throw new ft\exception\Model(sprintf(
                'Класс [%s] должен быть унаследован от [%s]',
                get_class($oIfaceField),
                'ExtBuilder\Field\Prototype'
            ));
        }

        $oIfaceField->setBaseDesc($aParams);

        return $oIfaceField;
    }

    /**
     * Отдает список простых редакторов, которые
     *  могут быть самостоятельно обработаны ExtJS
     *  без подключения дополнительных компонентов.
     *
     * @return string[]
     */
    protected static function getSimpleEditorList()
    {
        return [
            'show',
            'string',
            'money',
            //'int',
            'float',
            'check',
            'button',
            'hide',
            'text',
            //'date',
            //'time',
            //'datetime',
            'html',
            //'wyswyg',
            //'multiselect',
        ];
    }
}

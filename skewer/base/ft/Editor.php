<?php

namespace skewer\base\ft;

/**
 * Класс для работы с редакторами ft-сущностей
 * Class Editor.
 */
class Editor
{
    // стандартные редакторы
    const INTEGER = 'int';
    const FLOAT = 'float';
    const MONEY = 'money';
    const STRING = 'string';
    const TEXT = 'text';
    const WYSWYG = 'wyswyg';
    const SELECT = 'select';
    const CHECK = 'check';
    const FILE = 'file';
    const GALLERY = 'gallery';
    const DATE = 'date';
    const TIME = 'time';
    const HIDE = 'hide';
    const DATETIME = 'datetime';
    const COLOR = 'colorselector';
    const MAP_SINGLE_MARKER = 'mapSingleMarker';
    const MAP_LIST_MARKER = 'mapListMarkers';
    const DICTCOLLECTION = 'dictcollection';

    // спец редакторы
    const MULTISELECT = 'multiselect';
    const COLLECTION = 'collection';
    const MULTICOLLECTION = 'multicollection';
    const PAYMENTOBJECT = 'paymentObject';
    const SELECTIMAGE = 'selectimage';
    const MULTISELECTIMAGE = 'multiselectimage';

    private static $aEditorList = [
        self::INTEGER => 'int',
        self::FLOAT => 'double',
        self::MONEY => 'decimal',
        self::STRING => 'varchar',
        self::TEXT => 'text',
        self::WYSWYG => 'text',
        self::SELECT => 'int',
        self::CHECK => 'int',
        self::FILE => 'varchar',
        self::GALLERY => 'varchar',
        self::DATE => 'date',
        self::TIME => 'time',
        self::HIDE => 'varchar',
        self::DATETIME => 'datetime',
    ];

    /**
     * Отдает массив пар "псевдоним типа"  => "имя типа".
     *
     * @return array
     */
    public static function getSimpleList()
    {
        $aList = [];

        foreach (self::$aEditorList as $sKey => $aItem) {
            $aList[$sKey] = \Yii::t('ft', 'field_type_' . $sKey);
        }

        return $aList;
    }

    /**
     * Отдает тип редактора для интерфейса.
     *
     * @param string $editor
     *
     * @return string
     */
    public static function getTypeForEditor($editor)
    {
        if ($editor == self::COLLECTION || $editor == self::SELECTIMAGE) {
            return self::INTEGER;
        }
        if ($editor == self::MAP_SINGLE_MARKER) {
            $editor = self::STRING;
        }

        $editor = isset(self::$aEditorList[$editor]) ? $editor : self::STRING;

        return self::$aEditorList[$editor];
    }
}

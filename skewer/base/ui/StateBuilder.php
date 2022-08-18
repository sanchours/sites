<?php

namespace skewer\base\ui;

use skewer\base\ui\builder\FileBuilder;
use skewer\base\ui\builder\FormBuilder;
use skewer\base\ui\builder\ListBuilder;
use skewer\base\ui\builder\Prototype;
use skewer\base\ui\builder\ShowBuilder;
use skewer\build\Cms;
use skewer\components\ext;

/**
 * Сборщик интерфейсных моделей.
 *
 * @deprecated используйте классы \skewer\components\ext\view
 */
class StateBuilder
{
    /**
     * Отправляет в интерфейс команду на перестрение одной строки в списке.
     *
     * @param Cms\Tabs\ModulePrototype $oModule модуль в котором отстраивается интерфейс
     * @param array $aData данные
     * @param array|string $mSearchFields имя колонки (или набор) по которой будет произведено сравнение
     *
     * @return ext\ListRows
     */
    public static function updRow(Cms\Tabs\ModulePrototype $oModule, $aData, $mSearchFields = 'id')
    {
        $oListVal = new ext\ListRows();
        $oListVal->setSearchField($mSearchFields);
        $oListVal->addDataRow($aData);
        $oListVal->setData($oModule);

        return $oListVal;
    }

    /**
     * Создание нового списки.
     *
     * @param null|Prototype $oInterface
     *
     * @return ListBuilder
     */
    public static function newList($oInterface = null)
    {
        return new ListBuilder($oInterface);
    }

    /**
     * Создание новой формы редактирования.
     *
     * @param null|Prototype $oInterface
     *
     * @return FormBuilder
     */
    public static function newEdit($oInterface = null)
    {
        return new FormBuilder($oInterface);
    }

    /**
     * Создания новой формы работы с файлами.
     *
     * @param string $sLibName
     * @param null $oInterface
     *
     * @return FileBuilder
     */
    public static function newFile($sLibName, $oInterface = null)
    {
        return new FileBuilder($sLibName, $oInterface);
    }

    /**
     * Создания новой формы для вывода текстовых наборов данных
     * (не редактируемых пар название-значение).
     *
     * @param null $oInterface
     *
     * @return ShowBuilder
     */
    public static function newShow($oInterface = null)
    {
        return new ShowBuilder($oInterface);
    }
}

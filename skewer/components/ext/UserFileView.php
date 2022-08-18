<?php

namespace skewer\components\ext;

use skewer\build\Cms;

/**
 * Класс для автоматической сборки админских интерфейсов на ExtJS.
 *
 * Эта библиотека служит для построения интерфейса из пользовательских файлов
 * Собственного JS файла ни имеет
 *
 * @class: ExtUser
 *
 * @Author: User, $Author$
 * @version: $Revision$
 * @date: $Date$
 */
class UserFileView extends ViewPrototype
{
    /**
     * Конструктор объекта.
     *
     * @param $sLibName - имя основного пользовательского JS файла
     */
    public function __construct($sLibName)
    {
        $this->setLibName($sLibName);
    }

    /**
     * @var string - Имя основной библиотеки
     */
    protected $sLibName = '';

    /**
     * Возвращает имя основной библиотеки.
     *
     * @return string
     */
    public function getLibName()
    {
        return $this->sLibName;
    }

    /**
     * Устанавливает имя основной библиотеки.
     *
     * @param string $sLibName
     */
    public function setLibName($sLibName)
    {
        $this->sLibName = $sLibName;
    }

    /**
     * Возвращает имя компонента.
     *
     * @return string
     */
    public function getComponentName()
    {
        return '';
    }

    /**
     * Отдает интерфейсный массив для атопостроителя интерфейсов.
     *
     * @return array
     */
    public function getInterfaceArray()
    {
        // выходной массив
        $aOut = [
            'componentName' => $this->getLibName(),
        ];

        // вывод данных
        return $aOut;
    }

    /**
     * Задает инициализационный  массив для атопостроителя интерфейсов.
     *
     * @param Cms\Frame\ModulePrototype $oModule - ссылка на вызвавший объект
     */
    public function setInterfaceData(Cms\Frame\ModulePrototype $oModule)
    {
        $oModule->addLibClass($this->getLibName());

        parent::setInterfaceData($oModule);
    }
}

<?php

namespace skewer\build\Adm\Files;

use skewer\components\ext;

/**
 * Class ExtListModule.
 */
class ExtListModule extends ext\ListView
{
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
     * Собирает интерфейсный массив для выдачи в JS.
     *
     * @return array
     */
    public function getInterfaceArray()
    {
        // забпосить результат работы родительской функции
        $aValues = parent::getInterfaceArray();

        // убрать имя стандартного компонента
        unset($aValues['extComponent']);

        // добавить имя специфического компонента
        $aValues['componentName'] = 'FileBrowserFiles';

        // добавить стандартный компонент как подключенный модуль,
        //  поскольку он будет использоваться
        $this->addComponent('List');

        // вернуть результирующий массив
        return $aValues;
    }
}

<?php

namespace skewer\components\ext\field;

/**
 * Класс для работы с полями автопостроителя.
 */
class ByArray extends Prototype
{
    /**
     * Конструктор для первичной инициализации в момент создания.
     *
     * @param array $aBaseDesc
     * @param array $aAddDesc
     */
    public function __construct(array $aBaseDesc = null, array $aAddDesc = null)
    {
        // если есть базовое описание
        if ($aBaseDesc !== null) {
            // инициализировать базовое описание
            $this->setBaseDesc($aBaseDesc);

            // если есть дополнительные параметры
            if ($aAddDesc !== null) {
                // инициализировать дополнительные параметры
                $this->setAddDesc($aAddDesc);
            }
        }
    }

    public function getView()
    {
        return (string) $this->getDescVal('view');
    }
}

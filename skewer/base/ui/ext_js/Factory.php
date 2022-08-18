<?php

namespace skewer\base\ui\ext_js;

use skewer\base\ui;
use skewer\components\ext;

/**
 * Фабрика интерфейсных объектов для ExtJS.
 */
class Factory implements ui\FactoryInterface
{
    /**
     * Отдает объект для построения спискового интерфейса.
     *
     * @return ui\state\ListInterface
     */
    public function getList()
    {
        return new ext\ListView();
    }

    /**
     * Отдает объект для построения интерфейса редактирования.
     *
     * @return ui\state\EditInterface
     */
    public function getEdit()
    {
        return new ext\FormView();
    }

    /**
     * Отдает объект для построения интерфейса отображения данных.
     *
     * @return ui\state\ShowInterface
     */
    public function getShow()
    {
        return new ext\ShowView();
    }
}

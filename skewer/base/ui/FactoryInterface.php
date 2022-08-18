<?php

namespace skewer\base\ui;

use skewer\base\ui;

/**
 * Фабрика интерфейсов
 * Class Factory.
 */
interface FactoryInterface
{
    /**
     * Отдает объект для построения спискового интерфейса.
     *
     * @return ui\state\ListInterface
     */
    public function getList();

    /**
     * Отдает объект для построения интерфейса редактирования.
     *
     * @return ui\state\EditInterface
     */
    public function getEdit();

    /**
     * Отдает объект для построения интерфейса отображения данных.
     *
     * @return ui\state\ShowInterface
     */
    public function getShow();
}

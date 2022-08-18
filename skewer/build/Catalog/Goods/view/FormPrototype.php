<?php

namespace skewer\build\Catalog\Goods\view;

use skewer\components\ext\view\FormView;

/**
 * Прототип построителя интерфейса редактирования товара
 * Class ListPrototype.
 */
abstract class FormPrototype extends FormView
{
    /** @var \skewer\build\Catalog\Goods\model\FormPrototype $model */
    public $model;

    /** @var \skewer\build\Catalog\Goods\Module Модуль в котором осуществляется вывод интерфейса */
    protected $_module;
}

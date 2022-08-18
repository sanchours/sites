<?php

namespace skewer\components\ext\view;

use skewer\base\ui\builder\ListBuilder;
use skewer\components\ext;

/**
 * Прототип вида типа "Список" для админских модулей.
 */
abstract class ListView extends Prototype
{
    /** @var int номер текущей страницы */
    public $page = 0;

    /** @var int количество элементов на страницу */
    public $onPage = 0;

    /** @var int общее число записей */
    public $total = 0;

    /** @var \skewer\base\ui\builder\ListBuilder объект построителя списковых интерфейсов */
    protected $_list;

    /**
     * {@inheritdoc}
     */
    public function __construct(array $config)
    {
        parent::__construct($config);

        $this->_list = new ListBuilder();
    }

    /**
     * Отдает объект построителя интерфейсов.
     *
     * @return \skewer\base\ui\builder\ListBuilder
     */
    protected function getBuilder()
    {
        return $this->_list;
    }

    /**
     * Отдает объект построителя интерфейсов.
     *
     * @return ext\ListView
     */
    final public function getInterface()
    {
        return $this->_list->getForm();
    }
}

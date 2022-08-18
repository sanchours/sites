<?php

namespace skewer\components\ext\view;

use skewer\base\ui\builder\IframeBuilder;

abstract class IframeView extends Prototype
{
    /** @var \skewer\base\ui\builder\IframeBuilder объект построителя интерфейсов */
    protected $_form;

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->_form = new IframeBuilder();
    }



    /**
     * Отдает объект построитель интерфейса.
     *
     * @return \skewer\base\ui\state\BaseInterface|\skewer\components\ext\FormView
     */
    public function getInterface()
    {
        return $this->_form->getForm();
    }
}

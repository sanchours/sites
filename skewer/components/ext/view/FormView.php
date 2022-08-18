<?php
/**
 * Created by PhpStorm.
 * User: Александр
 * Date: 29.07.2016
 * Time: 13:38.
 */

namespace skewer\components\ext\view;

use skewer\base\ui\builder\FormBuilder;

abstract class FormView extends Prototype
{
    /** @var \skewer\base\ui\builder\FormBuilder объект построителя интерфейсов */
    protected $_form;

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->_form = new FormBuilder();
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

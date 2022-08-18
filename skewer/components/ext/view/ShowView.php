<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 28.02.2017
 * Time: 18:25.
 */

namespace skewer\components\ext\view;

use skewer\base\ui\builder\ShowBuilder;

abstract class ShowView extends Prototype
{
    /** @var \skewer\base\ui\builder\ShowBuilder объект построителя интерфейсов */
    protected $_form;

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->_form = new ShowBuilder();
    }

    /**
     * Отдает объект построитель интерфейса.
     *
     * @return \skewer\base\ui\state\BaseInterface
     */
    public function getInterface()
    {
        return $this->_form->getForm();
    }
}

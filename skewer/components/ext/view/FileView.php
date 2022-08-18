<?php
/**
 * Created by PhpStorm.
 * User: holod
 * Date: 27.02.2017
 * Time: 19:07.
 */

namespace skewer\components\ext\view;

use skewer\base\ui\builder\FileBuilder;

abstract class FileView extends Prototype
{
    /** @var \skewer\base\ui\builder\FileBuilder объект построителя интерфейсов */
    protected $_form;

    public function __construct(array $config = [])
    {
        parent::__construct($config);

        $this->_form = new FileBuilder($this->getLibFileName());
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

    abstract protected function getLibFileName();
}

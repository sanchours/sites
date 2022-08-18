<?php

namespace skewer\build\Tool\Payments;

class Form
{
    /** @var string Метод */
    private $method = 'POST';

    /** @var string action */
    private $action = '';

    /** @var array Поля */
    private $aFields = [];

    /**
     * Задание списка полей формы.
     *
     * @param array $aFields
     */
    public function setFields($aFields)
    {
        $this->aFields = $aFields;
    }

    /**
     * Получаем список полей.
     *
     * @return array
     */
    public function getFields()
    {
        return $this->aFields;
    }

    /**
     * Задание страницы отправки.
     *
     * @param string $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
}

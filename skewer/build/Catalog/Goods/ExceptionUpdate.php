<?php

namespace skewer\build\Catalog\Goods;

/**
 * Исключение при ошибке изменения данных update / insert / delete
 * Class ExceptionUpdate.
 */
class ExceptionUpdate extends Exception
{
    /**
     * Набор пар ошибок "имя поля" => "текст ошибки".
     *
     * @var array
     */
    private $aErrorList = [];

    /**
     * Конструктор исключения.
     *
     * @param string $sMessage
     * @param array $aErrorList
     */
    public function __construct($sMessage, $aErrorList = [])
    {
        $this->aErrorList = $aErrorList;
        parent::__construct($sMessage);
    }

    /**
     * Отдает набор ошибок.
     *
     * @return array
     */
    public function getErrorList()
    {
        return $this->aErrorList;
    }
}

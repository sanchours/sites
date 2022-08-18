<?php

namespace unit\components\config;

use skewer\components\config\Exception;
use skewer\components\config\UpdatePrototype;

/**
 * Основная конфигурация сайта.
 *
 * @deprecated разогнать по компонентам все конфиги
 */
class ConfigTestClass extends UpdatePrototype
{
    /** @var array первично загруженные данные */
    private $aBaseData = [];

    /**
     * @param $aData
     */
    public function __construct($aData)
    {
        $this->aBaseData = $aData;
        parent::__construct();
    }

    /**
     * Загружает набор данных.
     *
     * @throws Exception
     */
    protected function loadData()
    {
        $this->aData = $this->aBaseData;
    }

    /**
     * Отдает вектор шифрования.
     *
     * @return string
     */
    public function getSecurityVector()
    {
        return $this->get('security.vector');
    }

    /**
     * Сохраняет изменения в базу
     * Запрещенный для вызова метод. выкидывает исключение через
     * вызов обязательного метода сохранения.
     *
     * @throws Exception
     *
     * @return bool
     */
    public function commitChanges()
    {
        return $this->saveData();
    }

    /**
     * Сохраняет данные.
     *
     * @throws Exception
     *
     * @return bool
     */
    protected function saveData()
    {
        throw new Exception('Call of deprected method for saving changed data in config');
    }
}

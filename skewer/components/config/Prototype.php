<?php

namespace skewer\components\config;

/**
 * Прототип конфигурационных файлов.
 */
abstract class Prototype
{
    /**
     * Набор конфигурационных данных.
     *
     * @var array
     */
    protected $aData;

    /**
     * Разделитель.
     *
     * @var string
     */
    protected $sPathDelimiter = '.';

    /**
     * Создает объект, проводит инициализацию.
     */
    public function __construct()
    {
        $this->loadData();
    }

    /**
     * Отдает значение по имени или NULL, если не найдено.
     *
     * @param array|string $mName
     *
     * @throws Exception
     *
     * @return null|mixed
     */
    public function get($mName)
    {
        if ($this->aData === null) {
            throw new Exception('Config file not loaded');
        }
        if (is_array($mName)) {
            return $this->getByArray($mName);
        }

        if (!is_string($mName)) {
            throw new Exception('`Name` must be a string or an array');
        }

        return $this->getByArray(explode($this->getDelimiter(), $mName));
    }

    /**
     * Отдает флаг наличия значения.
     *
     * @param array|string $mName
     *
     * @return bool
     */
    public function exists($mName)
    {
        return $this->get($mName) !== null;
    }

    /**
     * Отдает данные по массиву ключей.
     *
     * @param $aNameItems
     *
     * @return null|array
     */
    private function getByArray($aNameItems)
    {
        $pConfig = &$this->aData;

        // спуститься по набору имен
        foreach ($aNameItems as $sSubName) {
            if (!is_array($pConfig)) {
                return;
            }

            if (!isset($pConfig[$sSubName])) {
                return;
            }

            $pConfig = &$pConfig[$sSubName];
        }

        // вернуть то, до чего дошли
        return $pConfig;
    }

    /**
     * Загружаем данные во внутренний массив.
     *
     * @param array $aData
     */
    protected function setData($aData)
    {
        $this->aData = $aData;
    }

    /**
     * Отдает данные конфигурации
     * вне конфигов можно использовать для отладки
     * для работы с контентом нужно задать метод.
     *
     * @return array
     */
    public function getData()
    {
        return $this->aData;
    }

    /**
     * Загружает набор данных.
     */
    abstract protected function loadData();

    /**
     * Отдает символ "разделитель" для путей.
     *
     * @return string
     */
    public function getDelimiter()
    {
        return $this->sPathDelimiter;
    }

    /**
     * Перезагружает данные из источников заново.
     */
    public function reloadData()
    {
        // сброс значений
        $this->setData([]);

        // повторная загрузка
        $this->loadData();
    }
}

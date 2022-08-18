<?php

namespace skewer\components\config;

/**
 * Прототип конфигурационных файлов с возможностью записи и сохранения.
 */
abstract class UpdatePrototype extends Prototype
{
    /**
     * Флаг наличия изменений.
     *
     * @var bool
     */
    private $bHasChanges = false;

    /**
     * Устанавливает значение ключа по пути (с возможностью добавить свой (int,str,array,bool)).
     *
     * @param mixed $mName Путь до параметра конфигурации
     * @param mixed $mValue Значение параметра конфигурации
     *
     * @throws Exception
     *
     * @return bool Возвращает true в случае добавления значения либо false в случае неудачи
     */
    public function set($mName, $mValue)
    {
        if (empty($mName)) {
            return false;
        }

        $this->setHasChanges();

        if ($this->aData === null) {
            throw new Exception('Config file not loaded');
        }
        if (is_array($mName)) {
            return $this->setByArray($mName, $mValue);
        }

        if (!is_string($mName)) {
            throw new Exception('`Name` must be a string or an array');
        }

        return $this->setByArray(explode($this->getDelimiter(), $mName), $mValue);
    }

    /**
     * Добавляет элемент в массив по пути. Если переменна пуста,
     * то создает массив и добавляет туда.
     *
     * @param string $mName Путь до параметра конфигурации
     * @param string $mValue Значение параметра конфигурации
     *
     * @throws Exception
     *
     * @return bool Возвращает true в случае добавления значения либо false в случае неудачи
     */
    public function append($mName, $mValue)
    {
        $mData = $this->get($mName);

        if ($mData === null) {
            $mData = [];
        }

        if (!is_array($mData)) {
            throw new Exception('Can append only to array');
        }
        $mData[] = $mValue;

        return $this->set($mName, $mData);
    }

    /**
     * Задает значение в контейнер по набору меток.
     *
     * @param array $aNameItems
     * @param mixed $mValue
     *
     * @throws Exception
     *
     * @return bool
     */
    private function setByArray($aNameItems, $mValue)
    {
        $aConfigLink = &$this->aData;

        $sPrevKey = '-';
        foreach ($aNameItems as $sKeyName) {
            if (!is_array($aConfigLink)) {
                $sPath = implode($this->getDelimiter(), $aNameItems);
                throw new Exception("Config key [{$sPath}] cannot be reached: [{$sPrevKey}] is not an array");
            }
            if (!isset($aConfigLink[$sKeyName])) {
                $aConfigLink[$sKeyName] = [];
            }
            $aConfigLink = &$aConfigLink[$sKeyName];
            $sPrevKey = $sKeyName;
        }

        $aConfigLink = $mValue;

        return true;
    }

    /**
     * Удаляет ключ конфигурации $key.
     *
     * @static
     *
     * @param array|string $mName имя удаляемого ключа
     *
     * @throws Exception
     *
     * @return bool
     */
    public function remove($mName)
    {
        if (empty($mName)) {
            return false;
        }

        $this->setHasChanges();

        if ($this->aData === null) {
            throw new Exception('Config file not loaded');
        }
        if (is_array($mName)) {
            return $this->removeByArray($mName);
        }

        if (!is_string($mName)) {
            throw new Exception('`Name` must be a string or an array');
        }

        return $this->removeByArray(explode($this->getDelimiter(), $mName));
    }

    /**
     * Отдает данные по массиву ключей.
     *
     * @param array $aNameItems
     *
     * @return bool
     */
    private function removeByArray($aNameItems)
    {
        $aConfigLink = &$this->aData;

        $iLast = count($aNameItems) - 1;

        $bRes = false;

        foreach (array_values($aNameItems) as $iKey => $sItem) {
            if ($iKey === $iLast) {
                $bRes = isset($aConfigLink[$sItem]);
                if ($bRes) {
                    unset($aConfigLink[$sItem]);
                }
            } else {
                $aConfigLink = &$aConfigLink[$sItem];
            }
        }

        return $bRes;
    }

    /**
     * Отдает флаг наличия изменений.
     *
     * @return bool
     */
    public function hasChanges()
    {
        return $this->bHasChanges;
    }

    /**
     * Устанавливает флаг наличия изменений.
     *
     * @param bool $bVal
     */
    protected function setHasChanges($bVal = true)
    {
        $this->bHasChanges = (bool) $bVal;
    }

    /**
     * Сохраняет изменения в базу.
     *
     * @return bool
     */
    public function commitChanges()
    {
        if (!$this->hasChanges()) {
            return false;
        }

        $this->saveData();

        $this->setHasChanges(false);

        return true;
    }

    /**
     * Сохраняет данные.
     *
     * @return bool
     */
    abstract protected function saveData();

    /**
     * Откатывает назад изменения, если они были сделаны.
     */
    public function revertChanges()
    {
        if (!$this->hasChanges()) {
            return;
        }

        $this->setHasChanges(false);

        $this->reloadData();
    }

    /**
     * Очищает данные модуля
     * Удаляет все записи.
     */
    public function clear()
    {
        $this->setHasChanges();
        $this->aData = [];
    }
}

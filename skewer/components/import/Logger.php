<?php

namespace skewer\components\import;

use skewer\components\import\ar\Log;
use skewer\components\import\ar\LogRow;

/**
 * Логгер для импорта.
 */
class Logger
{
    /** @var int Задача */
    private $iTask = 0;

    /** @var int Шаблон */
    private $iTpl = 0;

    /** @var array Хранилище данных */
    private $aStorage = [];

    /** @var array список сохраненных пунктов */
    private $saved = [];

    public function __construct($iTask, $iTpl)
    {
        $this->iTask = $iTask;
        $this->iTpl = $iTpl;

        $aParams = Log::find()
            ->where('task', $iTask)
            ->where('tpl', $iTpl)
            ->where('saved', 0)
            ->order('id')
            ->asArray()
            ->getAll();

        foreach ($aParams as $aParam) {
            if (!$aParam['list']) {
                $this->aStorage[$aParam['name']] = $aParam['value'];
            } else {
                $this->aStorage[$aParam['name']][] = $aParam['value'];
            }
        }
    }

    /**
     * Установка параметра.
     *
     * @param $name
     * @param $value
     */
    public function setParam($name, $value)
    {
        $this->aStorage[$name] = $value;
    }

    /**
     * Получение параметра
     * @param $name
     * @param string $default
     * @return bool|mixed
     */
    public function getParam($name, $default = '')
    {
        if (isset($this->aStorage[$name])) {
            return $this->aStorage[$name];
        }

        return $default;
    }

    /**
     * Увеличение параметра.
     *
     * @param $name
     */
    public function incParam($name)
    {
        $this->aStorage[$name] = (isset($this->aStorage[$name])) ? (int) $this->aStorage[$name] + 1 : 1;
    }

    /**
     * Добавление значения в список.
     *
     * @param $name
     * @param $value
     */
    public function setListParam($name, $value)
    {
        if (!isset($this->aStorage[$name])) {
            $this->aStorage[$name] = [];
        }

        if (!is_array($this->aStorage[$name])) {
            $this->aStorage[$name] = [$this->aStorage[$name]];
        }

        $this->aStorage[$name][] = $value;
    }

    /**
     * Список сохраненных пунктов.
     *
     * @param array $saved
     */
    public function setSaved(array $saved)
    {
        if (is_array($saved)) {
            $this->saved = $saved;
        }
    }

    /**
     * Сохранение логов.
     */
    public function save()
    {
        foreach ($this->aStorage as $key => $param) {
            if (is_array($param)) {
                $bSaved = 1;
                if (!in_array($key, $this->saved)) {
                    //удаляем старые
                    Log::delete()
                        ->where('task', $this->iTask)
                        ->where('tpl', $this->iTpl)
                        ->where('name', $key)
                        ->get();
                    $bSaved = 0;
                }

                //пишем новые
                foreach ($param as $value) {
                    //наверное не самый оптимальный способ для добавления множества записей
                    Log::getNewRow([
                            'task' => $this->iTask, 'tpl' => $this->iTpl, 'name' => $key,
                            'value' => $value, 'saved' => $bSaved, 'list' => 1,
                        ])->save();
                }
            } else {
                Log::delete()
                    ->where('task', $this->iTask)
                    ->where('tpl', $this->iTpl)
                    ->where('name', $key)
                    ->get();

                /** @var LogRow $oRowLog */
                $oRowLog = Log::getNewRow(['task' => $this->iTask, 'tpl' => $this->iTpl]);

                $oRowLog->name = $key;
                $oRowLog->value = $param;

                //сохраняем
                $oRowLog->save();
            }
        }
    }
}

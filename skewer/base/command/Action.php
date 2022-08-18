<?php
/**
 * Реализация прототипа Команда (Command).
 */

namespace skewer\base\command;

abstract class Action implements ActionInterface
{
    /** @var Hub */
    protected $oHub;

    /** @var bool флаг включения в концентратор */
    protected $bInHub = false;

    /** @var bool флаг инициализации */
    protected $bInited = false;

    /**
     * Инициализация
     * Добавление слушателей событий.
     */
    abstract protected function init();

    /**
     * Устанавливает концентратор для команд.
     *
     * @param Hub $oHub
     *
     * @throws Exception
     *
     * @return mixed
     */
    protected function setHub(Hub $oHub)
    {
        if ($this->bInHub) {
            throw new Exception('double add command to hub');
        }
        $this->bInHub = true;
        $this->oHub = $oHub;

        if ($this->bInited) {
            throw new Exception('double add command to hub');
        }
        $this->bInited = true;
        $this->init();
    }

    /**
     * Отдает экземпляр концентратора.
     *
     * @throws Exception
     *
     * @return null|Hub
     */
    protected function getHub()
    {
        if (!$this->oHub) {
            throw new Exception('No Hub defined');
        }

        return $this->oHub;
    }

    /**
     * Добавление прослушивания событий.
     *
     * @param int $iEvent идентификатор события
     * @param string $sMethodName имя метода для вызова
     */
    protected function listenTo($iEvent, $sMethodName)
    {
        $this->getHub()->addListener($iEvent, [$this, $sMethodName]);
    }

    /**
     * Отсылает сообщение всем
     *
     * @param int $iEvent
     * @param array|mixed $aData массивом набор параметров для вызова
     *          если параметр один, то можно просто значение
     */
    public function notify($iEvent, $aData = null)
    {
        $this->getHub()->notify($iEvent, $aData);
    }
}

<?php

namespace skewer\base\command;

/**
 * Концентратор прототипа "Комманда" (Command).
 */
class Hub extends Action implements \Iterator
{
    /** @var Action[] набор комманд для выполнения */
    protected $aCommandList = [];

    /** @var \Exception последняя ошибка */
    protected $oError;

    /** @var int индекс текущей команды */
    protected $iIndex = 0;

    /** @var array список слушателей событий */
    protected $aListeners = [];

    /** @var bool флаг запуска выполенения */
    protected $bExecuted = false;

    /**
     * Инициализация
     * Добавление слушателей событий.
     */
    protected function init()
    {
    }

    /**
     * Отдает экземпляр концентратора.
     *
     * @return null|Hub
     */
    protected function getHub()
    {
        return $this->oHub;
    }

    /**
     * Проверяет есть ли родительский концентратор
     *
     * @return bool
     */
    protected function hasParentHub()
    {
        return $this->oHub !== null;
    }

    /**
     * Добавлени команды в список.
     *
     * @param Action $oCommand
     */
    public function addCommand(Action $oCommand)
    {
        $oCommand->setHub($this);
        $this->aCommandList[] = $oCommand;
    }

    /**
     * Добавление набора команд в список для выполнения.
     *
     * @param Action[] $aList
     *
     * @throws Exception
     */
    public function addCommandList($aList)
    {
        if (!is_array($aList)) {
            throw new Exception('Hub::addCommandList input val must be an array of Action');
        }
        foreach ($aList as $oCommand) {
            $this->addCommand($oCommand);
        }
    }

    /**
     * Выполнение набора комманд.
     *
     * @throws Exception
     *
     * @return bool
     */
    public function execute()
    {
        // проверка на повторный запуск
        if ($this->bExecuted) {
            throw new Exception('Action Hub double execute');
        }
        $this->bExecuted = true;

        // если нет родительского концентратора - инициализировать
        if (!$this->bInited) {
            $this->init();
            $this->bInited = true;
        }

        try {
            for ($this->rewind(); $this->valid(); $this->next()) {
                $this->current()->execute();
            }
        } catch (\Exception $e) {
            $this->oError = $e;
            $this->rollback();
            // если есть родительский концентратор - откатить и его
            if ($this->hasParentHub()) {
                throw new Exception(
                    $e->getMessage(),
                    $e->getCode(),
                    $e
                );
            }

            return false;
        }

        return true;
    }

    /**
     * Пытается выполнить набор команд. В случае исключения
     * пробрасывает его дальше.
     *
     * @throws \Exception
     * @throws null
     */
    public function executeOrExcept()
    {
        if (!$this->execute()) {
            throw $this->getError();
        }
    }

    /**
     * Откат примененных изменений.
     */
    public function rollback()
    {
        // выполнить откат для выполненных команд
        for ($this->prev(); $this->valid(); $this->prev()) {
            $this->current()->rollback();
        }
    }

    /**
     * Отдает последнюю ошибку.
     *
     * @return null|\Exception
     */
    public function getError()
    {
        return $this->oError;
    }

    /**
     * Отсылает сообщение всем
     *
     * @param int $iEvent
     * @param array|mixed $aData
     */
    public function notify($iEvent, $aData = null)
    {
        // если есть родительский
        if ($this->hasParentHub()) {
            // попросить его разослать уведомление
            $this->getHub()->notify($iEvent, $aData);
        } else {
            // иначе разослать уведомления всем наследникам
            $this->fireEvent($iEvent, $aData);
        }
    }

    /**
     * Добавить прослушивающий элемент к событию.
     *
     * @param int $iEvent идентификатор события
     * @param callable $mCallback
     *
     * @throws Exception
     */
    public function addListener($iEvent, $mCallback)
    {
        if (!is_callable($mCallback)) {
            throw new Exception('Try to add not valid callback on event');
        }
        // сохранить в набор прослушивающих
        $this->aListeners[$iEvent][] = $mCallback;
    }

    /**
     * Вызов обработчика собития.
     *
     * @param int $iEvent
     * @param array $aData
     *
     * @return bool
     */
    protected function fireEvent($iEvent, $aData)
    {
        if (array_key_exists($iEvent, $this->aListeners)) {
            if (!is_array($aData)) {
                $aData = [$aData];
            }

            // у всех наблюдателей события вызвать обработчики
            foreach ($this->aListeners[$iEvent] as $rCallback) {
                call_user_func_array($rCallback, $aData);
            }
        }

        // вызвать обработчик у всех подчиненных концентраторов
        foreach ($this->aCommandList as $oCommand) {
            if ($oCommand instanceof Hub) {
                $oCommand->fireEvent($iEvent, $aData);
            }
        }
    }

    /**
     * Return the current element.
     *
     * @see http://php.net/manual/en/iterator.current.php
     *
     * @return null|Action
     *
     * @since 5.0.0
     */
    public function current()
    {
        return $this->aCommandList[$this->key()];
    }

    /**
     * Move forward to next element.
     *
     * @see http://php.net/manual/en/iterator.next.php
     * @since 5.0.0
     */
    public function next()
    {
        ++$this->iIndex;
    }

    /**
     * Return the key of the current element.
     *
     * @see http://php.net/manual/en/iterator.key.php
     *
     * @return mixed scalar on success, or null on failure
     *
     * @since 5.0.0
     */
    public function key()
    {
        return $this->iIndex;
    }

    /**
     * Checks if current position is valid.
     *
     * @see http://php.net/manual/en/iterator.valid.php
     *
     * @return bool The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     *
     * @since 5.0.0
     */
    public function valid()
    {
        return isset($this->aCommandList[$this->key()]);
    }

    /**
     * Rewind the Iterator to the first element.
     *
     * @see http://php.net/manual/en/iterator.rewind.php
     * @since 5.0.0
     */
    public function rewind()
    {
        $this->iIndex = 0;
    }

    /**
     * Move to previous element.
     */
    public function prev()
    {
        --$this->iIndex;
    }
}

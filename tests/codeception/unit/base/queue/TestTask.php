<?php

namespace unit\base\queue;

use skewer\base\queue;
use skewer\base\SysVar;

class TestTask extends queue\Task
{
    protected $iCount = 0;

    protected $iId = 0;

    public function init()
    {
        $args = func_get_args();

        $this->iCount = $args[0]['param'] ?? 0;
        $this->iId = $args[0]['id'] ?? 0;
    }

    public function recovery()
    {
        $args = func_get_args();

        $this->iCount = $args[0]['param'] ?? 0;
        $this->iId = $args[0]['id'] ?? 0;
    }

    /**
     * Выполнение задачи.
     */
    public function execute()
    {
        ++$this->iCount;

        if ($this->iId === 2) {
            $this->setStatus(queue\Task::stError);
        }

        SysVar::set('TestTask.Param', $this->iCount);
        SysVar::set('TestTask.id', $this->iId);

        if ($this->iCount >= 3) {
            $this->setStatus(static::stComplete);
        }
    }

    public function reservation()
    {
        $this->setParams(['param' => $this->iCount]);
    }

    public function error()
    {
        SysVar::set('TestTask.error', $this->iId);
    }
}

<?php

namespace unit\base\queue;

use skewer\base\SysVar;

class TimeTask extends TestTask
{
    /** Время задержки на итерацию */
    const time = 20;

    /**
     * Выполнение задачи.
     */
    public function execute()
    {
        ++$this->iCount;

        SysVar::set('TestTask.Param', $this->iCount);

        if ($this->iCount >= 3) {
            $this->setStatus(static::stComplete);
        }

        sleep(static::time);
    }
}

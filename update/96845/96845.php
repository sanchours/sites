<?php

use skewer\base\queue\ar\Schedule;
use skewer\components\auth\DeleteExpiredTask;
use skewer\components\config\PatchPrototype;

class Patch96845 extends PatchPrototype
{
    public $sDescription = 'Создание задачи на удаление истекших аккаунтов';
    public $bUpdateCache = true;

    /**
     * @return bool|void
     */
    public function execute()
    {
        if (!Schedule::findOne(['name' => DeleteExpiredTask::SCHEDULE_TASK_NAME])) {
            $command = [
                'class' => 'skewer\components\auth\DeleteExpiredTask',
                'parameters' => [],
            ];

            $data = [
                'title' => 'Удаление неактивированных аккаунтов',
                'name' => DeleteExpiredTask::SCHEDULE_TASK_NAME,
                'command' => json_encode($command),
                'priority' => '1',
                'resource_use' => '7',
                'target_area' => '3',
                'status' => '1',
                'c_min' => '0',
                'c_hour' => '0',
                'c_day' => null,
                'c_month' => null,
                'c_dow' => null,
            ];

            $scheduleTask = new Schedule($data);
            $scheduleTask->save();
        }
    }
}

<?php

namespace skewer\controllers;

use skewer\base\queue\Api;
use skewer\base\queue\ar\Schedule as ScheduleAr;
use skewer\base\queue\Manager;
use skewer\base\SysVar;
use skewer\build\Tool\Schedule;

class CronController extends Prototype
{
    public function actionIndex()
    {
        if (random_int(1, 10) < 3) {
            Api::collectGarbage();
        }

        // этот класс прописан в инсталляторе install_23.php
        class_alias('\skewer\components\seo\SitemapTask', '\skewer\build\Component\SEO\SitemapTask');

        // добавление задач из планировщика

        $iStartTime = time();

        /**
         * отсечение секунд, т.к. при старте крона не с нулевой секунды,
         * может пропускаться последняя итерация для заданного временного промежутка.
         */
        $iStartTime = $iStartTime - ($iStartTime % 60);
        $iLastStartTime = (int) SysVar::get('SheduleLastStartTime');

        $aScheduleItems = ScheduleAr::find()
            ->where(['status' => Schedule\Api::iStatusActive])
            ->orderBy('title')
            ->asArray()
            ->all();

        $iCnt = 0;

        for ($iCurTime = $iLastStartTime + 60; $iCurTime <= $iStartTime; $iCurTime += 60) {
            if (++$iCnt > 100) {
                break;
            }

            $sCurTime = date('i:H:d:m:w', $iCurTime);
            $aCurTime = explode(':', $sCurTime);

            foreach ($aScheduleItems as $aCurTask) {
                if (\skewer\components\Cron\Api::runTask($aCurTask, $aCurTime)) {
                    $aData = [
                        'title' => $aCurTask['title'],
                        'priority' => $aCurTask['priority'],
                        'resource_use' => $aCurTask['resource_use'],
                        'target_area' => $aCurTask['target_area'],
                    ];

                    $command = json_decode($aCurTask['command'], true);
                    //Если указан метод - запустим универсальную задачу на выполнение одного метода
                    if (isset($command['method'])) {
                        $aData['class'] = '\skewer\base\queue\MethodTask';
                        $aData['parameters'] = [
                            'parameters' => $command['parameters'],
                            'class' => $command['class'],
                            'method' => $command['method'],
                        ];
                    } else {
                        $aData['class'] = $command['class'];
                        $aData['parameters'] = $command['parameters'];
                    }

                    Api::addTask(
                        $aData
                    );
                }
            }
        }

        // save last start time
        SysVar::del('SheduleLastStartTime');
        SysVar::set('SheduleLastStartTime', $iStartTime);

        /**
         * Запуск менеджера.
         */
        $oManager = Manager::getInstance();

        $oManager->execute();

        return 1;
    }
}

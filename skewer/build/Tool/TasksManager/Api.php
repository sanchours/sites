<?php

namespace skewer\build\Tool\TasksManager;

use skewer\base\queue;

/**
 * API работы с менеджером процессов.
 */
class Api
{
    /**
     * Список задач.
     *
     * @return mixed
     */
    public static function getListItems()
    {
        $aItems = queue\ar\Task::find()->order('upd_time', 'DESC')->asArray()->getAll();

        $aPriority = queue\Api::getPriorityList();
        $aResourceUse = queue\Api::getResourceUseList();
        $aStatus = queue\Api::getStatusList();

        /* Если закрыта - пишем выполнена */
        $aStatus[queue\Task::stClose] = \Yii::t('TasksManager', 'status_complete');

        foreach ($aItems as &$aItem) {
            $aItem['priority'] = (isset($aPriority[$aItem['priority']])) ? $aPriority[$aItem['priority']] : '';
            $aItem['resource_use'] = (isset($aResourceUse[$aItem['resource_use']])) ? $aResourceUse[$aItem['resource_use']] : '';
            $aItem['status'] = (isset($aStatus[$aItem['status']])) ? $aStatus[$aItem['status']] : '';
        }

        return $aItems;
    }
}

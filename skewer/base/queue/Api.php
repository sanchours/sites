<?php

namespace skewer\base\queue;

use skewer\base\log\Logger;
use skewer\base\orm\Query;
use skewer\base\queue\ar\TaskRow;
use skewer\components\gateway;

/**
 * Апи для работы с очередью задач.
 */
class Api
{
    /** Максимальная ресурсоемкость */
    const maxWeight = 10;

    /**
     * Добавление задачи.
     *
     * @param array $aData - массив с ключами:
     *      title, class, [parameters], [priority], [resource_use], [target_area], [parent]
     *
     * @return bool
     */
    public static function addTask($aData = [])
    {
        if (!isset($aData['title']) || !isset($aData['class'])) {
            return false;
        }

        $sParameters = isset($aData['parameters']) ? json_encode($aData['parameters']) : '';

        if (static::findTaskWithCommand($aData['class'], $sParameters)) {
            return false;
        }

        $priority = $aData['priority'] ?? Task::priorityLow;
        $resource_use = $aData['resource_use'] ?? Task::weightLow;

        $iTaskId = ar\Task::getNewRow(
            [
                'title' => $aData['title'],
                'class' => $aData['class'],
                'parameters' => $sParameters,
                'priority' => $priority,
                'resource_use' => $resource_use,
                'target_area' => $aData['target_area'] ?? 0,
                'status' => Task::stNew,
                'parent' => (isset($aData['parent'])) ? $aData['parent'] : 0,
                'md5' => md5(json_encode([$aData['class'], $sParameters])), //Запомним хеш задачи!
            ]
        )->save();

        if ($iTaskId && self::inCluster() && !YII_ENV_TEST) { //  Обработка записи в глобальной очереди
            try {
                $oClient = gateway\Api::createClient();

                $command = json_encode(['class' => $aData['class'], 'parameters' => $sParameters]);
                $aParam = [
                    $_SERVER['HTTP_HOST'],
                    $aData['title'],
                    $command,
                    $priority,
                    $resource_use,
                ];

                $oClient->addMethod('HostTools', 'addTask', $aParam, static function ($iGlobalId, $mError) use ($iTaskId) {
                    if ($mError) {
                        throw new \Exception($mError);
                    }
                    // update global ID
                    $oTask = ar\Task::find($iTaskId);
                    if ($oTask) {
                        $oTask->setData(['global_id' => $iGlobalId]);
                        if (!$oTask->save()) {
                            throw new \Exception('Ошибка при сохранении глобального ID задачи');
                        }
                    } else {
                        throw new \Exception('Не найдена задача!');
                    }
                });

                if (!$oClient->doRequest()) {
                    throw new \Exception($oClient->getError());
                }
            } catch (gateway\Exception $e) {
                echo $e->getMessage();
                Logger::dumpException($e);

                return false;
            } catch (\Exception $e) {
                Logger::dumpException($e);

                return false;
            }
        }

        return $iTaskId;
    }

    /**
     * Проверяет взята ли уже в работу задача
     * @param array $aData
     * @return false|TaskRow
     */
    public static function getTaskInProgress(array $aData)
    {
        if (!isset($aData['class'])) {
            return false;
        }

        $mParameters = '';
        if (isset($aData['parameters'])) {
            $mParameters = json_encode($aData['parameters']);
        }
        $oTask = static::findTaskWithCommand($aData['class'], $mParameters);
        if ($oTask) {
            return $oTask;
        }

        return false;
    }

    /**
     * Получение первой задачи.
     *
     * @return Task | false
     */
    public static function getFirstTask()
    {
        /** @var TaskRow $oTaskRow */
        $oTaskRow = ar\Task::find()
            ->where('status IN ? ', [Task::stNew, Task::stFrozen, Task::stWait])
            ->where('mutex', 0)
            ->where('parent', 0)
            ->order('priority', 'DESC')
            ->order('upd_time', 'ASC')
            ->order('id', 'ASC')
            ->getOne();

        if (!$oTaskRow) {
            return false;
        }

        return static::createTask($oTaskRow);
    }

    /**
     * Получение задачи по id.
     *
     * @param $id
     *
     * @return Task | false
     */
    public static function getTaskById($id)
    {
        /** @var TaskRow $oTaskRow */
        $oTaskRow = ar\Task::find()->where('id', $id)->where('mutex', 0)->getOne();

        if (!$oTaskRow) {
            return false;
        }

        return static::createTask($oTaskRow);
    }

    /**
     * Получение задачи по имени класса.
     *
     * @param $sClassName
     *
     * @return bool|Task
     */
    public static function getTaskByClassName($sClassName)
    {
        /** @var TaskRow $oTaskRow */
        $oTaskRow = ar\Task::find()
            ->where('status IN ? ', [Task::stNew, Task::stFrozen, Task::stWait])
            ->where('mutex', 0)
            ->where('class', $sClassName)
            ->getOne();

        if (!$oTaskRow) {
            return false;
        }

        return static::createTask($oTaskRow);
    }

    /**
     * Кол-во незакрытых подзадач.
     *
     * @param Task $oTask
     *
     * @return int
     */
    public static function getChildCount(Task $oTask)
    {
        return ar\Task::find()
            ->where('parent', $oTask->getId())
            ->where('mutex', 0)
            ->where('status IN ? ', [Task::stNew, Task::stFrozen, Task::stWait])
            ->getCount();
    }

    /**
     * Получение задачи по родительской.
     *
     * @param $id
     *
     * @return Task | false
     */
    public static function getFirstTask4Parent($id)
    {
        /** @var TaskRow $oTaskRow */
        $oTaskRow = ar\Task::find()
            ->where('parent', $id)
            ->where('mutex', 0)
            ->where('status IN ? ', [Task::stNew, Task::stFrozen, Task::stWait])
            ->order('parent', 'DESC')
            ->order('priority', 'DESC')
            ->order('upd_time', 'ASC')
            ->getOne();

        if (!$oTaskRow) {
            return false;
        }

        return static::createTask($oTaskRow);
    }

    /**
     * Создание экземпляра класса задачи по ее записи в бд.
     *
     * @param TaskRow $oTaskRow
     *
     * @throws \Exception
     *
     * @return Task | false
     */
    private static function createTask(TaskRow $oTaskRow)
    {
        try {
            $sClassName = $oTaskRow->class;

            if (!class_exists($sClassName)) {
                throw new \Exception('Class [' . $sClassName . '] task not found');
            }
            $oTask = new $sClassName();

            if (!$oTask instanceof Task) {
                throw new \Exception('Class [' . $sClassName . '] is not instance of \skewer\base\queue\Task');
            }
            $mParameters = json_decode($oTaskRow->parameters, true);

            if ($oTaskRow->parameters and $mParameters === null) {
                throw new \Exception(\Yii::t('TasksManager', 'invalid_parameters'));
            }
            $params = ($oTaskRow->parameters) ? $mParameters : [];

            $oTask->setStatus($oTaskRow->status, false);

            $oTask->setId($oTaskRow->id);

            /* Mutex */
            if (self::inCluster() && !YII_ENV_TEST) {
                $bMutex = self::canExecuteGlobal($oTaskRow->id, $oTaskRow->global_id);
            } else {
                $bMutex = self::holdItem($oTaskRow->id);
            }

            if (!$bMutex) {
                return false;
            }

            switch ($oTask->getStatus()) {
                case Task::stNew:
                    $oTask->init($params);
                    if ($oTask->getStatus() != Task::stError) {
                        $oTask->setStatus(Task::stInit);
                    }
                    break;

                case Task::stFrozen:
                    $oTask->recovery($params);
                    if ($oTask->getStatus() != Task::stError) {
                        $oTask->setStatus(Task::stInit);
                    }
                    break;

                case Task::stWait:
                    break;

                default:
                    throw new \Exception('Illegal status ' . $oTask->getStatus());
                    break;
            }

            return $oTask;
        } catch (\Exception $e) {
            Logger::dump('error queue\Api: ' . $e->getMessage());
            Logger::dumpException($e);
            $oTaskRow->setData(['status' => Task::stError]);
            $oTaskRow->save();
        } catch (\ErrorException $e) {
            Logger::dumpException($e);
            $oTaskRow->setData(['status' => Task::stError]);
            $oTaskRow->save();
        }
    }

    /**
     * @param Task $oTask
     *
     * @throws \Exception
     *
     * @return bool
     */
    public static function updateStatusTask(Task $oTask)
    {
        /** @var ar\TaskRow $oTaskRow */
        $oTaskRow = ar\Task::find($oTask->getId());
        if (!$oTaskRow) {
            throw new \Exception('Task not found ' . $oTask->getStatus());
        }

        $oTaskRow->status = $oTask->getStatus();
        $oTaskRow->save();

        if (self::inCluster() && !YII_ENV_TEST) {
            try {
                $oClient = gateway\Api::createClient();

                $aParam = [$oTaskRow->global_id, $oTaskRow->status];

                /* @noinspection PhpUnusedParameterInspection */
                $oClient->addMethod('HostTools', 'setTaskStatus', $aParam, static function ($mResult, $mError) {
                    if ($mError) {
                        throw new \Exception($mError);
                    }
                });

                if (!$oClient->doRequest()) {
                    throw new \Exception($oClient->getError());
                }
            } catch (\Exception $e) {
                Logger::dump('error QueueManager: ' . $e->getMessage());

                $oTask->setStatus(Task::stError, false);
                $oTaskRow->status = Task::stError;
                $oTaskRow->save();

                return false;
            }
        }

        return true;
    }

    /**
     * Сохранение задачи.
     *
     * @param Task $oTask
     * @param array $aParams
     */
    public static function saveTask(Task $oTask, $aParams = [])
    {
        $oTaskRow = ar\Task::find($oTask->getId());
        if (!$oTaskRow) {
            $oTaskRow = ar\Task::getNewRow();
        }

        $oTaskRow->status = $oTask->getStatus();
        if ($aParams) {
            $oTaskRow->parameters = json_encode($aParams, true);
        }
        $oTaskRow->save();
    }

    /**
     * При работе в кластере - отправляет список заданий в очереди с актуальными статусами,
     * регистрирует новые задания в глобальной очереди, стохастически запускает сборщик мусора.
     *
     * @throws \Exception
     *
     * @return bool
     */
    public static function syncronize()
    {
        if (!INCLUSTER) {
            return false;
        }

        $oClient = gateway\Api::createClient();

        $aItems = ar\Task::find()->asArray()->getAll();

        $aParam = [$_SERVER['HTTP_HOST'], $aItems];

        /* @noinspection PhpUnusedParameterInspection */
        $oClient->addMethod('HostTools', 'syncronizeTask', $aParam, static function ($mResult, $mError) {
            if ($mError) {
                throw new \Exception($mError);
            }
            //if(!$mResult)  throw new Exception('Ошибка при синхронизации глобальных статусов задач');
        });

        if (!$oClient->doRequest()) {
            return false;
        }

        return true;
    }

    /**
     * Возвращает активную задачу по заданной команде, если она существует
     * @param string $sClass
     * @param string $params
     * @return bool|TaskRow
     */
    private static function findTaskWithCommand($sClass, $params)
    {
        $md5 = md5(json_encode([$sClass, $params]));

        return ar\Task::find()
            ->where('md5', $md5)
            ->where('status != ? ', Task::stClose)
            ->andWhere('status != ? ', Task::stError)
            ->andWhere('status != ? ', Task::stTimeout)
            ->getOne();
    }

    /**
     * Метод маркерует задачу, выбранную для обработки. (Установка мютекса).
     *
     * @static
     *
     * @param int $iItemId
     *
     * @return int
     */
    public static function holdItem($iItemId)
    {
        return ar\Task::update()
            ->set('mutex', 1)
            ->where('id', $iItemId)
            ->get(true);
    }

    /**
     * Произвести захват задачи в общесерверной очереди для последующего исполнения.
     *
     * @param $iTask
     * @param $globalId
     * @param array $aErrors
     *
     * @return bool
     */
    public static function canExecuteGlobal($iTask, $globalId, &$aErrors = [])
    {
        // производит мьютексный захват задачи в очереди на сервере и сохраняет значение мьютекса в свойство
        // если захват не удался - возвращает false

        $oClient = gateway\Api::createClient();

        $aParam = [$globalId];

        $bRes = false;

        $oClient->addMethod('HostTools', 'canExecuteTask', $aParam, static function ($mResult, $mError) use ($iTask, $globalId, &$bRes, &$aErrors) {
            if ($mError) {
                throw new \Exception($mError);
            }
            // Обработка ответа от sms версии ниже 1.02.5
            if (is_bool($mResult)) {
                if ($mResult) {
                    $bRes = (bool) self::holdItem($iTask);
                } else {
                    $bRes = false;
                    $sErrorText = sprintf('Не удалось выполнить глобальную задачу [local=%d, global=%d]. %s', $iTask, $globalId, $mResult['description']);
                    $aErrors[] = $sErrorText;
                    \skewer\base\log\Logger::dumpException(new \Exception($sErrorText));
                    self::setStatus($iTask, Task::stWait);
                }
            } elseif (is_array($mResult)) {
                // версия sms старше 1.02.5
                //  Задача может быть запущена
                if ($mResult['result']) {
                    $bRes = (bool) self::holdItem($iTask);
                } else {
                    $bRes = false;
                    $aErrors[] = $mResult['description'];
                    \skewer\base\log\Logger::dumpException(
                        new \Exception(sprintf('Не удалось выполнить глобальную задачу [local=%d, global=%d]. %s', $iTask, $globalId, $mResult['description']))
                    );
                    self::setStatus($iTask, Task::stWait);
                }
            }
        });

        if (!$oClient->doRequest()) {
            self::setStatus($iTask, Task::stError);
            \skewer\base\log\Logger::dumpException(new \Exception($oClient->getError()));
            $bRes = false;
            $aErrors[] = $oClient->getError();
        }

        return $bRes;
    }

    /**
     * Сброс мьютекса.
     *
     * @static
     *
     * @param int $iItemId
     *
     * @return int
     */
    public static function unholdItem($iItemId)
    {
        ar\Task::update()
            ->set('mutex', 0)
            ->where('id', $iItemId)
            ->get();
    }

    /**
     * Удаляет из очереди выполненные задания, перезапускает или удаляет подвисшие задания
     * по таймауту.
     *
     * @return bool
     */
    public static function collectGarbage()
    {
        /** Отметим то, что висит давно */
        $sQuery = 'UPDATE `task` SET `mutex` = 0, `status` = :status
        WHERE `mutex`=1 AND `upd_time` < DATE_SUB(NOW(),INTERVAL 5 HOUR)';
        Query::SQL($sQuery, ['status' => Task::stTimeout]);

        /** Удалим старые повисшие */
        $sQuery = 'DELETE FROM `task` WHERE `upd_time` < DATE_SUB(NOW(),INTERVAL 7 DAY)';
        Query::SQL($sQuery);

        /** Отметим то, что висит недавно */
        $sQuery = 'UPDATE `task` SET `status` = :new_status
        WHERE `status` = :old_status AND `upd_time` < DATE_SUB(NOW(),INTERVAL 1 HOUR)';
        Query::SQL($sQuery, ['new_status' => Task::stTimeout, 'old_status' => Task::stInterapt]);

        /** Удаление старых */
        $sQuery = 'DELETE FROM `task` WHERE `status`=:status AND `upd_time` < DATE_SUB(NOW(),INTERVAL 12 HOUR)';
        Query::SQL($sQuery, ['status' => Task::stClose]);

        // синхронизация
        self::syncronize();

        return true;
    }

    /**
     * Получаем запись задачи.
     *
     * @param int $id
     *
     * @return TaskRow
     */
    public static function getTaskRowById($id)
    {
        return ar\Task::find($id);
    }

    /**
     * Определяет, работает ли сервер в кластере.
     *
     * @return bool
     */
    public static function inCluster()
    {
        return (defined('TestTask') && TestTask) ? false : INCLUSTER;
    }

    /**
     * Отадет название статуса по id.
     *
     * @return string
     */
    public static function getStatusList()
    {
        return [
            Task::stNew => \Yii::t('TasksManager', 'status_new'),
            Task::stInit => \Yii::t('TasksManager', 'status_init'),
            Task::stProcess => \Yii::t('TasksManager', 'status_process'),
            Task::stInterapt => \Yii::t('TasksManager', 'status_interapt'),
            Task::stError => \Yii::t('TasksManager', 'status_error'),
            Task::stFrozen => \Yii::t('TasksManager', 'status_frozen'),
            Task::stWait => \Yii::t('TasksManager', 'status_wait'),
            Task::stComplete => \Yii::t('TasksManager', 'status_complete'),
            Task::stClose => \Yii::t('TasksManager', 'status_close'),
            Task::stTimeout => \Yii::t('TasksManager', 'status_timeout'),
        ];
    }

    /**
     * Список приоритетов.
     *
     * @return array
     */
    public static function getPriorityList()
    {
        return [
            Task::priorityLow => \Yii::t('TasksManager', 'priority_low'),
            Task::priorityNormal => \Yii::t('TasksManager', 'priority_normal'),
            Task::priorityHigh => \Yii::t('TasksManager', 'priority_high'),
            Task::priorityCritical => \Yii::t('TasksManager', 'priority_critical'),
        ];
    }

    /**
     * Список ресурсоемкостей.
     *
     * @return array
     */
    public static function getResourceUseList()
    {
        return [
            Task::weightLow => \Yii::t('TasksManager', 'weight_low'),
            Task::weightNormal => \Yii::t('TasksManager', 'weight_normal'),
            Task::weightHigh => \Yii::t('TasksManager', 'weight_high'),
            Task::weightCritic => \Yii::t('TasksManager', 'weight_critical'),
        ];
    }

    /**
     * Устанавливает таску значение "Ошибка".
     *
     * @param int $iTask Id задачи
     * @param int $iStatus Код статуса
     *
     * @return bool
     */
    private static function setStatus($iTask, $iStatus)
    {
        /** @var TaskRow $oTask */
        $oTask = ar\Task::find($iTask);
        if (!$oTask) {
            return false;
        }

        $oTask->mutex = 0;
        $oTask->status = $iStatus;

        return $oTask->save();
    }
}

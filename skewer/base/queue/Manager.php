<?php

namespace skewer\base\queue;

use yii\base\ErrorException;

/**
 * Менеджер по запуску задач.
 */
class Manager
{
    private static $instance = null;

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Manager();
            self::$instance->init();
        }

        return self::$instance;
    }

    private function init()
    {
        /* Запуск системы слежения за ограничениями */
        Limiter::getInstance();
    }

    /**
     * Запуск задач.
     */
    public function execute()
    {
        do {
            /** @var Task $oTask */
            $oTask = Api::getFirstTask();

            if (!$oTask) {
                break;
            }

            /* Запуск задачи */
            $this->executeTask($oTask);
        } while (Limiter::checkLimit());
    }

    /**
     * Запуск задачи.
     *
     * @param Task $oTask
     *
     * @throws \Exception
     *
     * @return bool
     */
    public function executeTask(Task $oTask)
    {
        Api::holdItem($oTask->getId());

        try {
            switch ($oTask->getStatus()) {
                /* Запуск задачи */
                case Task::stInit:

                    $oTask->setStatus(Task::stProcess);

                    /* Предвыполнение */
                    $oTask->beforeExecute();

                    /* Запуск, пока не меняется статус */
                    while ($oTask->getStatus() == Task::stProcess) {
                        /* Следим за ограничениями */
                        if (!Limiter::checkLimit()) {
                            $oTask->setStatus(Task::stInterapt);
                            break;
                        }

                        /* Запуск задачи */
                        $oTask->execute();
                    }

                    /* Поствыполнение */
                    $oTask->afterExecute();

                    switch ($oTask->getStatus()) {
                        /* Прерывание задачи */
                        case Task::stInterapt:
                            $oTask->reservation();
                            $oTask->setStatus(Task::stFrozen, false);
                            break;

                        /* Завершение задачи */
                        case Task::stComplete:
                            $oTask->complete();
                            if (Api::getChildCount($oTask)) {
                                $oTask->setStatus(Task::stWait, false);
                                $this->executeChildTask($oTask);
                            } else {
                                $oTask->setStatus(Task::stClose, false);
                            }
                            break;

                        /* Ошибка в задаче */
                        case Task::stError:
                            $oTask->error();
                            break;

                        /* Ожидаем */
                        case Task::stWait:
                            $this->executeChildTask($oTask);
                            break;
                    }

                    break;

                /* Ожидание выполнения подзадач */
                case Task::stWait:
                    $this->executeChildTask($oTask);
                    break;

                /* Ошибка */
                case Task::stError:
                    $oTask->error();
                    break;

                default:
                    throw new \Exception('Illegal status ' . $oTask->getStatus());
                    break;
            }

            /* Сохранение */
            Api::saveTask($oTask);
            Api::unholdItem($oTask->getId());
        } catch (ErrorException $e) {
            $oTask->setStatus(Task::stError);
            $oTask->error();
            throw $e;
        }
    }

    /**
     * Запуск дочерней задачи.
     *
     * @param Task $oTask
     *
     * @return int
     */
    private function executeChildTask(Task $oTask)
    {
        do {
            /** @var Task $oChildTask */
            $oChildTask = Api::getFirstTask4Parent($oTask->getId());

            if ($oChildTask) {
                $oTask->setStatus(Task::stWait);

                /* Запуск дочерней задачи */
                $this->executeTask($oChildTask);
            } else {
                $oTask->setStatus(Task::stClose);
                break;
            }
        } while (Limiter::checkLimit());
    }

    /**
     * Очищает очередь задач.
     */
    public static function clear()
    {
        ar\Task::delete()->get();
        Api::syncronize();
    }
}

<?php

namespace skewer\base\queue;

use skewer\base\log\Logger;
use skewer\base\queue\ar\TaskRow;
use skewer\components\auth\CurrentAdmin;
use yii\base\UserException;

/**
 * Прототип задачи.
 */
abstract class Task
{
    /** Новая */
    const stNew = 1;

    /** В работе */
    const stProcess = 2;

    /** Завершена */
    const stComplete = 3;

    /** Ошибка */
    const stError = 4;

    /** Повисшая */
    const stTimeout = 5;

    /** Заморожена */
    const stFrozen = 6;

    /** В ожидании */
    const stWait = 7;

    /** Инициализирована */
    const stInit = 8;

    /** Закрыта */
    const stClose = 9;

    /** Прервана */
    const stInterapt = 10;

    /** Приоритеты */
    const priorityLow = 1;
    const priorityNormal = 2;
    const priorityHigh = 3;
    const priorityCritical = 4;

    /** Ресурсоемкость */
    const weightLow = 3;
    const weightNormal = 4;
    const weightHigh = 7;
    const weightCritic = 9;

    /** @var int Статус */
    private $status = 0;

    /** @var int Id */
    private $id = 0;

    /** @var string Название задачи */
    private $title = '';

    /** @var int Глобальный идентификатор в sms */
    private $global_id = 0;

    /** @var int Мьютекс */
    private $mutex = 0;

    /** @var array Параметры, передаваемые между итерациями */
    private $parameters = [];

    /** @var string Сообщение об ошибке */
    private $sError = '';

    /**
     * Метод первичной инициализации.
     */
    public function init()
    {
    }

    /**
     * Метод - восстановления данных.
     */
    public function recovery()
    {
    }

    /**
     * Метод, вызываемый перед выполнением
     */
    public function beforeExecute()
    {
    }

    /**
     * Выполнение задачи.
     */
    abstract public function execute();

    /**
     * Метод, вызываемый после выполнения.
     */
    public function afterExecute()
    {
    }

    /**
     * Метод, вызываемый при резервации для последующего продолжения работы.
     */
    public function reservation()
    {
    }

    /**
     * Метод, вызываемый в случае ошибок при работе задачи.
     */
    public function error()
    {
    }

    /**
     * Метод, вызываемый по завершении задачи.
     */
    public function complete()
    {
    }

    /**
     * Возвращает статус задачи.
     *
     * @return int
     */
    final public function getStatus()
    {
        return $this->status;
    }

    /**
     * Установка статуса задачи.
     *
     * @param $status
     * @param $bSave
     */
    final public function setStatus($status, $bSave = true)
    {
        $this->status = $status;

        if ($bSave && $this->getId()) {
            Api::updateStatusTask($this);
        }
    }

    /**
     * Сохранение id.
     *
     * @param int $id
     */
    final public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * Получение id.
     *
     * @return int
     */
    final public function getId()
    {
        return $this->id;
    }

    /**
     * Получить название задачи.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Установить название задачи.
     *
     * @param $sTitle
     */
    public function setTitle($sTitle)
    {
        $this->title = $sTitle;
    }

    /**
     * Получить поле мьютекс
     *
     * @return int
     */
    public function getMutex()
    {
        return $this->mutex;
    }

    /**
     * Установить поле мьютекс
     *
     * @param $mutex
     *
     * @return mixed
     */
    public function setMutex($mutex)
    {
        return $this->mutex = $mutex;
    }

    /**
     * Установить глобальный идентификатор задачи.
     *
     * @param int $id
     */
    public function setGlobalId($id)
    {
        $this->global_id = $id;
    }

    /**
     * Получить глобальный идентификатор
     *
     * @return int
     */
    public function getGlobalId()
    {
        return $this->global_id;
    }

    /**
     * Получить параметры задачи.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Установить параметры задачи.
     *
     * @param array $aParams
     */
    public function setParameters($aParams)
    {
        $this->parameters = $aParams;
    }

    /**
     * Установит сообщение об ошибке.
     *
     * @param string
     * @param mixed $sError
     */
    public function setError($sError)
    {
        $this->sError = $sError;
    }

    /**
     * Вернет сообщение об ошибке.
     *
     * @return string
     */
    public function getError()
    {
        return $this->sError;
    }

    /**
     * Сохранение параметров.
     *
     * @param array $aParams
     */
    final protected function setParams($aParams = [])
    {
        Api::saveTask($this, $aParams);
    }

    /**
     * Создаёт или запускает существующую задачу.
     *
     * @param array $aConfig - конфиг задачи
     * @param int $iTask - id задачи. Если =0 - создается новая
     * @param bool $bRunTaskByClassName - запускать существующую задачу по className, если по id задача не найдена?
     *
     * @throws UserException
     * @throws \Exception
     *
     * @return array - массив со статусом и id задачи
     */
    public static function runTask($aConfig, $iTask = 0, $bRunTaskByClassName = false)
    {
        if (!$iTask) {
            if ($bRunTaskByClassName) {
                /** @var TaskRow $oTask */
                $oTask = ar\Task::find()
                    ->where('status IN ? ', [Task::stNew, Task::stFrozen, Task::stWait])
                    ->where('mutex', 0)
                    ->where('class', $aConfig['class'])
                    ->order('upd_time', 'DESC')
                    ->getOne();

                if ($oTask) {
                    $iTask = $oTask->id;
                }
            }

            if (!$iTask) {
                $iTask = Api::addTask($aConfig);
            }
        }

        $oTaskRow = static::checkTask($iTask, $aConfig);

        $oTask = self::create($oTaskRow);

        if (!$oTask->canExecuteTask()) {
            throw new UserException($oTask->getError());
        }
        $oTask->initOrRecovery();

        $oManager = Manager::getInstance();
        $oManager->executeTask($oTask);

        return ['status' => $oTask->getStatus(), 'id' => $iTask];
    }

    /**
     * Метод проверяет может ли задача выполниться прямо сейчас
     *
     * @return bool
     */
    public function canExecuteTask()
    {
        if ($this->getMutex()) {
            $this->setError(
                CurrentAdmin::isSystemMode()
                ? sprintf('Локальный мьютекс захвачен задачей: [id=%d] %s', $this->getId(), $this->getTitle())
                : $this->getUserMessageOnMutexBusy()
            );
            $this->setStatus(self::stWait);
            Logger::dumpException(
                new \Exception(sprintf('Не удалось выполнить глобальную задачу [local=%d, global=%d]', $this->getId(), $this->getGlobalId()))
            );

            return false;
        }

        if (Api::inCluster()) {
            $aErrors = [];
            $bMutex = Api::canExecuteGlobal($this->getId(), $this->getGlobalId(), $aErrors);
            if (!$bMutex) {
                $this->setError(
                    CurrentAdmin::isSystemMode()
                    ? reset($aErrors)
                    : $this->getUserMessageOnMutexBusy()
                );

                return false;
            }
        }

        return true;
    }

    /**
     * Фабричный метод
     * Создаёт объект Task требуемого типа по записи из БД.
     *
     * @param TaskRow $oTaskRow
     *
     * @throws \Exception
     *
     * @return Task
     */
    public static function create(TaskRow $oTaskRow)
    {
        $sClassName = $oTaskRow->class;

        if (!class_exists($sClassName)) {
            throw new \Exception('Class [' . $sClassName . '] task not found');
        }
        /** @var Task $oTask */
        $oTask = new $sClassName();

        if (!$oTask instanceof Task) {
            throw new \Exception('Class [' . $sClassName . '] is not instance of \skewer\base\queue\Task');
        }
        $mParameters = json_decode($oTaskRow->parameters, true);

        if ($oTaskRow->parameters and $mParameters === null) {
            throw new \Exception(\Yii::t('TasksManager', 'invalid_parameters'));
        }
        $params = ($oTaskRow->parameters) ? $mParameters : [];

        $oTask->setId($oTaskRow->id);
        $oTask->setGlobalId($oTaskRow->global_id);
        $oTask->setTitle($oTaskRow->title);
        $oTask->setStatus($oTaskRow->status, false);
        $oTask->setMutex($oTaskRow->mutex);
        $oTask->setParameters($params);

        return $oTask;
    }

    /**
     * Метод выполняет инициализацию или восстановление задачи.
     */
    public function initOrRecovery()
    {
        Api::holdItem($this->id);

        switch ($this->getStatus()) {
            case Task::stNew:
                $this->init($this->getParameters());
                if ($this->getStatus() != self::stError) {
                    $this->setStatus(self::stInit);
                }
                break;

            case Task::stFrozen:
                $this->recovery($this->getParameters());
                if ($this->getStatus() != self::stError) {
                    $this->setStatus(self::stInit);
                }
                break;

            case Task::stWait:
                break;

            default:
                throw new \Exception('Illegal status ' . $this->getStatus());
                break;
        }
    }

    /**
     * Вернет сообщение для пользователя в случае если мьютекс занят
     *
     * @return string
     */
    public function getUserMessageOnMutexBusy()
    {
        return 'Предыдущая задача не выполнена';
    }

    /**
     * @param $iTask
     * @param $aConfig
     *
     * @throws UserException
     *
     * @return array|bool|\skewer\base\orm\ActiveRecord|TaskRow
     */
    protected static function checkTask($iTask, $aConfig)
    {
        /** @var TaskRow $oTaskRow */
        if (!$iTask || !($oTaskRow = ar\Task::findOne(['id' => $iTask]))) {
            throw new UserException('Задача не добавлена [' . $aConfig['class'] . ':' . $iTask . ']');
        }

        return $oTaskRow;
    }
}

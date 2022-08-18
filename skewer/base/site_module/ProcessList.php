<?php

namespace skewer\base\site_module;

use Exception;
use yii\web\ServerErrorHttpException;

/**
 * Класс для работы с деревом процессов.
 */
class ProcessList
{
    /**
     * @event Событие, которое будет вызвано после отработки дерева процессов
     */
    const EVENT_AFTER_COMPLETE = 'afterComplete';

    /**
     * Массив путей по меткам к процессам
     * Массив одноуровневый.
     *
     * @var Process[]
     */
    public $aProcessesPaths;

    /**
     * Флаг готовности дерева процессов.
     *
     * @var bool
     */
    private $bProcessTreeComplete = false;

    /**
     * Массив процессов, которые надо обработать
     * Представляется из себя дерево в отличие от $aProcessesPaths.
     *
     * @var array
     */
    protected $aProcessList = [];

    /**
     * Возвращает процесс по пути из меток от корневого процесса page
     * Если не найден и обработка дерева уже завершена, то отдает psNotFound,
     * иначе psWait как флаг того, что нужно подождать.
     *
     * @param string $sPath Путь от корневого процесса до искомого
     * @param int $iStatus Статус искомого процесса
     *
     * @return int|Process
     */
    public function getProcess($sPath, $iStatus = psComplete)
    {
        if (isset($this->aProcessesPaths[$sPath])) {
            if ($iStatus == psAll) {
                return $this->aProcessesPaths[$sPath];
            }

            if ($this->aProcessesPaths[$sPath]->getStatus() === $iStatus) {
                return $this->aProcessesPaths[$sPath];
            }
        }

        return $this->bProcessTreeComplete ? psNotFound : psWait;
    }

    /**
     * Восстанавливает список путей к процессам
     *
     * @param Process[] $aProcessList Список процессов
     */
    public function recoverProcessPaths(&$aProcessList = null)
    {
        if ($aProcessList === null) {
            $aProcessList = &$this->aProcessList;
        }

        foreach ($aProcessList as $oChildProcess) {
            // восстанавливаем массив с путями

            $this->aProcessesPaths[$oChildProcess->getLabelPath()] = $oChildProcess;

            if (count($oChildProcess->processes)) {
                $this->recoverProcessPaths($oChildProcess->processes);
            }
        }
        $this->bProcessTreeComplete = true;
    }

    /**
     * Запускает на выполнение дерево процессов.
     *
     * @throws Exception
     *
     * @return bool|int
     */
    public function executeProcessList()
    {
        $i = 0;

        $this->bProcessTreeComplete = true;

        do {
            if (++$i > 100) {
                throw new Exception('Infinite loop in Processor::executeProcessList. ' . $this->getCurrentStateText());
            }
            $bComplete = true;

            foreach ($this->aProcessesPaths as $oProcess) {
                /* @var $oProcess Process */
                switch ($oProcess->getStatus()) {
                    case psNew:
                    case psWait:

                        $sClassName = $oProcess->getModuleClass();
                        \Yii::beginProfile($sClassName, 'sk\Module');

                        $iStatus = $oProcess->execute();

                        \Yii::endProfile($sClassName, 'sk\Module');

                        switch ($iStatus) {
                            case psComplete:

                                if (count($oProcess->processes)) {
                                    foreach ($oProcess->processes as $oChildProcess) {
                                        if ($oChildProcess->getStatus() == psNew) {
                                            $bComplete = false;
                                        } // появились новые процессы
                                    }
                                }
                                break;

                            case psError:
                            case psExit:
                            case psReset:

                                return $iStatus;
                                break;

                            case psWait:
                                $bComplete = false; // Текущий процесс не отработал
                                break;
                        }

                    break;
                }
            }
        } while (!$bComplete);

        \Yii::$app->trigger(self::EVENT_AFTER_COMPLETE);

        return psComplete;
    }

    /**
     * Добавляет новый процесс в очередь выполнения.
     *
     * @param Context $oContext Контекст создаваемого процесса
     *
     * @throws \ErrorException
     *
     * @return null|Process
     */
    public function addProcess(Context $oContext)
    {
        // Место занято - выходим
        if (isset($this->aProcessList[$oContext->sLabel])) {
            throw new \ErrorException("Module [{$oContext->sLabel}] already exists in process tree");
        }
        $oContext->sURL = \Yii::$app->router->getURLTail();
        $oContext->sLabelPath = $oContext->sLabel;
        $oContext->aGet = \Yii::$app->router->aGet;

        $this->resetProcessTreeComplete();

        return  $this->aProcessList[$oContext->sLabel] = new Process($oContext);
    }

    /**
     * Добавляет процесс в метку с заданным именем
     * Метод использовать с осторожностью.
     *
     * @param string $sLabel
     * @param Process $oProcess
     */
    public function setProcessToLabel($sLabel, Process $oProcess)
    {
        $this->aProcessList[$sLabel] = $oProcess;
    }

    /**
     * Регистрирует процесс в списке процессов.
     *
     * @param string $sLabelPath Путь по меткам вызова до регистрируемого процесса
     * @param Process $oProcess Ссылка на процесс
     */
    public function registerProcessPath($sLabelPath, Process $oProcess)
    {
        $this->aProcessesPaths[$sLabelPath] = $oProcess;
    }

    /**
     * Отменяет регистрацию процесса по пути.
     *
     * @param $sLabelPath
     *
     * @throws ServerErrorHttpException
     */
    public function unregisterProcessPath($sLabelPath)
    {
        if (isset($this->aProcessesPaths[$sLabelPath])) {
            unset($this->aProcessesPaths[$sLabelPath]);
        }
    }

    /**
     * Сбрасывет флаг того, что дерево процессов отработало.
     */
    public function resetProcessTreeComplete()
    {
        $this->bProcessTreeComplete = false;
    }

    /**
     * Удаляет процесс из обработки.
     *
     * @param string $sLabelPath
     *
     * @return bool
     */
    public function removeProcess($sLabelPath)
    {
        if ($oProcess = $this->getProcess($sLabelPath)) {
            $this->resetProcessTreeComplete();

            // удаляем детей
            if (($oProcess instanceof Process)) {
                if (($oParentProcess = $oProcess->getParentProcess()) instanceof Process) {
                    $oParentProcess->removeChildProcess($oProcess->getLabel());

                    return true;
                }

                foreach ($oProcess->processes as $sChildLabel => $oChildProcess) {
                    $oProcess->removeChildProcess($sChildLabel);
                }

                $this->unregisterProcessPath($sLabelPath);
                unset($oProcess);
            }

            $this->unregisterProcessPath($sLabelPath);
            $this->aProcessList[$sLabelPath] = null;
            unset($this->aProcessList[$sLabelPath]);

            return true;
        }

        return false;
    }

    /**
     * Собирает список текущих процессов в удобном для пользователя виде
     * Используется для выдачи ошибки при зацикливании процессов.
     *
     * @return string
     */
    public function getCurrentStateText()
    {
        $sOut = "\n";

        $aStatusList = [
            0 => 'psNew',
            1 => 'psComplete',
            2 => 'psWait',
            3 => 'psNotFound',
            4 => 'psExit',
            5 => 'psRendered',
            6 => 'psError',
            7 => 'psAll',
            8 => 'psReset',
            9 => 'psBreak',
        ];

        foreach ($this->aProcessesPaths as $sName => $oProcess) {
            $sOut .= sprintf(
                "\n %s - %s [%d], obj: %s",
                $sName,
                isset($aStatusList[$oProcess->getStatus()]) ? $aStatusList[$oProcess->getStatus()] : '<status-unknown>',
                $oProcess->getStatus(),
                $oProcess->getModuleClass()
            );
        }

        return $sOut;
    }
}

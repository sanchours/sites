<?php

namespace skewer\base\site_module;

use Exception;
use skewer\base\router\Router;
use skewer\components\content_generator\Asset;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

/**
 * Прототип процесса.
 */
class Process
{
    /**
     * Ссылка на контекст вызова процесса.
     *
     * @var Context
     */
    protected $oContext;

    /**
     * Экземпляр запускаемого модуля.
     *
     * @var null|Prototype
     */
    protected $oObject;
    /**
     * Статус процесса.
     *
     * @var int
     */
    protected $iStatus = 0;
    /**
     * Флаг готовности дерева процессов.
     *
     * @var bool
     */
    protected $bProcessTreeComplete = false;
    /**
     * Счетчик количества выполнений.
     *
     * @var int
     */
    protected $iExecCount = 0;
    /**
     * Локальный путь к шаблону.
     *
     * @var string
     */
    public $template = '';
    /**
     * Массив дочерних процессов.
     *
     * @var Process[]
     */
    public $processes = [];
    /**
     * Экземпляр Реврайтера для текущего процесса.
     *
     * @var null|Router
     */
    public $oRouter;
    /**
     * Разделитель пути в дереве процессов.
     *
     * @var string
     */
    private $sDelimiter = '.';

    /**
     * Конструктор нового процесса.
     *
     * @param Context $oContext
     *
     * @throws ServerErrorHttpException В случае, если модуль,
     * для которого создается процесс не установлен выбрасывается исключение
     */
    public function __construct(Context $oContext)
    {
        $this->oContext = $oContext;

        $this->oContext->oProcess = &$this;

        \Yii::$app->processList->registerProcessPath($this->oContext->sLabelPath, $this);
    }

    // func

    public function getContext()
    {
        return $this->oContext;
    }

    /**
     * Очищает массив данных, возвращенных модулем
     */
    public function clearData()
    {
        $this->oContext->clearData();
    }

    /**
     * Запускает выполнение модуля.
     *
     * @throws Exception
     * @throws ServerErrorHttpException В случае, если модуль,
     * для которого создается процесс не установлен выбрасывается исключение
     *
     * @return int В случае, если модуль,
     * для которого создается процесс не установлен выбрасывается исключение
     */
    public function execute()
    {
        try {
            if (!($this->oObject instanceof $this->oContext->sClassName)) {
                $this->oContext->sClassName = Module::getClassOrExcept($this->oContext->sClassName, $this->oContext->getModuleLayer());

                if (!class_exists($this->oContext->sClassName)) {
                    throw new ServerErrorHttpException('Class [' . $this->oContext->sClassName . '] not found.');
                }
                $this->oObject = new $this->oContext->sClassName($this->oContext);

                $this->oRouter = new Router($this->oContext->sURL, $this->oContext->aGet);

                if ($this->oObject->useRouting()) {
                    $aDecodedRules = $this->oRouter->getRulesByClassName($this->oContext->sClassName, $this->getModule()->getBaseActionName());

                    if (!$aDecodedRules) {
                        $this->getModule()->setUseRouting(false);
                    }

                    $this->oContext->sUsedURL = $this->oContext->sURL;

                    if (!$this->oRouter->getParams($aDecodedRules)) {
                        throw new NotFoundHttpException();
                    }
                    if ($this->oRouter->getURLTail()) {
                        $this->oContext->sUsedURL = mb_substr($this->oContext->sUsedURL, 0, mb_strpos($this->oContext->sURL, $this->oRouter->getURLTail()));
                    }

                    $this->oContext->sURL = $this->oRouter->getURLTail();
                    $this->oContext->aGet = $this->oRouter->getURLParams();

                    if (!$this->oContext->sURL) {
                        \Yii::$app->router->setUriParsed();
                    }
                }
            }

            if (!$this->oObject->allowExecute()) {
                return psError;
            }

            //var_dump($this->oContext->getModuleName(), $this->oContext->getModuleLayer());

            $moduleName = $this->oContext->getModuleName();

            if (!\Yii::$app->register->moduleExists($moduleName, $this->oContext->getModuleLayer())) {
                throw new ServerErrorHttpException(
                    sprintf(
                        'Module [%s] in layer [%s] does not installed',
                        $moduleName,
                        $this->oContext->getModuleLayer()
                )
            );
            }

            $this->oObject->init();
            $this->iStatus = $this->oObject->execute();
            $this->iStatus = ($this->iStatus) ? $this->iStatus : psError;
            $this->oObject->shutdown();
        } catch (NotFoundHttpException $e) {
            \Yii::$app->router->setPage(page404);

            return psExit;
        } catch (UnauthorizedHttpException $e) {
            \Yii::$app->router->setPage(pageAuth);

            return psExit;
        }

        return $this->iStatus;
    }

    // func

    /**
     * Производит парсинг данных в шаблон.
     *
     * @return bool|string
     */
    public function render()
    {
        // Простая защита от многократного вызова текущего метода у одного процесса
        if ($this->getOuterText()) {
            return $this->getOuterText();
        }

        if (count($this->processes)) {
            foreach ($this->processes as $sLabel => &$oChildProcess) {
                $sContent = $oChildProcess->render();
                if (isset($sContent)) {
                    Asset::createBlockList($sContent);
                }
                $this->oContext->setData($sLabel, $sContent);
            }
            Asset::register(\Yii::$app->view);
        }

        if ($this->getStatus() == psComplete) {
            $this->getModule()->beforeRender();
            $this->setOut(Parser::render($this->oContext));
            $this->setStatus(psRendered);
            $this->oContext->clearData();
        } elseif ($this->getStatus() == psBreak) {
            $this->setOut('');
        }

        return $this->getOuterText();
    }

    // func

    /**
     * Не используется.
     *
     * @deprecated Проверить использование
     */
    public function wasCalled()
    {
    }

    // func

    /**
     * Не используется.
     *
     * @deprecated Проверить использование
     *
     * @param string $sPath
     */
    public function reCall($sPath)
    {
    }

    // func

    /**
     * Добавялет дочерний процесс на выполнение.
     *
     * @param Context $oContext Контекст Добавляемого процесса
     *
     * @return bool|Process Возвращет Экземпляр созданного процесса либо false в случае ошибки
     */
    public function addChildProcess(Context $oContext)
    {
        // если место под процесс занято - удаляем процесс с его детьми
        if (isset($this->processes[$oContext->sLabel])) {
            $this->removeChildProcess($oContext->sLabel);
        }

        $oContext->sURL = $this->oContext->sURL;
        $oContext->sLabelPath = $this->oContext->sLabelPath . $this->sDelimiter . $oContext->sLabel;
        \Yii::$app->processList->resetProcessTreeComplete();
        $oContext->setParentProcess($this);

        return $this->processes[$oContext->sLabel] = new self($oContext);
    }

    // func

    /**
     * Получить массив данных из модуля.
     *
     * @param string $sLabel
     *
     * @return array Возвращает массив данных отработанного процесса
     */
    public function getData($sLabel = '')
    {
        return $this->oContext->getData($sLabel);
    }

    // func

    /**
     * Добавить данные для парсинга в метку.
     *
     * @depricate Проверить использование и удалить если не нужен
     *
     * @param $sLabel
     * @param $mData
     */
    public function setData($sLabel, $mData)
    {
        $this->oContext->setData($sLabel, $mData);
    }

    // func

    /**
     * Возвращает статус процесса.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->iStatus;
    }

    // func

    /**
     * Отдает true если статус psComplete.
     *
     * @return int
     */
    public function isComplete()
    {
        return $this->getStatus() == psComplete;
    }

    /**
     * Отдает true если модуль завершил работу.
     *
     * @return bool
     */
    public function isCorrectCompletion()
    {
        return in_array(
            $this->getStatus(),
            [psComplete, psExit, psRendered, psError, psBreak]
        );
    }

    /**
     * Устанавливает статус процессу.
     *
     * @param int $iStatus Константа статуса
     *
     * @return int
     */
    public function setStatus($iStatus = psComplete)
    {
        return $this->iStatus = $iStatus;
    }

    // func

    /**
     * Возвращает используемую часть URL.
     *
     * @return string
     */
    public function getUsedURL()
    {
        return $this->oContext->sUsedURL;
    }

    /**
     * Возвращает отрендеренные данные процесса.
     *
     * @return string
     */
    public function getOuterText()
    {
        return $this->oContext->getOuterText();
    }

    // func

    /**
     * Возвращает отрендеренные данные процесса.
     *
     * @param string $sOut
     *
     * @return string
     */
    public function setOut($sOut)
    {
        return $this->oContext->setOut($sOut);
    }

    // func

    /**
     * Возвращает имя класса модуля.
     *
     * @return string
     */
    public function getModuleClass()
    {
        return $this->oContext->sClassName;
    }

    // func

    /**
     * Возвращает путь по меткам вызова до текущего процесса.
     *
     * @return string
     */
    public function getLabelPath()
    {
        return $this->oContext->sLabelPath;
    }

    // func

    /**
     * Возвращает метку вызова текущего процесса.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->oContext->sLabel;
    }

    // func

    /**
     * Выполняет метод $sMethodName модуля с аргументами $aArguments.
     *
     * @param string $sMethodName Имя метода класса модуля
     * @param array $aArguments Аргументы, передаваемые модулю
     *
     * @return int Возвращает статус выполнения процесса
     */
    public function executeModuleMethod($sMethodName, $aArguments)
    {
        return $this->setStatus($this->oObject->{$sMethodName}($aArguments));
    }

    // func

    /**
     * Установить значение параметру модуля.
     *
     * @param string $sParamName Название параметра
     * @param mixed $mValue Значение параметра
     *
     * @return mixed Возвращает установленное значение
     */
    public function setParam($sParamName, $mValue)
    {
        return $this->oObject->{$sParamName} = $mValue;
    }

    // func

    /**
     * Обновляет значения параметров модуля.
     *
     * @param array $aParams
     *
     * @return bool
     */
    public function updateParams($aParams)
    {
        $this->oContext->setParams($aParams);
        $this->oObject->overlayParams($aParams);

        return true;
    }

    // func

    public function getParams()
    {
        return $this->oContext->getParams();
    }

    /**
     * Жесткая вставка.
     * Установить во входящие параметры новое значение. Значение $mValue устанавливается в
     * параметр $sParamName. Изменению подвергается POST, GET, REQUEST.
     *
     * @param string $sParamName Название параметра
     * @param mixed $mValue Значение параметра
     *
     * @return bool Возвращает true, если значение установлено либо false в противном случае
     */
    public function setRequest($sParamName, $mValue)
    {
        if (Request::set($this->oContext->sLabelPath, $sParamName, $mValue, true)) {
            return true;
        }

        // проверка на существование роутера
        if ($this->oRouter) {
            if ($this->oRouter->set($sParamName, $mValue, true)) {
                return true;
            }
        }

        return false;
    }

    // func

    /**
     * Мягкая вставка.
     * Добавить во входящие параметры новое значение. Значение $mValue устанавливается в
     * параметр $sParamName. Изменению подвергается POST, GET, REQUEST в том случае, если
     * параметр отсутствовал.
     *
     * @param string $sParamName Название параметра
     * @param  mixed$mValue Значение параметра
     *
     * @return bool Возвращает true, если значение установлено либо false в противном случае
     */
    public function addRequest($sParamName, $mValue)
    {
        if (Request::set($this->oContext->sLabelPath, $sParamName, $mValue, false)) {
            return true;
        }

        if ($this->oRouter) { // проверка на существование роутера (роутер существует только в page процессоре)
            if ($this->oRouter->set($sParamName, $mValue, false)) {
                return true;
            }
        }

        return false;
    }

    // func

    /**
     * Возвращает ссылку на класс родительского процесса.
     *
     * @return null|Process
     */
    public function getParentProcess()
    {
        return $this->oContext->getParentProcess();
    }

    // func

    /**
     * Удалить Дочерний процесс в метке вызова $sLabel.
     *
     * @param string $sLabel Метка вызова для дочернего процесса
     *
     * @return bool
     */
    public function removeChildProcess($sLabel)
    {
        if (!isset($this->processes[$sLabel])) {
            return false;
        }

        $oProcess = $this->processes[$sLabel];

        if (!$oProcess) {
            return false;
        }

        /** @var $oProcess Process */
        foreach ($oProcess->processes as $sChildLabel => $oChildProcess) {
            $this->removeChildProcess($sChildLabel);
        }

        unset($oProcess);
        \Yii::$app->processList->unregisterProcessPath($this->getLabelPath() . $this->sDelimiter . $sLabel);
        \Yii::$app->processList->resetProcessTreeComplete();
        unset($this->processes[$sLabel]);

        return true;
    }

    // func

    /**
     * Отдает ссылку на модуль.
     *
     * @return Prototype
     */
    public function getModule()
    {
        return $this->oObject;
    }
}// class

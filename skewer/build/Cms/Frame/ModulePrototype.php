<?php

namespace skewer\build\Cms\Frame;

use skewer\base\site_module;
use skewer\components\auth\CurrentAdmin;
use skewer\helpers\Linker;
use yii\base\UserException;
use yii\web\ServerErrorHttpException;

/**
 * Прототип админского модуля с собственными js файлами для отображения интерфейса.
 *
 * @project Skewer
 *
 * @Author: sapozhkov, $Author$
 * @version: $Revision$
 * @date: $Date$
 */
class ModulePrototype extends site_module\Prototype
{
    /**
     * Директория с CSS файлами модуля от корня директории модуля.
     *
     * @var string
     */
    private $sCSSDir = 'css';

    /**
     * Директория с JS файлами модуля от корня директории модуля.
     *
     * @var string
     */
    private $sJSDir = 'js';

    /** @var ModulePrototype[] Состовные компоненты модуля, содержащие экшены для конкретных сущностей */
    private $aComponents = [];

    public function clearComponent()
    {
        foreach ($this->aComponents as $oComponent) {
            unset($oComponent);
        }

        $this->aComponents = [];

        return $this;
    }

    /**
     * Добавление нового компонента - расширение функционала модуля.
     *
     * @param string $sComponentName Имя подключаемого компонента
     * @param string $sNameSpace Имя пространстка
     *
     * @throws \Exception
     *
     * @return $this
     */
    public function addComponent($sComponentName, $sNameSpace = '')
    {
        if (!$sNameSpace) {
            $sNameSpace = mb_substr(get_class($this), 0, mb_strrpos(get_class($this), '\\'));
        }

        $sModuleName = $sNameSpace . '\\Sub' . $sComponentName;

        if (!class_exists($sModuleName)) {
            throw new \Exception(printf('Не найден компонент "%s" модуля "%s"', $sComponentName, $this->getModuleName()));
        }
        $oComponent = new $sModuleName($this);

        $this->aComponents[] = $oComponent;

        return $this;
    }

    /**
     * Функция получения защищенных параметров основного объекта.
     *
     * @param string $sParamName Имя параметра
     */
    public function getParam($sParamName)
    {
        return $this->{$sParamName} ?? null;
    }

    /**
     * @param string $sParamName Имя параметра
     * @param $sValue
     */
    public function setParam($sParamName, $sValue)
    {
        if (isset($this->{$sParamName})) {
            $this->{$sParamName} = $sValue;
        }
    }

    /**
     * Инициализация модуля.
     */
    public function init()
    {
        /* @noinspection PhpParamsInspection */
        $this->setParser(parserJSON);
        $this->clearMessages();
        $this->setJSONHeader('subLibs', []);
    }

    /**
     * Обработка пришедших запросов. Составляет имя внетреннего метода из
     * пришедшего параметра cmd и отдает результат выполнения функции.
     *
     * @param string $defAction
     *
     * @return int
     */
    public function execute($defAction = '')
    {
        // запрос комманды
        $sAction = $defAction ? $defAction : $this->getStr('cmd');

        // есть - выполнить
        if ($oComp = $this->actionExists($sAction)) {
            $iStatus = psComplete;

            try {
                // убрать сообщение об ошибке (от предыдущих ответов)
                $this->unsetJSONHeader('moduleError');

                try {
                    // проверка доступа
                    $this->checkAccess();

                    // метод перед выполнением состояния
                    $this->preExecute();
                } catch (\Exception $e) {
                    throw new ServerErrorHttpException(
                        $e->getMessage(),
                        $e->getCode(),
                        $e
                    );
                }

                // выполнение заданного метода
                $iStatus = (int) call_user_func([
                    $oComp,
                    $this->getActionMethodName($sAction),
                ]);

                // нет статуса - поставить "Завершен в штатном режиме"
                if (!$iStatus) {
                    $iStatus = psComplete;
                }

                $aErrorList = $this->getErrors();
                if ($aErrorList) {
                    $this->setData('moduleErrorList', $aErrorList);
                }
                $aMessageList = $this->getMessages();
                if ($aMessageList) {
                    $this->setData('moduleMessageList', $aMessageList);
                }
                $aWarningList = $this->getWarnings();
                if ($aWarningList) {
                    $this->setData('moduleWarningList', $aWarningList);
                }

                // js события
                $aJSEvents = $this->getJSEvents();
                $this->clearJSEvents();
                if ($aJSEvents) {
                    $this->setJSONHeader('fireEvents', $aJSEvents);
                } else {
                    $this->unsetJSONHeader('fireEvents');
                }

                // прослушивание js событий
                $aJSListeners = $this->getJSListeners();
                $this->clearJSListeners();
                if ($aJSListeners) {
                    $this->setJSONHeader('listenEvents', $aJSListeners);
                } else {
                    $this->unsetJSONHeader('listenEvents');
                }
            } catch (UserException $e) {
                // выдать системную ошибку в массив выдачи модуля
                $this->setData('error', $e->getMessage());

                \Yii::error((string) $e);
            }

            return $iStatus;
        }

        $sError = 'Недопустимое состояние';

        if (CurrentAdmin::isSystemMode()) {
            $sError .= " [{$sAction}]";
        }

        $this->setJSONHeader('moduleError', $sError);

        // нет - просто отработать
        return psComplete;
    }

    public function shutdown()
    {
        parent::shutdown();
        Asset::register(\Yii::$app->view);
    }

    /**
     * Проверка доступа.
     */
    protected function checkAccess()
    {
    }

    /**
     * Отдает true если есть доступный метод для заданного состояния.
     *
     * @param $sAction
     *
     * @return null|$this|\skewer\build\Cms\Frame\ModulePrototype
     */
    public function actionExists($sAction)
    {
        if (method_exists($this, $this->getActionMethodName($sAction))) {
            return $this;
        }

        if (count($this->aComponents)) {
            foreach ($this->aComponents as $oComponent) {
                if (method_exists($oComponent, $this->getActionMethodName($sAction))) {
                    return $oComponent;
                }
            }
        }
    }

    /**
     * Разрешить выполнение модуля.
     *
     * @return bool
     */
    public function allowExecute()
    {
        return CurrentAdmin::isLoggedIn();
    }

    /**
     * Метод, выполняемый перед action меодом
     */
    protected function preExecute()
    {
    }

    /**
     * Устанавливает cmd в выходной массив.
     *
     * @param $sCmd
     */
    protected function setCmd($sCmd)
    {
        $this->setData('cmd', $sCmd);
    }

    /**
     * Добавить определение js библиотеки в вывод
     * Устанавливается путь в js и при запросе подгружается.
     *
     * @param string $sLibName имя библиотеки
     * @param string $sLayerName слой
     * @param string $sModuleName имя модуля
     */
    public function addLibClass($sLibName, $sLayerName = '', $sModuleName = '')
    {
        // сторонний компонент
        $bNotOwn = ($sLayerName or $sModuleName);

        // если не задан слой
        if (!$sLayerName) {
            $sLayerName = $this->getLayerName();
        }

        // если не задано имя модуля
        if (!$sModuleName) {
            $sModuleName = $this->getModuleName();
        }

        // добавление в список вызова
        $aNowLibs = $this->getJSONHeader('subLibs');
        if (!is_array($aNowLibs)) {
            $aNowLibs = [];
        }
        if (!in_array($sLibName, $aNowLibs)) {
            $sDir = sprintf('%s%s/%s/%s', BUILDPATH, $sLayerName, $sModuleName, $sLibName);
            $sDirName = $this->getAssetWebDir($sDir);
            $aLib = [
                'name' => $sLibName,
                'layer' => $sLayerName,
                'module' => $sModuleName,
                'dir' => $sDirName,
                'notOwn' => $bNotOwn,
            ];
            array_push($aNowLibs, $aLib);
            $this->setJSONHeader('subLibs', $aNowLibs);
        }
    }

    /**
     * Добавляет инициализационный параметр для js слоя.
     *
     * @param $sName - имя параметра
     * @param $sValue - значение
     */
    protected function addInitParam($sName, $sValue)
    {
        $aNowParams = $this->getJSONHeader('init');
        if (!is_array($aNowParams)) {
            $aNowParams = [];
        }
        $aNowParams[$sName] = $sValue;
        $this->setJSONHeader('init', $aNowParams);
    }

    /**
     * Работа с сообщениями.
     *
     * @var array
     */
    private $aMessages = [
        'errors' => [],
        'messages' => [],
        'warnings' => [],
    ];

    /**
     * Добавить сообщение.
     *
     * @param string $sHeader
     * @param string $sText
     * @param int $iDelay задержка перед скрытием.
     *      null|0 - по умолчанию
     *      int - задержка в ms
     *      -1 - не скрываемая
     */
    public function addMessage($sHeader, $sText = '', $iDelay = null)
    {
        $this->aMessages['messages'][] = [$sHeader, $sText, $iDelay];
    }

    /**
     * Возвращает набор сообщений.
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->aMessages['messages'] ? $this->aMessages['messages'] : [];
    }

    public function addWarning($sHeader, $sText = '')
    {
        $this->aMessages['warnings'][] = [$sHeader, $sText];
    }

    /**
     * Возвращает набор сообщений.
     *
     * @return array
     */
    public function getWarnings()
    {
        return $this->aMessages['warnings'] ? $this->aMessages['warnings'] : [];
    }

    /**
     * Добавить сообщение об ошибке.
     *
     * @param string $sHeader
     * @param string $sText
     * @param int $iDelay задержка перед скрытием.
     *      null|0 - по умолчанию
     *      int - задержка в ms
     *      -1 - не скрываемая
     */
    public function addError($sHeader, $sText = '', $iDelay = null)
    {
        $this->aMessages['errors'][] = [$sHeader, $sText, $iDelay];
    }

    /**
     * Возвращает набор сообщений об ошибках.
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->aMessages['errors'] ? $this->aMessages['errors'] : [];
    }

    /**
     * Очистка списка сообщений.
     */
    public function clearMessages()
    {
        $this->aMessages = [
            'errors' => [],
            'messages' => [],
            'warnings' => [],
        ];
    }

    /**
     * Задает набор языковых меток для модуля.
     *
     * @param array $aKeys набор псевдонимов языковых меток. Пример элементов массива
     *  'btn_add', // - добавляет метку для перевода
     *  'root_categ_title' => 'LeftList.root_categ_title' // - подставляет значение из словаря
     *
     * @return array
     */
    protected function setModuleLangValues($aKeys)
    {
        $aOut = $this->parseLangVars($aKeys);

        $this->addInitParam('lang', $aOut);

        return $aOut;
    }

    /**
     * Работа с JS событиями.
     */

    /**
     * Набор событий, которые должны быть вызваны.
     *
     * @var array
     */
    protected $aJSEventList = [];

    /**
     * Добавить событие на выполнение в JS части.
     *
     * @param string $sEventName - имя события
     */
    public function fireJSEvent($sEventName)
    {
        // собираем посылку
        $aData = [
            $sEventName,
        ];

        // если входных параметров больше 2
        if (func_num_args() > 1) {
            for ($i = 1; $i < func_num_args(); ++$i) {
                $aData[] = func_get_arg($i);
            }
        }

        $this->aJSEventList[] = $aData;
    }

    /**
     * Возвращает набор событий для выполнения в JS части.
     *
     * @return array
     */
    public function getJSEvents()
    {
        return $this->aJSEventList;
    }

    /**
     * Очищает набор событий для выполнения в JS части.
     */
    public function clearJSEvents()
    {
        $this->aJSEventList = [];
    }

    /**
     * Работа с JS событиями - прослушивание.
     */

    /**
     * @var array набор слушаемых событий
     */
    protected $aJSListeners = [];

    /**
     * Добавляет php подписчика для js события
     * На одно событие можно повесить только одного слушателя.
     *
     * @param string $sEventName имя js события
     * @param string $sActionName название действия в php
     */
    protected function addJSListener($sEventName, $sActionName)
    {
        $this->aJSListeners[$sEventName] = $sActionName;
    }

    /**
     * Отдает набор php подписчиков для js события.
     *
     * @return array
     */
    public function getJSListeners()
    {
        return $this->aJSListeners;
    }

    /**
     * Очищает список подписчиков js событий.
     */
    public function clearJSListeners()
    {
        $this->aJSListeners = [];
    }

    /**
     * Работа с данными.
     *
     * @param mixed $aFilter
     * @param mixed $bExclude
     */

    /**
     * Получить массив пришедших данных, с возможностью фильтрации.
     *
     * @param array $aFilter массив имен необходимых полей
     * @param bool $bExclude - флаг исключение указанных полей
     *          true - !все указанные в фильтре поля
     *          false - все что есть в ответе, кроме заданных
     *
     * @return array
     */
    public function getInData($aFilter = [], $bExclude = false)
    {
        // получить данные
        $aData = $this->get('data');
        if (!is_array($aData)) {
            $aData = [];
        }

        // если есть ограничение по полям
        if ($aFilter) {
            $aOut = [];

            // если флаг исключения
            if ($bExclude) {
                // из полученных полей
                foreach ($aData as $sName) {
                    // убрать те, что есть в фильтре
                    if (!in_array($sName, $aFilter)) {
                        $aOut[$sName] = (string) $aData[$sName];
                    }
                }
            } else {
                // взять список необходимых полей
                foreach ($aFilter as $sName) {
                    // добавить в вывод те поля, которые есть в посылке,
                    // остальные заполнить пустышками
                    $aOut[$sName] = isset($aData[$sName]) ? (string) $aData[$sName] : '';
                }
            }
        } else {
            // не задан фильтр - просто вернуть все, что есть в посылке
            $aOut = $aData;
        }

        return $aOut;
    }

    /**
     * Возвращает значение элемента во входном массиве.
     *
     * @param string $sName Имя исходного параметра
     * @param mixed $mDefault Значение, подставляемое по-умолчанию
     *
     * @return string
     */
    protected function getInDataVal($sName, $mDefault = '')
    {
        $aData = $this->get('data');
        if (!$aData or !is_array($aData)) {
            return $mDefault;
        }

        return isset($aData[$sName]) ? (string) $aData[$sName] : $mDefault;
    }

    /**
     * Отдает значение переменной из массива данных.
     *
     * @param string $sName Имя исходного параметра
     * @param mixed $mDefault Значение, подставляемое по-умолчанию
     *
     * @return int
     */
    protected function getInDataValInt($sName, $mDefault = 0)
    {
        return (int) $this->getInDataVal($sName, $mDefault);
    }

    /**
     * Отдает класс-родитель, насдедники которого могут быть добавлены в дерево процессов.
     *
     * @return string
     */
    protected function getAllowedChildClass()
    {
        return 'skewer\build\Cms\Frame\ModulePrototype';
    }

    /**
     * Добавляет в список вывода на страницу JS файл $sFileName. В условии $sCondition, если задано.
     *
     * @param string $sFileName - Имя JS-файла в директории текущего модуля
     *
     * @return bool
     */
    final public function addJsFile($sFileName)
    {
        if ($sFileName[0] === '/') {
            $sPath = '';
        } else {
            $sPath = $this->getModuleWebDir() . \DIRECTORY_SEPARATOR . $this->sJSDir . \DIRECTORY_SEPARATOR;
        }

        $sFilePath = $sPath . $sFileName;

        return Linker::addJsFile($sFilePath);
    }

    /**
     * Добавляет в список вывода на страницу CSS файл $sFileName. В условии $sCondition, если задано.
     *
     * @param string $sFileName - Имя CSS-файла в директории текущего модуля
     *
     * @return bool
     */
    final public function addCssFile($sFileName)
    {
        if ($sFileName[0] === '/') {
            $sPath = '';
        } else {
            $sPath = $this->getModuleWebDir() . \DIRECTORY_SEPARATOR . $this->sCSSDir . \DIRECTORY_SEPARATOR;
        }

        return Linker::addCssFile($sPath . $sFileName);
    }
}

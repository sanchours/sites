<?php

namespace skewer\base\site_module;

use Exception;
use ReflectionClass;
use ReflectionProperty;
use skewer\base\log\models\Log;
use skewer\helpers\Paginator;
use skewer\helpers\StringHelper;

/**
 * Прототип модуля.
 */
abstract class Prototype extends \yii\base\Component
{
    /**
     * Экземпляр Контекста вызова.
     *
     * @var null|Context
     */
    protected $oContext;

    /**
     * Директория с шаблонами модуля от корня директории модуля.
     *
     * @var string
     */
    protected $sTemplateDir = 'templates';

    /**
     * Флаг использования правил разбора url.
     *
     * @var bool
     */
    private $bUseRouting = true;

    /**
     * Зона вывода.
     *
     * @var string
     */
    protected $zone = '';

    protected $zoneType = '';

    /**
     * Категория для словаря.
     *
     * @var string
     */
    protected $languageCategory = '';

    /**
     * Имя модуля.
     *
     * @var string
     */
    protected $title = '';

    /** Плучить имя модуля без создания объекта */
    public static function getNameModule()
    {
        return basename(dirname(str_replace('\\', '/', self::className())));
    }

    /**
     * Категория для словаря.
     *
     * @return string
     */
    public function getCategoryMessage()
    {
        return $this->languageCategory;
    }

    /**
     * Задает флаг использования правил разбора url.
     *
     * @param bool $bUseRouting
     */
    public function setUseRouting($bUseRouting)
    {
        $this->bUseRouting = (bool) $bUseRouting;
    }

    /**
     * Отдает флаг использования правил разбора url.
     *
     * @return bool
     */
    public function useRouting()
    {
        return $this->bUseRouting;
    }

    /**
     * Получение группы в которой находится метка модуля.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->oContext->getLabel();
    }

    /**
     * Имя модуля.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /** @noinspection PhpMissingParentConstructorInspection
     * Создает экземпляр модуля
     *
     * @param Context $oContext Передаваемый контекст
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(Context $oContext)
    {
        $sClassName = get_class($this);
        $sModulePath = \Yii::getAlias('@' . str_replace('\\', '/', $sClassName) . '.php');
        // Заменить слэши согласно текущей операционной системе
        $sModulePath = str_replace(['\\', '/'], \DIRECTORY_SEPARATOR, $sModulePath);

        $this->oContext = $oContext;

        $this->oContext->setModuleDir(dirname($sModulePath));
        if ($this->autoInitAsset()) {
            $this->oContext->setModuleWebDir($this->getAssetWebDir($sModulePath));
        }
        $this->oContext->setTplDirectory($this->getTplDirectory());
        $this->oContext->setAddTemplateDir($this->getAddTemplateDir());

        // данные по модулю - слой и имя
        $aPathItems = explode(\DIRECTORY_SEPARATOR, trim(dirname($sModulePath), \DIRECTORY_SEPARATOR));
        $this->oContext->setModuleName(array_pop($aPathItems));
        $this->oContext->setModuleLayer(array_pop($aPathItems));

        $oConfig = \Yii::$app->register->getModuleConfig($this->oContext->getModuleName(), $this->oContext->getModuleLayer());

        $this->languageCategory = $oConfig->getLanguageCategory();

        $this->title = $oConfig->getTitle();

        $this->overlayParams($oContext->getParams());

        // Пользовательская функция инициализации модуля
        $this->onCreate();

        return true;
    }

    /**
     * Пользовательская функция инициализации модуля.
     */
    protected function onCreate()
    {
    }

    /**
     * Вернет массив дополнительных директорий(абсол. пути) с шаблонами.
     *
     * @return array
     */
    protected function getAddTemplateDir()
    {
        return [];
    }

    /**
     * Перекрывает свойства классу модуля.
     *
     * @param null|array $aOverlayParams
     */
    final public function overlayParams($aOverlayParams = null)
    {
        if (!count($aOverlayParams)) {
            return;
        }

        if (isset($aOverlayParams['zone'])) {
            $this->zone = $aOverlayParams['zone'];
            $aZoneParts = explode('\\', $aOverlayParams['zone']);
            $this->zoneType = array_pop($aZoneParts);
        }

        $oRef = new ReflectionClass(get_class($this));
        $aPropValues = $oRef->getDefaultProperties();
        $aProperties = $oRef->getProperties(ReflectionProperty::IS_PUBLIC | ReflectionProperty::IS_PROTECTED);

        if (count($aProperties)) {
            foreach ($aProperties as $oProperty) {
                if (!($oProperty instanceof ReflectionProperty)) {
                    continue;
                }

                /* Не перекрывать системные параметры модуля */
                if ($oProperty->class == 'skewer\base\site_module\Prototype') {
                    continue;
                }

                if ($oProperty->name == 'oContext') {
                    continue;
                }
                $sName = $oProperty->name;
                $this->{$sName} = $aPropValues[$sName];
                if (isset($aOverlayParams[$sName])) {
                    $this->{$sName} = $aOverlayParams[$sName];
                }
            }
        }
    }

    /**
     * Закрытый деструктор без возможности перекрытия.
     */
    final public function __destruct()
    {
    }

    // func

    /**
     * Прототип - выполняется до вызова метода Execute.
     */
    public function init()
    {
    }

    // func

    /**
     * Прототип - вызывается после метода Execute.
     */
    public function shutdown()
    {
    }

    // func

    /**
     * Разрешить выполнение модуля.
     *
     * @return bool
     */
    public function allowExecute()
    {
        return true;
    }

    // func

    /**
     * Добавляет данные для вывода в шаблонизатор
     *
     * @final
     *
     * @param string $sLabel Метка для вставки данных
     * @param mixed $mData Данные
     *
     * @return bool
     */
    final public function setData($sLabel, $mData)
    {
        $this->oContext->setData($sLabel, $mData);

        return true;
    }

    // func

    /**
     * Возвращает Данные, установленные модулем в процессе выполнения.
     *
     * @param string $sLabel Если указан параметр sLabel -  будет возвращено только то значение,
     * которое к нему прикреплено
     *
     * @return mixed
     */
    final public function getData($sLabel = '')
    {
        return $this->oContext->getData($sLabel);
    }

    // func

    /**
     * Устанавливает шаблон для вывода.
     *
     * @final
     *
     * @param string $sTemplate Шаблон для рендеринга данных
     *
     * @return bool
     */
    final public function setTemplate($sTemplate)
    {
        $this->oContext->setTemplate($sTemplate);

        return true;
    }

    // func

    /**
     * Устанавливает вывод, обработанный шаблонизатором
     *
     * @final
     *
     * @param string $sOut
     *
     * @return bool
     */
    final public function setOut($sOut)
    {
        $this->oContext->setOut($sOut);

        return true;
    }

    // func

    /**
     * Указывает тип шаблонизатора.
     *
     * @final
     *
     * @param int $iParserType
     *
     * @return bool
     */
    final public function setParser($iParserType)
    {
        $this->oContext->setParser($iParserType);

        return true;
    }

    // func

    /**
     * Возвращает путь к директории модуля.
     *
     * @final
     *
     * @return string
     */
    final public function getModuleDir()
    {
        return $this->oContext->getModuleDir();
    }

    // func

    /**
     * Возвращает web путь к директории модуля.
     *
     * @final
     *
     * @return string
     */
    final public function getModuleWebDir()
    {
        return $this->oContext->getModuleWebDir();
    }

    // func

    /**
     * Ищет разобранный GET/POST/JSON целочисленный параметр.
     *
     * @param string $sName Имя исходного параметра
     * @param int $mDefault Значение, подставляемое по-умолчанию
     *
     * @return int
     */
    final public function getInt($sName, $mDefault = 0)
    {
        return (int) self::getStr($sName, $mDefault);
    }

    // func

    /**
     *  Ищет разобранный GET/POST/JSON строковый параметр.
     *
     * @param string $sName Имя исходного параметра
     * @param string $mDefault Значение, подставляемое по-умолчанию
     *
     * @return string
     */
    final public function getStr($sName, $mDefault = '')
    {
        /**
         * Ищем в POST и JSON параметрах
         * Перекрываем GET параметрами.
         */
        $sVal = Request::getStr($sName, $this->oContext->sLabelPath);
        if ($sVal !== null) {
            return $sVal;
        }
        //if(Request::getStr($this->oContext->sLabelPath, $sName, $sVal)) return $sVal;
        if ($this->oContext->oProcess->oRouter->getStr($sName, $sVal)) {
            return $sVal;
        }
        //if($this->oContext->oProcessor->oRouter->getStr($sName,$sVal)) return $sVal;

        return $mDefault;
    }

    // func

    /**
     *  Ищет разобранный GET/POST/JSON неопределенный параметр.
     *
     * @param string $sName Имя исходного параметра
     * @param string $mDefault Значение, подставляемое по-умолчанию
     *
     * @return mixed
     */
    final public function get($sName, $mDefault = '')
    {
        /**
         * Ищем в POST и JSON параметрах
         * Перекрываем GET параметрами.
         */

        //$val = Request::getStr($this->oContext->sLabelPath, $sName, $sVal);
        $sVal = Request::getStr($sName, $this->oContext->sLabelPath);
        if ($mDefault !== $sVal && $sVal !== null) {
            return $sVal;
        }
        if ($this->oContext->oProcess->oRouter->getStr($sName, $sVal)) {
            return $sVal;
        }
        //if($this->oContext->oProcessor->oRouter->getStr($sName,$sVal)) return $sVal;

        return $mDefault;
    }

    // func

    /**
     * Сохраняет во входной массив (GET/POST/JSON) значение.
     *
     * @param $sName
     * @param $mValue
     */
    final public function set($sName, $mValue)
    {
        Request::set($this->oContext->sLabelPath, $sName, $mValue);
    }

    /**
     * Получение параметров пришедших на изменение для модуля.
     */
    final protected function getPost()
    {
        return $_POST;
    }

    /**
     * Возвращает имя текущего модуля.
     *
     * @return string
     */
    final public function getModuleName()
    {
        return $this->oContext->getModuleName();
    }

    /**
     * отдает имя слоя.
     *
     * @return string
     */
    public function getLayerName()
    {
        return $this->oContext->getModuleLayer();
    }

    final public function getModuleNameAdm()
    {
        return $this->getModuleName() . 'Adm';
    }

    /**
     * Устаналивает имя текущего модуля.
     *
     * @param string $sModuleName Имя модуля
     *
     * @return bool
     */
    final public function setModuleName($sModuleName)
    {
        return $this->oContext->setModuleName($sModuleName);
    }

    // func

    /**
     * Возвращает значение внутреннего поля.
     *
     * @param string $sFieldName Имя поля
     * @param null $mDef Значение по умолчанию
     *
     * @return mixed
     */
    public function getModuleField($sFieldName, $mDef = null)
    {
        return $this->{$sFieldName} ?? $mDef;
    }

    /**
     * Возвращает экземпляр процесса по цепочке меток до него.
     *
     * @final
     *
     * @param string $sPath Путь от корневого процесса до искомого
     * @param int $iStatus Статус искомого процесса. psAll - выбрать все
     *
     * @return int|Process
     */
    final public function getProcess($sPath, $iStatus = psComplete)
    {
        return \Yii::$app->processList->getProcess($sPath, $iStatus);
    }

    // func

    /**
     * Отдает флаг фозможности создания наследников для данного модуля
     * в дереве процессов.
     *
     * @return bool
     */
    protected function canBeParent()
    {
        return true;
    }

    /**
     * Отдает класс-родитель, насдедники которого могут быть добавлены в дерево процессов.
     *
     * @return string
     */
    protected function getAllowedChildClass()
    {
        return Prototype::className();
    }

    /**
     * Adding child process in parent process.
     *
     * @final
     *
     * @param Context $oContext
     *
     * @throws \Exception
     *
     * @return bool|Process
     */
    public function addChildProcess(Context $oContext)
    {
        // проверка возможности добавлять подчиненные модули
        if (!$this->canBeParent()) {
            throw new \Exception(sprintf('Module `%s` can not have children.', $oContext->getClassName()));
        }
        // проверка добавляемого модуля
        $sParentClass = $this->getAllowedChildClass();
        if ($sParentClass) {
            if (!class_exists($oContext->getClassName())) {
                throw new \Exception(sprintf('Class `%s` not found', $oContext->getClassName()));
            }
            $oRC = new \ReflectionClass($oContext->getClassName());
            if (!$oRC->isSubclassOf($sParentClass)) {
                throw new \Exception(sprintf('Module `%s` must be an instance of `%s`', $oContext->getClassName(), $sParentClass));
            }
        }

        return $this->oContext->oProcess->addChildProcess($oContext);
    }

    /**
     * Возвращает список ссылок на дочерние процессы.
     *
     * @return Process[]
     */
    final public function getChildProcesses()
    {
        return $this->oContext->oProcess->processes;
    }

    // func

    /**
     * Возвращает ссылку на дочерний процесс по метке $sLabel его вызова.
     *
     * @param string $sLabel Метка вызова дочернего процесса
     *
     * @return bool|Process Возвращает ссылку на процесс либо false
     */
    final public function getChildProcess($sLabel)
    {
        return (isset($this->oContext->oProcess->processes[$sLabel])) ? $this->oContext->oProcess->processes[$sLabel] : false;
    }

    // func

    /**
     * Устанавливает статус $iStatus дочернему процессу в метке вызова $sLabel.
     *
     * @param string $sLabel Название метки вызова для дочернего процесса
     * @param int $iStatus Константа статуса
     *
     * @return bool Возвращает true если процесс найден и false в противном случае
     */
    final public function setChildProcessStatus($sLabel, $iStatus)
    {
        if (isset($this->oContext->oProcess->processes[$sLabel])) {
            /* @noinspection PhpUndefinedMethodInspection */
            $this->oContext->oProcess->processes[$sLabel]->setStatus($iStatus);

            return true;
        }

        return false;
    }

    // func

    /**
     * Удаляет подчиненный процесс
     *
     * @param $sLabel
     *
     * @return bool
     */
    final public function removeChildProcess($sLabel)
    {
        return $this->oContext->oProcess->removeChildProcess($sLabel);
    }

    /**
     * Удаляет все подчиненные процессы.
     *
     * @return int количество удаленных процессов
     */
    final public function removeAllChildProcess()
    {
        $aProcesses = $this->getChildProcesses();

        // удалить все по очереди
        /* @var $oProcess Process */
        foreach ($aProcesses as $oProcess) {
            $this->removeChildProcess($oProcess->getLabel());
        }

        return count($aProcesses);
    }

    /**
     * Устанавливает новый параметр окружения с именем $sName и значением $mValue.
     *
     * @param string $sName Имя параметра окружения
     * @param string $mValue Значение параметра окружения
     */
    final public function setEnvParam($sName, $mValue)
    {
        \Yii::$app->environment->set($sName, $mValue);
    }

    /**
     * Возвращает параметр окружения $sName либо $mDefault в случае, если параметр отсутствует.
     *
     * @param string $sName Имя параметра окружения
     * @param bool|mixed $mDefault Значение, возвращаемое, если параметр не был найден
     *
     * @return mixed
     */
    final public function getEnvParam($sName, $mDefault = false)
    {
        return \Yii::$app->environment->get($sName, $mDefault);
    }

    /**
     * Возвращает список параметров окружения (обших переменных в рамках дерева процессов).
     *
     * @return mixed
     */
    final public function getEnvParamsList()
    {
        return \Yii::$app->environment->getAll();
    }

    /**
     * Возвращает путь к директории хранения шаблонов модуля.
     *
     * @return string
     */
    public function getTplDirectory()
    {
        return $this->sTemplateDir;
    }

    // func

    /**
     * Изменяет путь к директории хранения шаблонов модуля.
     *
     * @param string $sDir Директория расположения CSS файлов модуля
     *
     * @return string
     */
    public function setTplDirectory($sDir)
    {
        $this->oContext->setTplDirectory($sDir);

        return $this->sTemplateDir = $sDir;
    }

    // func

    /**
     * Генирирует массив для постраничного вывода.
     *
     * @param int $iPage Текущая страница постраничного
     * @param int $iCount Количество элементов в списке (например, товаров в категории)
     * @param int $iSectionId Раздел для вывода постраничного списка
     * @param array $aURLParams массив дополнительных GET параметров
     * @param array $aParams Параметры настройки внешнего вида постраничного см. Paginator
     * @param string $sLabel Метка вывода сгенерированного массива в шаблон
     * @param bool $bHideCanonicalPagination Флаг запрещающий в код вставлять canonical пагинации
     *
     * @return string
     */
    final public function getPageLine($iPage, $iCount, $iSectionId, $aURLParams = [], $aParams = [], $sLabel = 'aPages', $bHideCanonicalPagination = false)
    {
        $aURL[$this->oContext->sClassName] = (count($aURLParams)) ? $aURLParams : [];

        return $this->oContext->setData($sLabel, Paginator::getPageLine($iPage, $iCount, $iSectionId, $aURL, $aParams, $bHideCanonicalPagination));
    }

    // func

    /**
     * Установить дополнительный JSON Header в response package от модуля.
     *
     * @param string $sKey Заголовок
     * @param mixed $mValue Значение заголовка
     */
    final public function setJSONHeader($sKey, $mValue)
    {
        $this->oContext->setJSONHeader($sKey, $mValue);
    }

    /**
     * Удалить дополнительный JSON Header в response package от модуля.
     *
     * @param string $sKey Заголовок
     *
     * @return bool
     */
    final public function unsetJSONHeader($sKey)
    {
        return $this->oContext->unsetJSONHeader($sKey);
    }

    /**
     * Получить дополнительный JSON Header по названию $sKey.
     *
     * @param $sKey
     *
     * @return null|mixed
     */
    final public function getJSONHeader($sKey)
    {
        return $this->oContext->getJSONHeader($sKey);
    }

    /**
     * Отрендерить шаблон, вернуть строку с результатом
     *
     * @param $sTemplate - шаблон
     * @param array $aData - массив для парсинга
     *
     * @return string
     */
    public function renderTemplate($sTemplate, $aData = [])
    {
        $sDirTemplate = $this->getModuleDir() . $this->getTplDirectory() . \DIRECTORY_SEPARATOR;

        if (StringHelper::endsWith($sTemplate, '.twig')) {
            $sHtml = Parser::parseTwig($sTemplate, $aData, $sDirTemplate);
        } else {
            if (StringHelper::endsWith($sTemplate, '.php')) {
                $sHtml = \Yii::$app->getView()->renderPhpFile($sDirTemplate . $sTemplate, $aData);
            } else {
                throw new \Exception('Unsupported parser');
            }
        }

        return $sHtml;
    }

    /**
     * Возвращает параметр конфигурации модуля по ключу $key в слое $sLayer.
     *
     * @param string $sParamPath
     *
     * @return mixed
     */
    final protected function getConfigParam($sParamPath)
    {
        return \Yii::$app->register->getModuleConfigParam(
            $sParamPath,
            $this->getModuleName(),
            $this->getLayerName()
        );
    }

    /**
     * Добавление сообщения в журнал в базе даных.
     *
     * @param string $sTitle название сообщения
     * @param array|string $mDescription описание
     *
     * @return bool|int
     */
    public function addModuleNoticeReport($sTitle, $mDescription = '')
    {
        return Log::addNoticeReport($sTitle, $mDescription, Log::logUsers, $this->getModuleName());
    }

    /**
     * Вычисляет директорию для клиентских файлов (с учетом asset механики).
     *
     * @param string $sModulePath полное имя основного класса модуля
     *
     * @throws Exception
     *
     * @return string отдает имя директории от корня url
     */
    public function getAssetWebDir($sModulePath = '')
    {
        if (!$sModulePath) {
            $sDirName = '';
            $sThisClass = '\\' . get_class($this);
            $sAssetClass = mb_substr($sThisClass, 0, mb_strrpos($sThisClass, '\\')) . '\Asset';
        } else {
            $sDirName = '/skewer' . mb_substr(dirname($sModulePath), mb_strlen(RELEASEPATH) - 1);
            $sAssetClass = str_replace('/', '\\', mb_substr($sDirName, 1)) . '\Asset';
        }

        // Заменить возможные слэши разделения директорий windows на слэши для namespace
        $sAssetClass = str_replace('/', '\\', $sAssetClass);

        if (class_exists($sAssetClass)) {
            if (!is_subclass_of($sAssetClass, 'yii\web\AssetBundle')) {
                throw new \Exception('Module asset must be extended from yii\web\AssetBundle');
            }
            /** @var \yii\web\AssetBundle $sAssetClass */
            $bundle = $sAssetClass::register(\Yii::$app->getView());

            return $bundle->baseUrl;
        }

        return $sDirName;
    }

    /**
     * Парсинг массива сообщений для модуля.
     *
     * @param $aMessages
     * @param string $category
     *
     * @return array
     */
    public function parseLangVars($aMessages, $category = '')
    {
        $aOut = [];

        foreach ($aMessages as $mKey => $sAlias) {
            $aOut[is_numeric($mKey) ? $sAlias : $mKey] = \Yii::t($category ?: $this->getCategoryMessage(), $sAlias);
        }

        return $aOut;
    }

    /**
     * Метод - исполнитель функционала.
     *
     * @abstract
     */
    abstract public function execute();

    /**
     * Метод отдает флаг автоматической регистрации Asset для модуля
     * Если необходимо активировать вручную - можно перекрыть метод и вернуть false.
     *
     * @return bool
     */
    public function autoInitAsset()
    {
        return true;
    }

    /**
     * Отдает имя метода по имени состояния.
     *
     * @param string $sAction
     *
     * @return string
     */
    public function getActionMethodName($sAction)
    {
        return 'action' . ($sAction ? ucfirst($sAction) : $this->getBaseActionName());
    }

    /**
     * Отддает имя первичного состояния (если не задано).
     *
     * @return string
     */
    public function getBaseActionName()
    {
        return 'Init';
    }

    /**
     * Вызывается перед рендерингом модуля.
     */
    public function beforeRender()
    {
    }

    /**
     * Добавит, выполнит и отрендерит процесс
     *
     * @param string $sLabel - метка вызова процесса
     * @param string $sClassNameModule - className модуля
     * @param array $aParams - параметры процесса
     *
     * @return string - результат работы процесса
     */
    public function createAndExecuteProcess($sLabel, $sClassNameModule, $aParams = [])
    {
        $oContext = new Context($sLabel, $sClassNameModule, ctModule);
        $oContext->setParams($aParams);

        $oProcess = $this->addChildProcess($oContext);

        $oProcess->execute();
        $oProcess->render();

        $sOuterHtml = trim($oProcess->getOuterText());

        return $sOuterHtml;
    }

    /**
     * Это главный модуль на страницу?
     *
     * @return bool
     */
    public function isMainModule()
    {
        return $this->getLabel() === 'content';
    }
}// class

<?php

namespace skewer\base\site_module;

/**
 * Передача контекста вызова.
 */
class Context
{
    /**
     * Входные параметры вызова модуля.
     *
     * @var array
     */
    protected $aParams = [];
    /**
     * Идентификатор объекта.
     *
     * @var string
     */
    protected $sObjectId = '';
    /**
     * Тип шаблонизатора.
     *
     * @var int
     */
    protected $iParserType = parserTwig;
    /**
     * Массив выходных данных.
     *
     * @var array
     */
    protected $aData = [];
    /**
     * Локальный (относительно корня модуля) путь к шаблону.
     *
     * @var string
     */
    protected $sTemplate = '';
    /**
     * Отрендеренный результат работы процесса.
     *
     * @var string
     */
    protected $sOut = '';

    /**
     * Путь к директории модуля.
     *
     * @var string
     */
    protected $sModuleDir = '';
    /**
     * Имя модуля.
     *
     * @var string
     */
    protected $sModuleName = '';

    /**
     * Имя метки для вставки данных.
     *
     * @var string
     */
    public $sLabel = '';
    /**
     * Путь по меткам от корневого процесса до текущего включительно.
     *
     * @var string
     */
    public $sLabelPath = '';

    /**
     * Ссылка на процесс (запустивший модуль на выполнение).
     *
     * @var null|Process
     */
    public $oProcess;

    /**
     * Ссылка на родительский процесс (запустивший модуль на выполнение).
     *
     * @var null|Process
     */
    private $oParentProcess;

    /**
     * Имя класса запускаемого в процессе модуля.
     *
     * @var string
     */
    public $sClassName = '';
    /**
     * Неразобранная роутером часть URL.
     *
     * @var string
     */
    public $sURL = '';

    /**
     * Разобранная роутером часть URL.
     *
     * @var string
     */
    public $sUsedURL = '';

    /**
     * Тип вызова процесса (page | Module).
     *
     * @var int
     */
    public $iCallType = '';
    /**
     * GET массив.
     *
     * @var array
     */
    public $aGet = [];

    /**
     * Путь до директории модуля от корня web-сервера с учетом alias.
     *
     * @var string
     */
    private $sModuleWebDir = '';

    /**
     * Массив дополнительных JSON headers? возвращаемых из модуля.
     *
     * @var array
     */
    protected $aJSONHeaders = [];

    /**
     * Директория с шаблонами модуля от корня директории модуля.
     *
     * @var string
     */
    private $sTemplateDir = '';

    /**
     * Массив дополнительных директорий(абсол. пути) с шаблонами.
     *
     * @var array
     */
    private $aAddTemplateDir = [];

    /**
     * Имя слоя.
     *
     * @var string
     */
    protected $sModuleLayer = '';

    /**
     * Создает экземпляр контекста.
     *
     * @param string $sLabel Метка вызова
     * @param string $sClassName Имя класса вызываемого модуля
     * @param int $iCallType Тип вызова
     * @param array $aParams Параметры вызова модуля
     */
    public function __construct($sLabel, $sClassName, $iCallType, $aParams = [])
    {
        $this->sLabel = $sLabel;
        $this->aParams = $aParams;
        $this->sClassName = $sClassName;
        $this->iCallType = $iCallType;
    }

    // func

    /**
     * Отдает метку вызова.
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->sLabel;
    }

    /**
     * Сохраняет данные в метку.
     *
     * @param string $sLabel Метка установки данных
     * @param mixed $mData Данные
     *
     * @return string
     */
    public function setData($sLabel, $mData)
    {
        return $this->aData[$sLabel] = $mData;
    }

    // func

    /**
     * Устанавливает шаблон вывода.
     *
     * @param string $sTemplate
     *
     * @return bool
     */
    public function setTemplate($sTemplate)
    {
        $this->sTemplate = $sTemplate;

        return true;
    }

    // func

    /**
     * Устанавливает ответ, обработанный шаблонизатором
     *
     * @param string $sOut
     *
     * @return bool
     */
    public function setOut($sOut)
    {
        $this->sOut = $sOut;

        return true;
    }

    // func

    /**
     * Возвращает ответ, обработанный шаблонизатором
     *
     * @return string
     */
    public function getOuterText()
    {
        return $this->sOut;
    }

    /**
     * Устанавливает тип шаблонизатора.
     *
     * @param int $iParserType
     *
     * @return bool
     */
    public function setParser($iParserType)
    {
        $this->iParserType = $iParserType;

        return true;
    }

    // func

    /**
     * Устанавливает директорию модуля.
     *
     * @param string $sModuleDir
     *
     * @return bool
     */
    public function setModuleDir($sModuleDir)
    {
        $this->sModuleDir = $sModuleDir . '/';

        return true;
    }

    // func

    /**
     * Устанавливает имя модуля.
     *
     * @param string $sModuleName Имя модуля
     *
     * @return bool
     */
    public function setModuleName($sModuleName)
    {
        $this->sModuleName = $sModuleName;

        return true;
    }

    // func

    /**
     * Возвращает результаты (массив) работы модуля.
     *
     * @param string $sLabel метка данных
     *
     * @return array|mixed
     */
    public function getData($sLabel = '')
    {
        return (isset($this->aData[$sLabel])) ? $this->aData[$sLabel] : $this->aData;
    }

    // func

    /**
     * Возвращает относительный путь к шаблону рендеринга.
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->sTemplate;
    }

    // func

    /**
     * Вернет массив дополнительных директорий(абсол. пути) с шаблонами.
     *
     * @return array
     */
    public function getAddTemplateDir()
    {
        return $this->aAddTemplateDir;
    }

    /**
     * Установить массив дополнительных директорий(абсол. пути) с шаблонами.
     *
     * @param array $aDirs
     */
    public function setAddTemplateDir($aDirs = [])
    {
        $this->aAddTemplateDir = $aDirs;
    }

    /**
     * Возвращает тип шаблонизатора для модуля.
     *
     * @return int
     */
    public function getParser()
    {
        return $this->iParserType;
    }

    // func

    /**
     * Возвращает путь к директории модуля.
     *
     * @return string
     */
    public function getModuleDir()
    {
        return $this->sModuleDir;
    }

    // func

    /**
     * Возвращает параметры вызова.
     *
     * @return array
     */
    public function getParams()
    {
        return $this->aParams;
    }

    // func

    /**
     * Возвращает имя модуля.
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->sModuleName;
    }

    // func

    /**
     * Очищает массив данных, возвращенных модулем
     *
     * @return array
     */
    public function clearData()
    {
        return $this->aData = [];
    }

    // func

    /**
     * Возвращает ссылку на родительский процесс
     *
     * @return null|Process
     */
    public function getParentProcess()
    {
        return $this->oParentProcess;
    }

    // func

    /**
     * Устанавливает родительский процесс
     *
     * @param Process $oParentProcess
     *
     * @return Process
     */
    public function setParentProcess(Process &$oParentProcess)
    {
        return $this->oParentProcess = $oParentProcess;
    }

    // func

    /**
     *Устанавливает имя слоя модуля.
     *
     * @param $sModuleLayer
     */
    public function setModuleLayer($sModuleLayer)
    {
        $this->sModuleLayer = $sModuleLayer;
    }

    /**
     * Возвращает имя слоя модуля.
     *
     * @return string
     */
    public function getModuleLayer()
    {
        return $this->sModuleLayer;
    }

    /**
     * Возвращает корневую директрию модуля от корня web-сервера с учетом alias.
     *
     * @return string
     */
    public function getModuleWebDir()
    {
        if ($this->sModuleWebDir === null) {
            $this->sModuleWebDir = $this->oProcess->getModule()->getAssetWebDir();
        }

        return $this->sModuleWebDir;
    }

    /**
     * Устанавливает корневую директрию модуля от корня web-сервера с учетом alias.
     *
     * @param string $sVal Путь до директории модуля
     *
     * @return mixed
     */
    public function setModuleWebDir($sVal)
    {
        return $this->sModuleWebDir = $sVal;
    }

    /**
     * Возвращает JSON Header по ключу $sKey.
     *
     * @param string $sKey Название ключа заголовка
     */
    public function getJSONHeader($sKey)
    {
        return isset($this->aJSONHeaders[$sKey]) ? $this->aJSONHeaders[$sKey] : null;
    }

    /**
     * Устанавливает дополнительный JSON заголовок $sKey со значением $mValue.
     *
     * @param string $sKey Заголовок
     * @param mixed $mValue Значение заголовка
     *
     * @return array Возвращает значение зоголовка
     */
    public function setJSONHeader($sKey, $mValue)
    {
        return $this->aJSONHeaders[$sKey] = $mValue;
    }

    /**
     * Убирает дополнительный JSON заголовок $sKey со значением $mValue.
     *
     * @param string $sKey Заголовок
     *
     * @return bool - true - был и удален / false - не было или не удален
     */
    public function unsetJSONHeader($sKey)
    {
        if (isset($this->aJSONHeaders[$sKey])) {
            unset($this->aJSONHeaders[$sKey]);

            return true;
        }

        return false;
    }

    /**
     * Возвращает список добавленных JSON Headers.
     *
     * @return array
     */
    public function getJSONHeadersList()
    {
        return $this->aJSONHeaders;
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
        return $this->sTemplateDir = $sDir;
    }

    // func

    public function setParams($aParams)
    {
        $this->aParams = $aParams;
    }

    // func

    /**
     * Отдает имя класса.
     *
     * @return string
     */
    public function getClassName()
    {
        return $this->sClassName;
    }
}// class

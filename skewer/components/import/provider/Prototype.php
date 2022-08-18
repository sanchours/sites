<?php

namespace skewer\components\import\provider;

use skewer\components\import;
use yii\helpers\ArrayHelper;

/**
 * Прототип объект для запроса данных из файла.
 */
abstract class Prototype
{
    /** @var import\Config настройки шаблона */
    private $config;

    /** @var string имя файла для импорта */
    protected $file = '';

    /** @var bool Флаг того, что файл можно читать */
    protected $canRead = true;

    /**
     * Общие параметры для редактирования.
     *
     * @var array
     */
    private $parentParameters = [
    ];

    /**
     * Параметры для редактирования.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Кодировка.
     *
     * @var string
     */
    protected $codding = 'utf-8';

    /**
     * Возвращает список расширений файлов для провайдера.
     *
     * @return []
     */
    abstract public function getAllowedExtension();

    /**
     * @throws \Exception
     */
    final public function __construct(import\Config $oConfig)
    {
        $this->config = $oConfig;

        switch ($this->getConfigVal('type')) {
            case import\Api::Type_Url:
                $this->file = $this->getConfigVal('file');
                break;

            case import\Api::Type_File:
                $this->file = WEBPATH . $this->getConfigVal('source');
                break;

            case import\Api::Type_Path:
                $this->file = ROOTPATH . $this->getConfigVal('source');
                break;
        }

        if (!$this->file) {
            $this->fail(\Yii::t('import', 'error_not_file'));
        }

        if (!file_exists($this->file)) {
            $this->fail(\Yii::t('import', 'error_not_exist_file'));
        }

        /* Проверка формата */
        $this->validateFormat();

        $this->codding = $this->getConfigVal('coding', import\Api::utf);

        /* Инициализация параметров */
        $this->initParam();

        /* Инициализация провайдера */
        $this->init();

        /* проверка кодировки */
        $this->checkCoding();
    }

    /**
     * Разрешение на чтение.
     *
     * @return bool
     */
    public function canRead()
    {
        return $this->canRead;
    }

    /**
     * Инициализация.
     */
    protected function init()
    {
    }

    /**
     * Метод, вызываемый перед началом итеративного чтения.
     *
     * @return mixed
     */
    public function beforeExecute()
    {
    }

    /**
     * Метод, вызываемый после прохождения итераций.
     *
     * @return mixed
     */
    public function afterExecute()
    {
    }

    /**
     * Отдает массив данных - одну прочитанную строку для импорта
     * Или false, если больше строк для чтения нет
     *
     * @return array|bool
     */
    abstract public function getRow();

    /**
     * Пример данных из файла.
     *
     * @return string
     */
    abstract public function getExample();

    /**
     * Строка данных для информации.
     *
     * @return array
     */
    abstract public function getInfoRow();

    /**
     * Строка из файла.
     *
     * @return string
     */
    abstract public function getPureString();

    /**
     * Отдает значение из конфига по имени.
     *
     * @param string $sName имя параметра (можно вложенное через .)
     * @param string $sDefault значение по умолчанию
     *
     * @return mixed
     */
    public function getConfigVal($sName, $sDefault = '')
    {
        return $this->config->getParam($sName, $sDefault);
    }

    /**
     * Сохраняет значение в конфиг по имени.
     *
     * @param string $sName имя параметра
     * @param string $sValue значение
     *
     * @return mixed
     */
    public function setConfigVal($sName, $sValue)
    {
        $this->config->setParam($sName, $sValue);
    }

    /**
     * Инициализация параметров.
     */
    private function initParam()
    {
        foreach ($this->getParameters() as $sKey => $mVal) {
            if (isset($this->{$sKey})) {
                $this->{$sKey} = $this->getConfigVal($sKey, $this->{$sKey});
            }
        }
    }

    /**
     * Проверка правильной установки кодировки.
     *
     * @throws \Exception
     */
    protected function checkCoding()
    {
        $aRow = $this->getPureString();
        if ($aRow) {
            if (import\Api::detect_encoding($aRow) !== $this->codding) {
                $this->fail(\Yii::t('import', 'error_codding'));
            }
        }
    }

    /**
     * Выбрасывает исключение.
     *
     * @param $sMes
     *
     * @throws \Exception
     */
    final protected function fail($sMes)
    {
        throw new \Exception($sMes);
    }

    /**
     * Параметры для редактирования.
     *
     * @return array
     */
    public function getParameters()
    {
        return ArrayHelper::merge($this->parentParameters, $this->parameters);
    }

    /**
     * Раскодирование.
     *
     * @param $mData
     *
     * @return mixed
     */
    protected function encode($mData)
    {
        return ($this->codding == import\Api::windows) ? import\Api::decode($mData) : $mData;
    }

    /**
     * Проверка на соответствие формату файла.
     *
     * @throws \Exception
     */
    private function validateFormat()
    {
        $aExtAllowed = $this->getAllowedExtension();

        $ext = mb_substr($this->file, mb_strrpos($this->file, '.') + 1);
        $ext = (mb_strrpos($ext, '?') !== false) ? mb_stristr($ext, '?', true) : $ext;

        if (!$ext || !is_array($aExtAllowed) || !in_array($ext, $aExtAllowed)) {
            $this->fail(\Yii::t('import', 'error_no_valid_format_file'));
        }
    }

    /**
     * Вернет путь к файлу импорта.
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }
}

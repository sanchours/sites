<?php

namespace skewer\components\import\field;

use skewer\components\catalog\GoodsRow;
use skewer\components\import;
use skewer\components\import\Config;

/**
 * Прототип поля обработчика данных для импорта.
 */
abstract class Prototype
{
    /** @var string имя поля в карточке товаров */
    protected $fieldName = '';

    /**
     * набор идентификаторов полей
     * Может содержать как цифры, так и строки в зависимости от провайдера данных.
     *
     * @var string[]
     */
    protected $importFieldNames;

    /**
     * данные, пришедшие из выгрузки в виде массива
     * В качестве индексов может содержать как цифры,
     * так и строки в зависимости от провайдера данных.
     *
     * @var []
     */
    protected $values = [];

    /* @var array Параметры полей для редактора настроек полей в админке */
    protected static $parameters = [
    ];

    /** @var Config Конфиг */
    protected $config;

    /** @var import\Task $oTask ссылка на задачу импорта */
    private $task;

    /** @var import\Logger */
    protected $logger;

    /**
     * флаг отвечающий за уникальное поле в импорте.
     *
     * @return bool
     */
    public function isUnique()
    {
        return false;
    }

    /**
     * Флаг отвечающий за раздел импорта.
     *
     * @return bool
     */
    public function isSection()
    {
        return false;
    }

    /**
     * Флаг пропуска поля при импорте.
     *
     * @return bool
     */
    public function skipField()
    {
        return false;
    }

    /**
     * @param array $fields
     * @param string $sFieldName
     * @param import\Task $oTask
     */
    public function __construct($fields, $sFieldName, import\Task $oTask)
    {
        $this->importFieldNames = $fields;
        $this->fieldName = $sFieldName;
        $this->task = $oTask;
        $this->config = $this->getTask()->getConfig();
        $this->logger = $this->getTask()->getLogger();
        $this->initParams();
    }

    /**
     * Инициализация объекта.
     */
    public function init()
    {
    }

    /**
     * Имя поля.
     *
     * @return string
     */
    final public function getName()
    {
        return $this->fieldName;
    }

    /**
     * Получение текущего Task объекта.
     *
     * @return import\Task
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * Установка данных.
     *
     * @param [] $data
     */
    final public function loadData($data)
    {
        foreach ($this->importFieldNames as $sName) {
            if (isset($data[$sName])) {
                $this->values[$sName] = trim($data[$sName]);
            }
        }
    }

    /**
     * Операции, выполняемые до обработки товара.
     */
    public function beforeExecute()
    {
    }

    /**
     * Операции перед сохранением товара.
     */
    public function beforeSave()
    {
    }

    /**
     * Сохранение.
     */
    final public function execute()
    {
        $sField = $this->fieldName;

        $this->getGoodsRow()->setData([$sField => $this->getValue()]);
    }

    /**
     * Отдает значение на сохранение в запись товара.
     *
     * @return mixed
     */
    abstract public function getValue();

    /**
     * Функция, вызываемая после сохранения записи.
     */
    public function afterSave()
    {
    }

    /**
     * Функция очистки внутренних данных.
     */
    final public function dropDown()
    {
        $this->values = [];
    }

    /**
     * Завершение работы.
     */
    public function shutdown()
    {
    }

    /**
     * Пропуск строки.
     *
     * @param mixed $bSkip
     */
    final protected function skipCurrentRow($bSkip)
    {
        $this->getTask()->skipCurrentRow($bSkip);
    }

    /**
     * Получение товара.
     *
     * @return GoodsRow
     */
    final protected function getGoodsRow()
    {
        return $this->getTask()->goodsRow;
    }

    /**
     * Задание товара.
     *
     * @param GoodsRow $oGoodsRow
     */
    final protected function setGoodsRow(GoodsRow $oGoodsRow)
    {
        $this->getTask()->goodsRow = $oGoodsRow;
    }

    /**
     * Статус импорта.
     *
     * @return mixed
     */
    final protected function getImportStatus()
    {
        return $this->config->getParam('importStatus');
    }

    /**
     * Получаем карточку.
     *
     * @return mixed
     */
    final protected function getCard()
    {
        return $this->config->getParam('card');
    }

    /**
     * Получение id текущей задачи импорта.
     *
     * @return int
     */
    final protected function getTaskId()
    {
        return $this->getTask()->getId();
    }

    /**
     * Инициализация параметров.
     */
    private function initParams()
    {
        $aParams = $this->config->getParam('fields.' . $this->fieldName . '.params');
        foreach (static::getParameters() as $key => $val) {
            if (isset($this->{$key})) {
                $this->{$key} = (isset($aParams[$key])) ? $aParams[$key] : $val['default'];
            }
        }
    }

    /**
     * Параметры для редактирования.
     */
    public static function getParameters()
    {
        return static::$parameters;
    }

    /**
     * Имя класса.
     *
     * @return string
     */
    public static function className()
    {
        return get_called_class();
    }

    /**
     * Получить системное имя поля(последняя часть имени класса).
     *
     * @return string
     */
    public static function getSystemNameField()
    {
        $iPos = mb_strrpos(self::className(), '\\');

        return mb_substr(self::className(), $iPos + 1);
    }
}

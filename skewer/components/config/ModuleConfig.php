<?php

namespace skewer\components\config;

/**
 * Конфигурация модуля.
 */
class ModuleConfig
{
    /** системное имя модуля */
    const NAME = 'name';

    /** название модуля */
    const TITLE = 'title';

    /** версия модуля */
    const VERSION = 'version';

    /** описание модуля */
    const DESCRIPTION = 'description';

    /** номер ревизии */
    const REVISION = 'revision';

    /** имя слоя */
    const LAYER = 'layer';

    /** зависимости */
    const DEPENDENCY = 'dependency';

    /** категория для словарей */
    const LANGUAGE_CATEGORY = 'languageCategory';

    /** @var array данные конфигурации модуля */
    private $aData;

    /**
     * Констуктор.
     * Задает данные во внутренний массив.
     *
     * @param array $aData
     *
     * @throws Exception
     */
    public function __construct($aData)
    {
        if (!is_array($aData)) {
            throw new Exception('Init data must be an array');
        }
        // набор обязательных полей
        $aExpected = [
            self::NAME,
            self::TITLE,
            self::VERSION,
            self::REVISION,
            self::LAYER,
        ];

        // проверить наличие обязательных полей
        foreach ($aExpected as $sKey) {
            if (!isset($aData[$sKey])) {
                throw new Exception("No key [{$sKey}] in config init array");
            }
        }

        $this->aData = $aData;
    }

    /**
     * Отдает значение по имени.
     *
     * @param $sName
     *
     * @return mixed
     */
    public function getVal($sName)
    {
        if (!isset($this->aData[$sName])) {
            return;
        }

        return $this->aData[$sName];
    }

    /**
     * Отдает имя модуля.
     *
     * @return string
     */
    public function getName()
    {
        return (string) $this->getVal(self::NAME);
    }

    /**
     * Отдает имя модуля с namespace.
     *
     * @return string
     */
    public function getNameWithNamespace()
    {
        return 'skewer\\build\\' . $this->getLayer() . '\\' . $this->getName() . '\\Module';
    }

    /**
     * Отдает название модуля
     * Название модуля собирается из имени слоя, имени модуля, tab_name: News.Page.tab_name
     * Если указанного языкового сообщения нет, то ищется по имени модуля: News.tab_name
     * Если нет, то tab_name.
     *
     * @return string
     */
    public function getTitle()
    {
        $sCustomTitle = $this->getCustomTitle($this->getName(), $this->getLayer());

        if ($sCustomTitle) {
            return $sCustomTitle;
        }

        $sName = $this->getName() . '.' . $this->getLayer() . '.tab_name';

        $sTitle = \Yii::t($this->getLanguageCategory(), $sName);

        if (mb_strpos($sTitle, 'tab_name') === false) {
            return $sTitle;
        }

        $sName = $this->getName() . '.tab_name';

        $sTitle = \Yii::t($this->getLanguageCategory(), $sName);

        if (mb_strpos($sTitle, 'tab_name') === false) {
            return $sTitle;
        }

        $sTitle = \Yii::t($this->getLanguageCategory(), 'tab_name');

        if ($sTitle !== 'tab_name') {
            return $sTitle;
        }

        return (string) $this->getVal(self::TITLE);
    }

    /**
     * Пытается спросить у модуля его название.
     *
     * @param $sName
     * @param $sLayer
     *
     * @return bool/string
     */
    private function getCustomTitle($sName, $sLayer)
    {
        $sClass = '\skewer\build\\' . $sLayer . '\\' . $sName . '\\Module';

        if (!class_exists($sClass) || !method_exists($sClass, 'getTitleTree')) {
            return false;
        }
        /* @noinspection PhpUndefinedMethodInspection */
        return $sClass::getTitleTree();
    }

    /**
     * Отдает номер версии.
     *
     * @return string
     */
    public function getVersion()
    {
        return (string) $this->getVal(self::VERSION);
    }

    /**
     * Отдает описание.
     *
     * @return string
     */
    public function getDescription()
    {
        return (string) $this->getVal(self::DESCRIPTION);
    }

    /**
     * Отдает номер ревизии.
     *
     * @return string
     */
    public function getRevision()
    {
        return (string) $this->getVal(self::REVISION);
    }

    /**
     * Отдает имя слоя.
     *
     * @return string
     */
    public function getLayer()
    {
        return (string) $this->getVal(self::LAYER);
    }

    /**
     * Возвращает список зависимостей для модуля.
     *
     * @return array
     */
    public function getDependency()
    {
        $dependency = $this->getVal(self::DEPENDENCY);

        return  $dependency === null ? [] : $dependency;
    }

    /**
     * Отдает набор данных в виде массива.
     *
     * @return array
     */
    public function getData()
    {
        return $this->aData;
    }

    /**
     * Удаляет набор полей которые не должны быть записаны в реестр
     */
    public function clearUnwantedData()
    {
        $aUnsetParam = [
            self::DESCRIPTION,
        ];

        foreach ($aUnsetParam as $sKey) {
            if (isset($this->aData[$sKey])) {
                unset($this->aData[$sKey]);
            }
        }
    }

    /**
     * Отдает набор данных в виде массива.
     *
     * @return string
     */
    public function getLanguageCategory()
    {
        return (isset($this->aData[self::LANGUAGE_CATEGORY])) ? $this->aData[self::LANGUAGE_CATEGORY] : lcfirst($this->getName());
    }
}

<?php

namespace skewer\components\config;

use skewer\base\section\Parameters;
use skewer\base\site\Layer;
use skewer\build\Design\Zones\Api;
use yii\helpers\ArrayHelper;
use yii\helpers\StringHelper;

/**
 * Класс-прототип файлов установки, обновления и удаления модулей.
 */
abstract class InstallPrototype extends UpdateHelper
{
    /** @var ModuleConfig Конфиг модуля */
    protected $config;

    /** @var array Языки */
    protected $words = [];

    public function __construct(ModuleConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Возвращает параметр конфига модуля.
     *
     * @param $sParamName
     *
     * @return string
     */
    public function getConfigParam($sParamName)
    {
        return $this->config->getVal($sParamName);
    }

    /**
     * Имя устанавлеваимого модуля.
     *
     * @return string
     */
    public function getModuleName()
    {
        return $this->config->getName();
    }

    /**
     * Имя слоя, куда устанавливается модуль.
     *
     * @return string
     */
    public function getLayer()
    {
        return $this->config->getLayer();
    }

    /**
     * Прототип функции инициализации перед выполнением операций над модулем.
     *
     * @return bool
     */
    abstract public function init();

    /**
     * Прототип функции для инструкций установки модуля.
     *
     * @return bool
     */
    abstract public function install();

    /**
     * Прототип функции для инструкций удаления модуля.
     *
     * @return bool
     */
    abstract public function uninstall();

    /**
     * Получить перевод языковой метки текущего ПОКА НЕ установленного модуля прямо из языкового файла.
     *
     * @param string $sLangKey Языковая метка модуля
     * @param array $aParams Параметры
     * @param string $sLangName Псевдоним нужного языка. Если не указан, то берётся текущий
     *
     * @return string
     */
    public function lang($sLangKey, $aParams = [], $sLangName = '')
    {
        // Закэшировать языковые значения модуля
        if (!$this->words) {
            /** Путь к директории текущего объекта */
            $sChildModuleDir = dirname(ROOTPATH . str_replace('\\', \DIRECTORY_SEPARATOR, get_class($this))) . \DIRECTORY_SEPARATOR;

            if (file_exists($sChildModuleDir . 'Language.php')) {
                $this->words = require $sChildModuleDir . 'Language.php';
            }

            // Чтобы при следующем вызове не выполнялся этот блок условия
            if (!$this->words) {
                $this->words[0] = 1;
            }
        }

        $sLangName = $sLangName ?: \Yii::$app->language;

        if (isset($this->words[$sLangName][$sLangKey])) {
            $sTranslate = $this->words[$sLangName][$sLangKey];

            if ($aParams) {
                $sTranslate = sprintf(str_replace(['{0}', '{1}', '{2}'], '%s', $sTranslate), $aParams);
            }

            return $sTranslate;
        }

        // Иначе попробовать найти немецкое значение
        $sLangCategory = $this->getConfigParam('languageCategory') ?: $this->getModuleName();

        return \Yii::t($sLangCategory, $sLangKey, $aParams);
    }

    /**
     * Убрать объект из страниц сайта.
     *
     * @param string $sLabelName Название группы, в которой выполняется объект
     * @param array $aLayouts Области страницы, откуда удалять объект
     *
     * @see Parameters::getListByLayoutLabels()
     */
    public function removeObjectFromLayouts($sLabelName, $aLayouts = ['content', 'head', 'left', 'right'])
    {
        foreach (Parameters::getListByLayoutLabels($sLabelName, $aLayouts) as $oParam) {
            $oParam->value = trim(str_ireplace(",{$sLabelName},", ',', ',' . $oParam->value . ','), ',');
            $oParam->save(false);
        }
    }

    /**
     * Провести апгрейд модуля с версии до версии.
     *
     * @param $iPreviosVersion
     * @param $iNextVersion
     * @param mixed $bOnlyLabels
     */
//    abstract public function upgrade($iPreviosVersion, $iNextVersion);

    /**
     * Удалить параметры модуля.
     *
     * @param bool $bOnlyLabels - true  - удаляет только метки из зон,
     *                            false - удаляет метки + группы параметров с объектом модуля
     *
     * @throws \Exception
     */
    public function deleteModuleParams($bOnlyLabels = false)
    {
        if ($this->getLayer() != Layer::PAGE) {
            throw new \Exception(sprintf('Операция неприменима к модулю слоя %s', $this->getLayer()));
        }
        $aListParams = \skewer\base\section\Parameters::getList()
            ->name(Parameters::object)
            ->value($this->getModuleName())
            ->asArray()->get();

        $aLayouts = Api::getAllZones(null, false, false);

        $aNameGroups = ArrayHelper::getColumn($aListParams, 'group');

        foreach ($aNameGroups as $sNameGroup) {
            foreach ($aLayouts as $oLayout) {
                /** @var \skewer\base\section\models\ParamsAr $oLayout */
                $aValue = StringHelper::explode($oLayout->show_val);

                if (($iPos = array_search($sNameGroup, $aValue)) !== false) {
                    unset($aValue[$iPos]);
                }

                $oLayout->show_val = implode(',', $aValue);
                $oLayout->save();
            }
        }

        if (!$bOnlyLabels) {
            Parameters::removeByGroup($aNameGroups);
        }
    }

    /**
     * Возвращает класс и функцию которую необходимо установить после установки модуля и всех его зависимостей.
     *
     * @return array
     */
    public function getCommandsAfterInstall()
    {
        return [];
    }
}// class

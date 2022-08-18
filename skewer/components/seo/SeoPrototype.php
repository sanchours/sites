<?php

namespace skewer\components\seo;

use skewer\base\section\Parameters;
use skewer\base\section\Tree;
use skewer\build\Adm\Articles;
use skewer\build\Adm\CategoryViewer;
use skewer\build\Adm\FAQ;
use skewer\build\Adm\Gallery;
use skewer\build\Adm\News;
use skewer\build\Catalog\Collections;
use skewer\build\Catalog\Goods;
use skewer\build\Page\Main;
use skewer\components\search\Prototype;
use yii\helpers\ArrayHelper;
use yii\web\ServerErrorHttpException;

abstract class SeoPrototype implements SeoInterface
{
    public $title = '';

    public $description = '';

    public $keywords = '';

    public $seo_gallery = 0;

    public $none_index = 0;

    public $none_search = 0;

    public $add_meta = '';

    public $frequency = '';

    public $priority = '';

    /** @var int Id родитеской сущности.
     * Для всех наследников SeoPrototype, кроме 'Элемент коллекции'  - это id раздела
     * Для 'Элемент коллекции' - это id карточки коллекции
     * */
    public $iSectionId = 0;

    public $iEntityId = 0;

    /**
     * Общий массив меток замены( =merge(метки контекста, метки сущности) ).
     *
     * @var array
     */
    protected $aReplaceLabels = [];

    /**
     * Метки-замены, вычисленные из данных сущности.
     *
     * @var array
     */
    protected $aLabelsFromEntity = [];

    /**
     * Метки-замены, вычисленные из контекста вызова компонента
     * Как правило это метки, вычисляемые исходя из раздела.
     *
     * @var array
     */
    protected $aLabelsFromContext = [];

    /**
     * Seo-шаблон.
     *
     * @var  TemplateRow
     */
    protected $oTemplate;

    /**
     * Динамическая часть псевдонима шаблона.
     *
     * @var string
     */
    public $sExtraAlias = '';

    public $aDataEntity = [];

    public function __construct($iEntityId = 0, $iSectionId = 0, $aDataEntity = [])
    {
        $this->aDataEntity = $aDataEntity;
        $this->iSectionId = $iSectionId;
        $this->iEntityId = $iEntityId;
    }

    /**
     * Установить дин.часть псевдонима.
     *
     * @param string $sExtraAlias
     */
    public function setExtraAlias($sExtraAlias)
    {
        $this->sExtraAlias = $sExtraAlias;
    }

    /**
     * Получить дин.часть псевдонима.
     *
     * @return string
     */
    public function getExtraAlias()
    {
        return $this->sExtraAlias;
    }

    /**
     * Вычислить метки для замены.
     *
     * @param $aParams
     */
    private function computeReplaceLabels($aParams)
    {
        if (!$this->aLabelsFromContext) {
            $iSectionId = ArrayHelper::getValue($aParams, 'sectionId', false);
            $this->aLabelsFromContext = Api::getCommonSeoLabels($iSectionId);
        }

        if (!$this->aLabelsFromEntity && $this->aDataEntity) {
            $this->aLabelsFromEntity = $this->extractReplaceLabels($aParams);
        }

        $this->aReplaceLabels = ArrayHelper::merge($this->aLabelsFromContext, $this->aLabelsFromEntity);
    }

    public function isExistSeoData()
    {
        foreach (Api::getDataFields() as $sFieldName) {
            if (!empty($this->{$sFieldName})) {
                return true;
            }
        }

        return false;
    }

    public function getDataEntity()
    {
        return $this->aDataEntity;
    }

    public function toLower($str)
    {
        $str = trim($str);
        $str = explode(' ', $str);
        $str[0] = mb_convert_case($str[0], MB_CASE_LOWER);
        $str = implode(' ', $str);

        return $str;
    }

    /**
     * Очистить массив меток сущности
     * Метод используется при парсинге списка объектов для того чтобы данные предыдущего объекта не попали в следующий.
     */
    public function clearLabelsFromEntity()
    {
        $this->aLabelsFromEntity = [];
    }

    /**
     * Очистить массив меток сущности
     * Метод используется при парсинге списка объектов, имеющих разные контексты(Например: товары разных разделов).
     */
    public function clearLabelsFromContext()
    {
        $this->aLabelsFromContext = [];
    }

    /**
     * Заменяет содержащиеся в поле метки на значения из внутр.массива aReplaceLabels.
     *
     * @param $sFieldName - имя поля
     * @param array $aParams - метки для замены
     * @param bool $doParse - Парсить поле? Если =false, то метод вернет нераспарсенное значение поля seo-шаблона
     *
     * @throws \Exception
     *
     * @return string
     */
    public function parseField($sFieldName, $aParams = [], $doParse = true)
    {
        if ($doParse) {
            $this->computeReplaceLabels($aParams);
        }

        $this->selectTemplate();

        return ($doParse) ? $this->oTemplate->parseTpl($sFieldName, $this->aReplaceLabels) : $this->oTemplate->{$sFieldName};
    }

    /**
     * Выбрать шаблон c метками.
     */
    public function selectTemplate()
    {
        // Шаблон был выбран ранее
        if ($this->oTemplate) {
            return;
        }

        $oTpl = $this->getIndividualTemplate4Section();

        if (!$oTpl) {
            $oTpl = Template::getByAliases(static::getAlias(), $this->sExtraAlias);
        }

        if (!$oTpl) {
            $oTpl = Template::getByAliases(static::getAlias(), '');
        }

        if (!$oTpl) {
            throw new \Exception('Не найден шаблон');
        }
        $this->oTemplate = $oTpl;
    }

    /**
     * Вернёт массив полей, которые можно парсить.
     *
     * @return array
     */
    public static function getField4Parsing()
    {
        return ['title', 'description', 'keywords'];
    }

    public static function getFieldList()
    {
        return [
            'title' => 'SEOTitle',
            'description' => 'SEODescription',
            'keywords' => 'SEOKeywords',
            'none_index' => 'SEONonIndex',
            'none_search' => 'SEONonSearch',
            'add_meta' => 'SEOAddMeta',
            'priority' => 'SEOPriority',
            'frequency' => 'SEOFrequency',
            'seo_gallery' => 'SEOSeoGallery',
        ];
    }

    public static function className()
    {
        return get_called_class();
    }

    /**
     * Расчет значения приоритета(для sitemap).
     *
     * @return float
     */
    protected function getPriority()
    {
        return (float) $this->getPriorityFromTemplate();
    }

    /**
     * Метод расчитывает значение приоритета.
     *
     * @return float
     */
    public function calculatePriority()
    {
        $fPriority = $this->getPriority();

        if ($fPriority < 0) {
            $fPriority = 0;
        }

        return $fPriority;
    }

    /**
     * Расчет значения частоты(для sitemap).
     *
     * @return mixed
     */
    public function calculateFrequency()
    {
        return $this->getFrequencyFromTemplate();
    }

    /**
     * Получить базовое значение приоритета, привязанное к шаблону.
     *
     * @return float
     */
    private function getPriorityFromTemplate()
    {
        $aTemplates = Tree::getSubSections(\Yii::$app->sections->templates(), true, true);

        $iTpl = (in_array($this->iSectionId, $aTemplates))
            ? $this->iSectionId
            : Parameters::getTpl($this->iSectionId);

        $aSeoData = Api::get(Main\Seo::getGroup(), $iTpl, $iTpl, true);

        return (float) ArrayHelper::getValue($aSeoData, 'priority', 0);
    }

    /**
     * Получить базовое значение частоты, привязанное к шаблону.
     *
     * @return mixed
     */
    private function getFrequencyFromTemplate()
    {
        $aTemplates = Tree::getSubSections(\Yii::$app->sections->templates(), true, true);

        $iTpl = (in_array($this->iSectionId, $aTemplates))
            ? $this->iSectionId
            : Parameters::getTpl($this->iSectionId);

        $aSeoData = Api::get(Main\Seo::getGroup(), $iTpl, $iTpl, true);

        return ArrayHelper::getValue($aSeoData, 'frequency', '');
    }

    /**
     * Метод инициализирует компонент
     * данными из таблицы seo_data.
     */
    public function initSeoData()
    {
        $iEntityId = $this->iEntityId ? $this->iEntityId : (int) ArrayHelper::getValue($this->aDataEntity, 'id', 0);

        if ($aSeoData = Api::get(static::getGroup(), $iEntityId, $this->iSectionId, true)) {
            \Yii::configure($this, $aSeoData);
        }
    }

    /**
     * Установить данные сущности.
     *
     * @param $aData
     */
    public function setDataEntity($aData)
    {
        $this->aDataEntity = $aData;
    }

    /**
     * Метод запрашивает данные соответсвующей сущности
     * и сохраняет их во внутреннею переменную.
     *
     * @return mixed
     */
    abstract public function loadDataEntity();

    /**
     * Метод собирает с сущности метки для замены в seo шаблонах.
     *
     * @param array $aParams - параметры для подстановки
     *
     * @return mixed
     */
    abstract public function extractReplaceLabels($aParams);

    /**
     * Отдает, соответствующий данному классу, объект класса Search.
     *
     * @throws ServerErrorHttpException
     *
     * @return Prototype
     */
    public function getSearchObject()
    {
        $sSearchClassName = $this->getSearchClassName();

        if (!class_exists($sSearchClassName)) {
            throw new ServerErrorHttpException(sprintf('Class [%s] not found', $sSearchClassName));
        }
        /** @var Prototype $oSearch */
        $oSearch = new $sSearchClassName();

        if (!($oSearch instanceof Prototype)) {
            throw new ServerErrorHttpException(sprintf('Class [%s] is not an instance of [%s]', $sSearchClassName, Prototype::className()));
        }

        return $oSearch;
    }

    /**
     * Возвращает имя поискового класса, соответствующее данному seo компоненту.
     *
     * @return string
     */
    abstract protected function getSearchClassName();

    /**
     * Установить id сущности.
     *
     * @param $iEntityId
     */
    public function setEntityId($iEntityId)
    {
        $this->iEntityId = $iEntityId;
    }

    /**
     * Установить id раздела, в котором используется сущность.
     *
     * @param $iSectionId
     */
    public function setSectionId($iSectionId)
    {
        $this->iSectionId = $iSectionId;
    }

    /**
     * Получить id раздела.
     *
     * @return int
     */
    public function getSectionId()
    {
        return $this->iSectionId;
    }

    /**
     * Метод вернет массив seo данных.
     *
     * @param array $aParams - метки для замены
     *
     * @return array|bool
     */
    public function parseSeoData($aParams = [])
    {
        if (!$this->aDataEntity) {
            return false;
        }

        if (!$this->isExistSeoData()) {
            $this->initSeoData();
        }

        $aRow = [];

        foreach ($this as $field => $value) {
            if (!in_array($field, array_keys(self::getFieldList()))) {
                continue;
            }

            if (in_array($field, SeoPrototype::getField4Parsing())) {
                $aRow[$field] = [
                    'value' => $this->{$field} ? $this->{$field} : $this->parseField($field, $aParams),
                    'overriden' => ($this->{$field}) ? true : false,
                ];
            } elseif ($field == 'priority') {
                $fCalculatedPriority = $this->calculatePriority();
                $fPriority = ($this->{$field}) ? $this->{$field} : $fCalculatedPriority;

                $aRow[$field] = [
                    'value' => $fPriority,
                    'overriden' => ($fPriority != $fCalculatedPriority) ? true : false,
                ];
            } elseif ($field == 'frequency') {
                $fCalculatedFrequency = $this->calculateFrequency();
                $sFrequency = ($this->{$field}) ? $this->{$field} : $fCalculatedFrequency;

                $aRow[$field] = [
                    'value' => $sFrequency,
                    'overriden' => ($sFrequency != $fCalculatedFrequency) ? true : false,
                ];
            } else {
                $aRow[$field] = $value;
            }
        }

        return $aRow;
    }

    /**
     * Проверяет по alias существует ли запись сущности
     * Вернёт id записи или false если запись не найдена.
     *
     * @param $sPath -   url-путь записи
     *
     * @return bool|int
     */
    public function doExistRecord(/* @noinspection PhpUnusedParameterInspection */ $sPath)
    {
        return false;
    }

    /**
     * Фабричный метод. Вернёт объект seo класса по псевдониму шаблона.
     *
     * @param $sAlias - тип seo - шаблона
     *
     * @return null|SeoPrototype
     */
    public static function getInstanceByAlias($sAlias)
    {
        switch ($sAlias) {
            case Main\Seo::getAlias():
                $oInstance = new Main\Seo();
                break;

            case News\Seo::getAlias():
                $oInstance = new News\Seo();
                break;

            case Articles\Seo::getAlias():
                $oInstance = new Articles\Seo();
                break;

            case FAQ\Seo::getAlias():
                $oInstance = new FAQ\Seo();
                break;

            case Gallery\Seo::getAlias():
                $oInstance = new Gallery\Seo();
                break;

            case Goods\SeoGood::getAlias():
                $oInstance = new Goods\SeoGood();
                break;

            case Goods\SeoGoodModifications::getAlias():
                $oInstance = new Goods\SeoGoodModifications();
                break;

            case Collections\SeoCollectionList::getAlias():
                $oInstance = new Collections\SeoCollectionList();
                break;

            case Collections\SeoElementCollection::getAlias():
                $oInstance = new Collections\SeoElementCollection();
                break;

            case CategoryViewer\Seo::getAlias():
                $oInstance = new CategoryViewer\Seo();
                break;

            default:
                $oInstance = null;
        }

        return $oInstance;
    }

    /**
     * Поля, парсинг которых поддерживает seo шаблон.
     *
     * @return array
     */
    public function editableSeoTemplateFields()
    {
        return [
            'title',
            'description',
            'keywords',
            'nameImage',
            'altTitle',
        ];
    }

    /**
     * Получить индивидуальный шаблон для раздела.
     *
     * @return null|TemplateRow вернет null если класс не использует индивидульные шаблоны для раздела
     */
    public function getIndividualTemplate4Section()
    {
    }
}

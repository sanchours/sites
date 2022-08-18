<?php

namespace skewer\components\catalog\field;

use skewer\base\ft;
use skewer\base\ft\Editor;
use skewer\base\site_module\Parser;
use skewer\components\catalog\Card;
use skewer\components\seo\SeoPrototype;
use yii\helpers\ArrayHelper;

/**
 * Прототип для типизированного объекта парсинга поля сущности
 * Class Prototype.
 */
abstract class Prototype
{
    protected $type = '';

    protected $name = '';

    protected $title = '';

    protected $card = '';

    protected $attr = [];

    protected $widget = '';

    protected $subEntity = '';

    protected $subData = [];

    /** @var ft\model\Field */
    protected $ftField;

    /**
     * @var bool выключено редактирование
     */
    public $disableEdit = false;
    /**
     * @var bool Поле является ссылочным на сущность
     */
    public $isLinked = false;

    /**
     * @var bool Отображение спец. редакторов
     */
    public $isSpecialEdit = false;

    /**
     * Получить тех.имя поля.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Получить редактор поля.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /** Получить виджет поля */
    public function getWidget()
    {
        return $this->widget;
    }

    /**
     * Получить заголовок поля.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Получить ft-описание поля.
     *
     * @return ft\model\Field
     */
    public function getFtField()
    {
        return $this->ftField;
    }

    /**
     * Создание поля для парсера на основе ft-поля модели.
     *
     * @param ft\model\Field $field
     *
     * @return Prototype
     */
    public static function init($field)
    {
        $sEditorName = $field->getEditorName();

        // для php 7.0
        if (in_array($sEditorName, ['string', 'int', 'float'])) {
            $sEditorName .= 'Field';
        }

        $class = __NAMESPACE__ . '\\' . ucfirst($sEditorName);

        $obj = class_exists($class) ? new $class() : new StringField();

        $obj->ftField = $field;
        $obj->type = $field->getEditorName();
        $obj->name = $field->getName();
        $obj->title = $field->getTitle();
        $obj->card = $field->getModel()->getName();
        $obj->attr = $field->getAttrs();
        $obj->widget = $field->getWidgetName();

        return $obj;
    }

    public function loadData()
    {
        $this->load();
        $this->loadSubData();
    }

    protected function load()
    {
    }

    protected function loadSubData()
    {
        $oModel = ft\Cache::get($this->card);

        if (!$oModel) {
            return;
        }

        if ($oRel = $oModel->getOneFieldRelation($this->name)) {
            $this->subEntity = $oRel->getEntityName();

            $query = ft\Cache::getMagicTable($this->subEntity)->find()->asArray();

            // Сортировка значений справочника мультисписка
            if ($this->type == Editor::MULTISELECT) {
                $query->order(Card::FIELD_SORT);
            }

            while ($row = $query->each()) {
                $this->subData[$row['id']] = $row;
            }
        }
    }

    protected function getSubDataValue($key)
    {
        return ArrayHelper::getValue($this->subData, $key, false);
    }

    /**
     * Классовая функция преобразования значения.
     *
     * @param string $value значение поля из записи
     * @param int $rowId идентификатор записи
     * @param array $aParams Дополнительные параметры
     *
     * @return mixed
     */
    abstract protected function build($value, $rowId, $aParams);

    /**
     * Функция преобразования значения.
     *
     * @param string $value значение поля из записи
     * @param int $rowId идентификатор записи
     * @param array $aParams Дополнительные параметры
     *
     * @return mixed
     */
    public function parse($value, $rowId, $aParams = [])
    {
        $sMeasure = (isset($this->attr['measure']) && $this->attr['measure']) ? $this->attr['measure'] : '';
        $out = $this->build($value, $rowId, $aParams);

        $out['attrs'] = $this->attr;
        $out['name'] = $this->name;
        $out['card'] = $this->card;
        $out['title'] = $this->title;
        $out['type'] = $this->type;
        $out['widget'] = $this->widget;

        // ед измерения
        if ($out['html'] && $sMeasure) {
            $out['measure'] = $sMeasure;
        }

        return $out;
    }

    /**
     * Действие, выполняемое после парсинга товара.
     *
     * @param array $aGoodData  - данные товара
     * @param array $aFieldData - данные поля
     */
    public function afterParseGood($aGoodData, $aFieldData)
    {
    }

    /**
     * Получение html кода шаблона.
     *
     * @param $value
     * @param string $sTmp
     * @param array $aData
     *
     * @return string
     */
    public function getHtmlData($value, $sTmp = 'string.twig', $aData = [])
    {
        $sPath = __DIR__ . "/templates/{$this->type}/";

        $mTmpDir = is_dir($sPath) ? $sPath : __DIR__ . '/templates/string/';
        $aData = ['title' => $this->title, 'name' => $this->name, 'value' => $value, 'attrs' => $this->attr] + $aData;
        $sHtml = Parser::parseTwig($sTmp, $aData, $mTmpDir);

        return $sHtml;
    }

    /**
     * Устанавливает seo-данные в уже распарсенную структуру товара.
     *
     * @param SeoPrototype $oSeo - seo компонент
     * @param array $aField - распарсенные данные поля товара
     * @param int $iSectionId - раздел для которого парсится товар
     */
    public function setSeo(/* @noinspection PhpUnusedParameterInspection */ $oSeo,
        &$aField,
                            /* @noinspection PhpUnusedParameterInspection */
        $iSectionId
    ) {
    }

    /**
     * @param mixed $link_id
     *
     * @return array
     */
    public static function getGroupWidgetList($link_id = '')
    {
        return [];
    }

    /**
     * Список сущностей для связи с полем
     *
     * @param string $link_id
     *
     * @return array
     */
    public static function getEntityList($link_id = '')
    {
        return [];
    }

    /**
     * Может ли поле быть NULL.
     *
     * @return bool
     */
    public static function canBeNull()
    {
        return false;
    }

    /**
     * returns namespace.
     *
     * @return string
     */
    public static function getNamespace()
    {
        return '\\' . __NAMESPACE__ . '\\';
    }

    /**
     * Конвертировать ли конкретное значение в null для конкретного типа поля.
     *
     * @param $sValue
     *
     * @return bool
     */
    public static function convertValueToNull($sValue)
    {
        return false;
    }

    /**
     * Вернёт строку содержащую опции библиотеки jquery.inputMask для данного поля.
     *
     * @see https://github.com/RobinHerbots/Inputmask/wiki
     *
     * @return string | bool
     */
    public function getInputMaskOptions()
    {
        return false;
    }
}

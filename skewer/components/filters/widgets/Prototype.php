<?php

namespace skewer\components\filters\widgets;

use skewer\base\orm\state\StateSelect;
use skewer\components\catalog;
use skewer\components\filters;
use skewer\helpers\Transliterate;
use yii\web\ServerErrorHttpException;

/**
 * Class Prototype - прототип виджетов фильтра.
 * Каждый класс соответствует отдельному виджету(шаблону поля фильтра).
 */
abstract class Prototype implements WidgetInterface
{
    /** @var catalog\field\Prototype - каталожное поле. */
    protected $oField;

    /** @var filters\FilterPrototype - объект фильтра */
    protected $oFilter;

    public function __construct(catalog\field\Prototype $oField, filters\FilterPrototype $oFilter = null)
    {
        $this->oField = $oField;

        if ($oFilter) {
            $this->oFilter = $oFilter;
        }
    }

    /**
     * Парсинг виджета.
     *
     * @param array $aFilterFieldData - данные-условия для данного поля
     *
     * @return array
     */
    abstract public function parse($aFilterFieldData);

    /**
     * Добавляет условие фильтра в запрос $oQuery.
     *
     * @param StateSelect $oQuery - запрос
     * @param array $aFilterData - данные фильтра
     *
     * @return bool - true если условие было добавлено, false в противном случае
     */
    public function addFilterConditionToQuery(StateSelect $oQuery, $aFilterData = [])
    {
        if ($this->oField instanceof filters\FilteredInterface) {
            return $this->oField->addFilterConditionToQuery($oQuery, $aFilterData);
        }

        return false;
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
     * Вернет экземпляр виджета фильтра.
     *
     * @param catalog\field\Prototype $oField - поле каталога
     * @param filters\FilterPrototype $oFilter - объект фильтра
     *
     * @throws ServerErrorHttpException
     *
     * @return bool| Prototype - объект виджета или false, если поле не может учавствовать в фильтре
     */
    public static function getInstanceWidget(catalog\field\Prototype $oField, filters\FilterPrototype $oFilter = null)
    {
        $sWidget = filters\Api::getWidgetByCatalogField($oField);

        if (!$sWidget) {
            return false;
        }

        $sClassName = __NAMESPACE__ . '\\' . Transliterate::toCamelCase($sWidget);

        if (!class_exists($sClassName)) {
            throw new ServerErrorHttpException(sprintf('Класс [%s] не существует', $sClassName));
        }
        $oWidget = new $sClassName($oField, $oFilter);

        if (!$oWidget instanceof self) {
            throw new ServerErrorHttpException(sprintf(
                'Класс [%s] должен быть унаследован от [%s]',
                get_class($oWidget),
                __NAMESPACE__ . '\\Prototype'
            ));
        }

        return $oWidget;
    }

    /**
     * Вернёт строку содержащую опции библиотеки jquery.inputMask для данного поля.
     *
     * @return string | bool
     */
    public function getInputMaskOptions()
    {
        return $this->oField->getInputMaskOptions();
    }

    /**
     * Прокси-метод получения тех.имени поля фильтра.
     *
     * @return string
     */
    public function getFieldName()
    {
        return $this->oField->getName();
    }

    /**
     * Прокси-метод получения названия поля фильтра.
     *
     * @return string
     */
    public function getFieldTitle()
    {
        return $this->oField->getTitle();
    }

    /**
     * Конвертировать значение поля в alias.
     *
     * @param array $aDataItem - значение поля
     *
     * @return array|bool - вернет список алиасов или false если значение поля не может иметь алиаса
     */
    public function convertValueToAlias(/* @noinspection PhpUnusedParameterInspection */ $aDataItem)
    {
        return false;
    }

    /**
     * Конвертировать значение поля в id.
     *
     * @param  array $aDataItem - значение поля
     *
     * @return array|bool - вернет список ид или false если значение поля не может иметь id
     */
    public function convertValueToId(/* @noinspection PhpUnusedParameterInspection */ $aDataItem)
    {
        return false;
    }

    /**
     * Конвертировать значение поля в title.
     *
     * @param array $aDataItem - значение поля
     *
     * @return array|bool - вернет список title или false если значение поля не может иметь title
     */
    public function convertValueToTitle(/* @noinspection PhpUnusedParameterInspection */ $aDataItem)
    {
        return false;
    }

    /**
     * Может ли значение поля фильтра иметь заголовок
     * Может ли данное поле использоваться для построения seo meta-тегов, h1 и хлебных крошек.
     *
     * @return bool
     */
    public function canHaveTitle()
    {
        return true;
    }

    /**
     * Приводит к единому виду (массиву) набор параметров.
     *
     * @param array|string $mDataItem
     *
     * @return array
     */
    public function canonizeValue($mDataItem)
    {
        if (is_array($mDataItem)) {
            return $mDataItem;
        }

        return [$mDataItem];
    }

    /**
     * Фильтрует входящие в фильтр данные.
     *
     * @param array $aDataItem - данные поля фильтра
     *
     * @return bool Если =true, то данные этого поля применятся как условия фильтра
     *              Если =false, то это поле не будет учавствовать в фильтрации
     */
    public function filterInputVal(/* @noinspection PhpUnusedParameterInspection */ $aDataItem)
    {
        return true;
    }
}

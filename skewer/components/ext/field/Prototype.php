<?php

namespace skewer\components\ext\field;

use skewer\base\ft;

/**
 * Родительский класс для полей автопостроителя.
 */
abstract class Prototype
{
    /** @var array описание поля в ExtJS нотации */
    protected $aDesc = [];

    /** @var ft\Model описание модели */
    protected $oModel;

    /** @var ft\model\Field описание поля */
    protected $oField;

    /**
     * Инициализация поля.
     *
     * @param string $sValue Базовое значение поля
     * @param string $sShowVal Дополнительное значение поля
     */
    public function init($sValue, $sShowVal)
    {
    }

    /**
     * Задает объектное описание модели для поля.
     *
     * @param ft\model\Field $oField
     * @param ft\Model $oModel
     */
    public function setDescObj(ft\model\Field $oField, ft\Model $oModel)
    {
        $this->oField = $oField;
        $this->oModel = $oModel;
    }

    /**
     * Задает базовое описание для поля даже при инициализированном дополнительном описании
     * Первичны - значения дополнительного описания.
     *
     * @param array $aBaseDesc
     */
    public function setBaseDesc(array $aBaseDesc)
    {
        $this->aDesc = array_merge($aBaseDesc, $this->aDesc);
    }

    /**
     * Задает дополнительное описание для поля даже при инициализированном базовом описании
     * Первичны - значения дополнительного описания.
     *
     * @param array $aAddDesc
     */
    public function setAddDesc(array $aAddDesc)
    {
        $this->aDesc = array_merge($this->aDesc, $aAddDesc);
    }

    /**
     * Задает дополнительное описание для списковой части отображения
     * Первичны - значения дополнительного описания
     * - рекурсивное слияние массивов не подошло.
     *
     * @param array $aAddDesc
     */
    public function setAddListDesc(array $aAddDesc)
    {
        // запросить старое
        $aListColumns = $this->getDescVal('listColumns', []);

        // перекрыть
        $aListColumns = array_merge($aListColumns, $aAddDesc);

        // записать назад
        $this->setDescVal('listColumns', $aListColumns);
    }

    /**
     * Отдает массив с описанием
     *
     * @return array
     */
    public function getDesc()
    {
        return $this->aDesc;
    }

    /**
     * Заменяет массив с описанием
     *
     * @param $aDesc
     */
    public function setDesc($aDesc)
    {
        $this->aDesc = $aDesc;
    }

    /*
     * Работа со значением поля
     */

    /**
     * Устанавливает значение.
     *
     * @param mixed $mValue
     *
     * @return mixed
     */
    public function setValue($mValue)
    {
        return $this->setDescVal('value', $mValue);
    }

    /**
     * Возвращает значение.
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->getDescVal('value');
    }

    /**
     * Возвращает значение для отображеня в списке.
     *
     * @return mixed
     */
    public function getValueList()
    {
        return $this->getValue();
    }

    /**
     * Проверяет наличие параметра значения.
     *
     * @return mixed
     */
    public function hasValue()
    {
        return $this->hasDescVal('value');
    }

    /**
     * Отдает значение по умолчанию.
     *
     * @return string
     */
    public function getDefaultVal()
    {
        // запрос параметра
        $mVal = $this->getDescVal('default');

        // типовое значение по умолчанию
        if (!$mVal and $this->getType() === 'i') {
            $mVal = 0;
        }

        // отдать
        return $mVal;
    }

    /**
     * Возвращает значение для сохранения.
     *
     * @return mixed
     */
    public function getSaveValue()
    {
        return $this->getValue();
    }

    /**
     * Дополнительные параметры описания.
     *
     * @param mixed $sName
     * @param null|mixed $sDef
     */

    /**
     * Отлает значение парметра.
     *
     * @param string $sName имя парметра описания
     * @param mixed $sDef значение, если параметр не найден
     *
     * @return mixed
     */
    public function getDescVal($sName, $sDef = null)
    {
        return isset($this->aDesc[$sName]) ? $this->aDesc[$sName] : $sDef;
    }

    /**
     * Устанавливает занчение параметра описания.
     *
     * @param string $sName имя парметра описания
     * @param mixed $mVal значение
     *
     * @return mixed
     */
    public function setDescVal($sName, $mVal)
    {
        return $this->aDesc[$sName] = $mVal;
    }

    /**
     * Удаляет значние параметра описания
     * Возвращает true, если найдет и удалит элемент, false - если не найдет
     *
     * @param string $sName имя парметра описания
     *
     * @return bool
     */
    public function delDescVal($sName)
    {
        if ($this->hasDescVal($sName)) {
            unset($this->aDesc[$sName]);

            return true;
        }

        return false;
    }

    /**
     * Проверяет наличие элемента в описании.
     *
     * @param string $sName имя парметра описания
     *
     * @return bool
     */
    public function hasDescVal($sName)
    {
        return isset($this->aDesc[$sName]);
    }

    /**
     * Отдает имя параметра.
     *
     * @param string $sName
     */
    public function setName($sName)
    {
        $this->setDescVal('name', (string) $sName);
    }

    /**
     * Отдает имя параметра.
     *
     * @return string
     */
    public function getName()
    {
        return (string) $this->getDescVal('name');
    }

    /**
     * Отдает название поля.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->getDescVal('title');
    }

    /**
     * Сохраняет название поля.
     *
     * @param string $sTitle
     *
     * @return mixed
     */
    public function setTitle($sTitle)
    {
        return $this->setDescVal('title', $sTitle);
    }

    /**
     * Отдает букву типа данных ( i / s ).
     *
     * @return string
     */
    public function getType()
    {
        return (string) $this->getDescVal('type', 's');
    }

    /**
     * Устанавливает группу.
     *
     * @param string $sGroupTitle Заголовок группы
     * @param int $iGroupType Тип группы (0 - обычная, 1 - сворачиваемая, 2 - свёрнутая)
     *
     * @return mixed
     */
    public function setGroup($sGroupTitle, $iGroupType = 0)
    {
        return $this->setDescVal('groupTitle', $sGroupTitle) and
               $this->setDescVal('groupType', $iGroupType);
    }

    /**
     * Задет текст ошибки.
     *
     * @param string $sError
     *
     * @return mixed
     */
    public function setError($sError)
    {
        return $this->setDescVal('activeError', $sError);
    }

    /**
     * Проверяет доступность заданного значения.
     *
     * @return bool
     */
    public function isValid()
    {
        return true;
    }

    /**
     * Возвращает текcт ошибки валидации.
     *
     * @return string
     */
    public function getInvalidText()
    {
        return \Yii::t('adm', 'errorInvalidFied', $this->getName());
    }

    /**
     * Отдает набор параметров.
     *
     * @throws ft\exception\Inner
     *
     * @return array
     */
    protected function getParams()
    {
        // если нет описания поля
        if (!$this->oField) {
            throw new ft\exception\Inner('Не задан объект поля для редактора');
        }

        return $this->oField->getEditorParams();
    }

    /**
     * Отдает название типа отображения.
     *
     * @return string
     */
    abstract public function getView();
}

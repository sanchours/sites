<?php

namespace skewer\base\ft;

/**
 * Класс для хранения описания модели
 * Является провайдером данных для всех ft классов.
 */
class Model
{
    /** имя метки пространства имен */
    const NAMESPACE_LABEL = 'namespace';

    /** имя метки для родительского поля */
    const PARENT_FIELD = 'parent_field';

    /** имя метки для префикса таблицы */
    const TABLE_PREFIX = 'tablePrefix';

    /** имя метки для названия */
    const TITLE = 'title';

    const HIDE_DETAIL = 'hide_detail';

    const PRIORITY_SORT = 'priority_sort';

    /** @var string имя сущности */
    protected $sName;

    /** @var array контейнер заголовочных данных описания */
    protected $aEntity = [];

    /** @var model\Field[] контейнер полей */
    protected $aFields = [];

    /** @var array наборы колонок */
    protected $aColumns = [];

    /** @var model\Index[] набор индексов */
    protected $aIndexes = [];

    /** @var string адрес в ространстве имен */
    protected $sNamespace = '';

    /** @var string имя поля первичного ключа */
    protected $sPrimaryKey = 'id';

    /**
     * Конструктор
     *
     * @param array $aModel описание сущности
     *
     * @throws exception\Model
     */
    public function __construct($aModel)
    {
        // если формат данных заведомо левый - то выходим
        if (!is_array($aModel)) {
            throw new exception\Model('Неверный тип контейнера описания для модели');
        }
        if (!isset($aModel['entity']) or !isset($aModel['fields'])) {
            throw new exception\Model('Отсутствуют необходимые контейнеры');
        }
        // заголовочные данные описания
        $this->aEntity = $aModel['entity'];
        if (!is_array($this->aEntity)) {
            throw new exception\Model('Неверный контейнер описания');
        }
        // имя сущности
        $this->sName = isset($this->aEntity['name']) ? (string) $this->aEntity['name'] : '';
        $this->sName = str_replace("'", '', $this->sName);
        if (!$this->sName) {
            throw new exception\Model('Отсутствует имя сущности');
        }
        // добавление полей
        $aFields = $aModel['fields'];
        if (!is_array($aFields)) {
            throw new exception\Model('Неверный контейнер полей');
        }
        foreach ($aFields as $sFieldName => $aField) {
            $this->addFieldObject($this->createField($sFieldName, $aField));
        }

        // добавление индексов
        $aIndexes = $aModel['indexes'] ?? [];
        if (!is_array($aIndexes)) {
            throw new exception\Model('Неверный контейнер индексов');
        }
        foreach ($aIndexes as $sIndexName => $aIndex) {
            $oIndex = new model\Index($sIndexName, $aIndex);
            $this->aIndexes[$oIndex->getName()] = $oIndex;
        }

        $this->aColumns = $aModel['columns'] ?? [];
    }

//    /**
//     * Отдает описание сущности
//     * @static
//     * @param $sEntityName
//     * @param bool $bJustLoaded - флаг для рекурсивного вызова самой себя
//     * @return Model|null
//     */
//    static public function get( $sEntityName, $bJustLoaded=false ) {
//
//        $oDef = null;
//
//        // если уже есть в кэше
//        if ( Cache::exists( $sEntityName ) ) {
//            $oDef = Cache::get( $sEntityName );
    ////            self::egInited() && ftEntityGen::nullLifeCnt( $sEntityName );
//        }
//
    ////        // иначе если можно загружать и подгружены динамические таблицы
    ////        elseif ( !$bJustLoaded and self::egInited() ) {
    ////
    ////            // если не верное имя - исправить и попробовать запросить в кэше
    ////            if ( !ftEntityGen::checkName( $sEntityName ) ) {
    ////                $sEntityName = ftEntityGen::rightName( $sEntityName );
    ////                $oModel = self::getEntityDef( $sEntityName, true );
    ////            } // if
    ////
    ////            // если не найдено, запросить из базы
    ////            if ( !$oModel and !ftEntityGen::isMissedEntity( $sEntityName ) ) {
    ////                $def = ftEntityGen::getEntityDef( $sEntityName );
    ////                if ( $def ) {
    ////                    // если нашли - положить в кэш
    ////                    ftEntityGen::addToCache( $sEntityName, $def );
    ////                    $oModel = $def;
    ////                }
    ////                else {
    ////                    // нет - занести с список промахов запросов
    ////                    ftEntityGen::addMissEntity( $sEntityName );
    ////                }
    ////            }
    ////
    ////        }
//
//        return $oDef;
//    }

    /**
     * Отдает массив описания.
     *
     * @static
     *
     * @return array
     */
    public function getModelArray()
    {
        $aFields = [];
        foreach ($this->aFields as $oField) {
            $aFields[$oField->getName()] = $oField->getModelArray();
        }

        $aIndexes = [];
        foreach ($this->aIndexes as $oIndex) {
            $aIndexes[$oIndex->getName()] = $oIndex->getModelArray();
        }

        $aOut = [
            'entity' => $this->aEntity,
            'fields' => $aFields,
            'columns' => $this->aColumns,
            'indexes' => $aIndexes,
        ];

        return $aOut;
    }

    /**
     * Добавляет атрибут
     *
     * @param $sAttrName
     * @param $mVal
     */
    public function setAttr($sAttrName, $mVal)
    {
        $this->aEntity[$sAttrName] = $mVal;
    }

    /**
     * Отдает атрибут
     *
     * @param $sAttrName
     *
     * @return null|mixed
     */
    public function getAttr($sAttrName)
    {
        return isset($this->aEntity[$sAttrName]) ? $this->aEntity[$sAttrName] : null;
    }

    /**
     * Отдает имя сущности.
     *
     * @return string
     */
    public function getName()
    {
        return $this->sName;
    }

    /**
     * Отдает имя таблицы.
     *
     * @return string
     */
    public function getTableName()
    {
        return $this->getTablePrefix() . $this->getName();
    }

    /**
     * Задает префикс таблицы.
     *
     * @param $sPrefix
     */
    public function setTablePrefix($sPrefix)
    {
        $this->setAttr(self::TABLE_PREFIX, (string) $sPrefix);
    }

    /**
     * Отдает префикс таблицы.
     *
     * @return string
     */
    public function getTablePrefix()
    {
        return (string) $this->getAttr(self::TABLE_PREFIX);
    }

    /**
     * Отдает тип сущности.
     *
     * @return int
     */
    public function getType()
    {
        return (int) $this->getAttr('type');
    }

    /**
     * Сохраняет тип сущности.
     *
     * @param int $iType
     */
    public function setType($iType)
    {
        $this->setAttr('type', (int) $iType);
    }

    /**
     * Отдает id родительской карточки.
     *
     * @return int
     */
    public function getParentId()
    {
        return (int) $this->getAttr('parent');
    }

    /**
     * Задает id родительской карточки.
     *
     * @param int $iParentId
     */
    public function setParentId($iParentId)
    {
        $this->setAttr('parent', (int) $iParentId);
    }

    /**
     * Задает ид карточки(сущности в таблице c_entity).
     *
     * @param int $iEntityId
     */
    public function setEntityId($iEntityId)
    {
        $this->setAttr('id', (int) $iEntityId);
    }

    /**
     * Получить ид карточки(сущности в таблице c_entity).
     *
     * @return int
     */
    public function getEntityId()
    {
        return $this->getAttr('id');
    }

    /**
     * Отдает название сущности.
     *
     * @return string
     */
    public function getTitle()
    {
        $sTitle = $this->getAttr(self::TITLE);

        return $sTitle ? $sTitle : $this->getName();
    }

    /**
     * Устанавливает флаг скрытия детальной.
     *
     * @param bool $bHide
     */
    public function setHideDetail($bHide)
    {
        $this->setAttr(self::HIDE_DETAIL, (int) $bHide);
    }

    /**
     * Отдает флаг скрытия детальной.
     *
     * @return null|mixed
     */
    public function getHideDetail()
    {
        $iHideDetail = $this->getAttr(self::HIDE_DETAIL);

        return $iHideDetail;
    }

    /**
     * Отдает набор полей.
     *
     * @return model\Field[]
     */
    public function getFileds()
    {
        return $this->aFields;
    }

    /**
     * Задает имя родительского поля.
     *
     * @deprecated
     *
     * @param string $sFieldName
     */
    public function setParentField($sFieldName)
    {
        $this->setAttr(self::PARENT_FIELD, (string) $sFieldName);
    }

    /**
     * Отдает имя родительского поля.
     *
     * @deprecated
     *
     * @return string
     */
    public function getParentField()
    {
        return (string) $this->getAttr(self::PARENT_FIELD);
    }

    /**
     * Задает тип связи.
     *
     * @deprecated
     *
     * @param string $sType
     */
    public function setConnectionType($sType)
    {
        $this->setAttr('connection_type', (string) $sType);
    }

    /**
     * Отдает тип связи.
     *
     * @deprecated
     *
     * @return string
     */
    public function connectionType()
    {
        // тип установлен в описании
        if ($this->getAttr('connection_type') !== null) {
            return $this->getAttr('connection_type');
        }

        // проверка на жесткую связь "один к одному" ---
        if ($this->getParentEntity()) {
            return '---';
        }

        // проверка на "многие ко многим"
        if ($this->getSourceEntity()) {
            return '><';
        }

        // проверка на "один ко многим"
        if ($this->hasParentField()) {
            return '-<';
        }

        // иначе "один к одному"
        return '--';
    }

    /**
     * Задает тип связи.
     *
     * @deprecated
     *
     * @param string $sType
     */
    public function setSourceEntity($sType)
    {
        $this->setAttr('source_entity', (string) $sType);
    }

    /**
     * Отдает тип связи.
     *
     * @deprecated
     *
     * @return string
     */
    public function getSourceEntity()
    {
        return (string) $this->getAttr('source_entity');
    }

    /**
     * Задает родительскую сущность.
     *
     * @deprecated
     *
     * @param string $sEntityName
     */
    public function setParentEntity($sEntityName)
    {
        $this->setAttr('parent_entity', (string) $sEntityName);
    }

    /**
     * Отдает родительскую сущность.
     *
     * @deprecated
     *
     * @return string
     */
    public function getParentEntity()
    {
        return (string) $this->getAttr('parent_entity');
    }

    /**
     * Отдает объект поля.
     *
     * @param $sFieldName
     *
     * @return null|model\Field
     */
    public function getFiled($sFieldName)
    {
        return $this->hasField($sFieldName) ? $this->aFields[$sFieldName] : null;
    }

    /**
     * Определяет наличие поля.
     *
     * @static
     *
     * @param string $sFieldName имя поля
     *
     * @return bool
     */
    public function hasField($sFieldName)
    {
        return isset($this->aFields[$sFieldName]);
    }

    /**
     * Отдает флаг наличия поля привязки к родительской записи.
     *
     * @deprecated
     *
     * @return bool
     */
    public function hasParentField()
    {
        return $this->hasField('_parent');
    }

    /**
     * Отдает набор имен полей.
     *
     * @return array
     */
    public function getAllFieldNames()
    {
        return array_keys($this->aFields);
    }

    /**
     * Добавляет псевдоним для набора полей.
     *
     * @param $sSetName
     * @param $mFieldList
     *
     * @return string
     */
    public function addColumnSet($sSetName, $mFieldList)
    {
        // набор полей
        $aFieldList = $this->makeFieldsSet($mFieldList);

        return $this->aColumns[$sSetName] = implode(',', $aFieldList);
    }

    /**
     * Отдает все наборы колонок, которые есть.
     *
     * @static
     *
     * @return array
     */
    public function getColumnSetList()
    {
        return $this->aColumns;
    }

    /**
     * Отдает набор колонок по псевдониму.
     *
     * @param $sSetName
     *
     * @return array
     */
    public function getColumnSet($sSetName)
    {
        $mFields = isset($this->aColumns[$sSetName]) ? $this->aColumns[$sSetName] : $sSetName;

        return Fnc::toArray($mFields);
    }

    /**
     * Отдает массив - заготовку для описания сущности.
     *
     * @param $sEntityName
     * @param string $sEntityTitle
     *
     * @return array|bool
     */
    public static function getBlankArray($sEntityName, $sEntityTitle = '')
    {
        $sEntityName = str_replace("'", '', trim($sEntityName));
        if (!$sEntityName) {
            return false;
        }
        $aModel = [];
        $aModel['entity']['id'] = null;
        $aModel['entity']['name'] = $sEntityName;
        $aModel['entity'][self::TITLE] = $sEntityTitle ? $sEntityTitle : $sEntityName;
        $aModel['entity'][self::TABLE_PREFIX] = '';
        $aModel['entity'][self::NAMESPACE_LABEL] = '';
        $aModel['entity'][self::HIDE_DETAIL] = '0';
        $aModel['entity'][self::PRIORITY_SORT] = '0';
        $aModel['fields'] = [];
        $aModel['columns'] = [];
        $aModel['indexes'] = [];
        foreach (Fnc::processorTypes() as $sProcessorName) {
            $aModel[$sProcessorName] = [];
        }

        return $aModel;
    }

    /**
     * Приводит набор имен полей к единому виду и фильтрует по текущему составу полей.
     *
     * @param array|string $mFieldList набор полей
     *
     * @return array
     */
    public function makeFieldsSet($mFieldList)
    {
        // выходной массив
        $aOutFieldList = [];

        // если пуст запрос - вернуть пустой массив
        $aFieldList = Fnc::toArray($mFieldList);

        // перебрать все поля
        foreach ($aFieldList as $sFieldName) {
            // добавить только существующие
            if ($this->hasField($sFieldName)) {
                $aOutFieldList[] = $sFieldName;
            }
        }

        return $aOutFieldList;
    }

    /**
     * Добавляет поле к сущности.
     *
     * @param string $sFieldName
     * @param string $sDatatype тип в базе, может быть с размером в скобках
     * @param string $sTitle
     *
     * @return Entity
     */
    public function addField($sFieldName, $sDatatype = 'varchar', $sTitle = '')
    {
        $this->addFieldObject(
            $this->createField(
                $sFieldName,
                model\Field::getBaseDesc($sDatatype, $sTitle)
            )
        );
    }

    /**
     * Добавляет поле как объект
     *
     * @param model\Field $oField
     */
    public function addFieldObject(model\Field $oField)
    {
        $oField->setModel($this);
        $this->aFields[$oField->getName()] = $oField;
    }

    /**
     * Удаляет поле.
     *
     * @param string $sFieldName имя поля
     *
     * @return bool
     */
    public function delField($sFieldName)
    {
        $bFound = $this->hasField($sFieldName);
        if ($bFound) {
            unset($this->aFields[$sFieldName]);
        }

        return $bFound;
    }

    /**
     * Отдает набор индексов.
     *
     * @return model\Index[]
     */
    public function getIndexes()
    {
        return $this->aIndexes;
    }

    /**
     * Отдает индекс по имени.
     *
     * @param string $sIndexName
     *
     * @return null|model\Index
     */
    public function getIndex($sIndexName)
    {
        return isset($this->aIndexes[$sIndexName]) ? $this->aIndexes[$sIndexName] : null;
    }

    /**
     * Добавляет индекс
     *
     * @param string $sIndexName имя индекса
     * @param array $aFields набор полей
     * @param string $sIndexType тип индекса
     * @param bool $bUnique флаг уникальности
     *
     * @return string имя индекса
     */
    public function addIndex($sIndexName, $aFields, $sIndexType, $bUnique)
    {
        // форматирование набора полей
        $aFields = $this->makeFieldsSet($aFields);

        $oIndex = new model\Index($sIndexName, [
            'fields' => $aFields,
            'index_type' => $sIndexType,
            'unique' => $bUnique,
        ]);
        if (!$oIndex) {
            return;
        }

        return $this->addIndexObject($oIndex);
    }

    /**
     * Добавляет индекс по псевдониму типа.
     *
     * @param string $sIndexName имя индекса
     * @param array $aFields набор полей
     * @param string $sType псевдоним типа
     *
     * @return string имя индекса
     */
    public function addIndexByAlias($sIndexName, $aFields, $sType)
    {
        $oIndex = new model\Index($sIndexName, model\Index::getBaseDesc($aFields, $sType));
        if (!$oIndex) {
            return;
        }

        return $this->addIndexObject($oIndex);
    }

    /**
     * Добавляет индекс к списку.
     *
     * @param model\Index $oIndex
     *
     * @throws exception\Model
     *
     * @return string имя индекса
     */
    protected function addIndexObject(model\Index $oIndex)
    {
        // сгенерировать имя
        $sIndexName = $oIndex->getName();

        $sFirstField = $oIndex->getFirstFieldName();
        if (!$sFirstField) {
            throw new exception\Model('Не заданы поля для индекса');
        }
        $cnt = 1;
        while (isset($this->aIndexes[$sIndexName])) {
            $sIndexName = $sFirstField . '_' . ++$cnt;
            $oIndex->setName($sIndexName);
            if ($cnt > 10) {
                throw new exception\Model('Не могу создать индекс для сущности `' . $this->getName() . '` - достигнут предел перебора');
            }
        }

        $this->aIndexes[$sIndexName] = $oIndex;

        return $oIndex->getName();
    }

    /**
     * Определяет, является ли сущность мультиязычной.
     *
     * @return bool
     */
    public function isMultilang()
    {
        // если есть хоть одно мультиязычное поле
        foreach ($this->getFileds() as $oField) {
            if ($oField->isMultilang()) {
                return true;
            }
        }

        return false;
    }

    /*
     * Отношения
     */

    /**
     * Добавляет связь с сущностью.
     *
     * @param string $sType тип связи
     * @param string $sEntityName имя связанной сушщности
     * @param string $sContentField [виртуальне] поле для "хранениия" связи
     * @param string $sInnerFieldName имя поля для связи в этой сущноти
     * @param string $sExternalFieldName имя поля в подчиненной сущности
     *
     * @return $this
     */
    public function addRelation($sType, $sEntityName, $sContentField, $sInnerFieldName, $sExternalFieldName)
    {
        if (!$this->hasField($sContentField)) {
            $this->addField($sContentField, 'int');
        }

        $oField = $this->getFiled($sContentField);

        $oRelation = new Relation($sType, $sEntityName, $sContentField, $sInnerFieldName, $sExternalFieldName);

        $oField->addRelation($oRelation);

        $oField->setFictitious($oRelation->fieldIsFictions());
    }

    /**
     * Отдает первый найденный объект связи для поля, если есть.
     *
     * @param $sFieldName
     *
     * @return null|Relation
     */
    public function getOneFieldRelation($sFieldName)
    {
        $oField = $this->getFiled($sFieldName);
        if (!$oField) {
            return [];
        }

        $aList = $oField->getRelationList();
        if (count($aList)) {
            return array_shift($aList);
        }
    }

    /**
     * Отдает имя первичного ключа.
     *
     * @return string
     */
    public function getPrimaryKey()
    {
        return $this->sPrimaryKey;
    }

    /**
     * Задает имя поля для первичного ключа.
     *
     * @param mixed $sFieldName
     */
    public function setPrimaryKey($sFieldName = 'id')
    {
        $this->sPrimaryKey = $sFieldName;
    }

    /**
     * Отдает адрес в пространстве имен.
     *
     * @return string
     */
    public function getNamespace()
    {
        return $this->getAttr(self::NAMESPACE_LABEL);
    }

    /**
     * Задает адрес в пространстве имен.
     *
     * @param string $sNamespace
     */
    public function setNamespace($sNamespace)
    {
        $this->setAttr(self::NAMESPACE_LABEL, $sNamespace);
    }

    /**
     * Отдает полное имя - с пространством имен.
     *
     * @return string
     */
    public function getFullName()
    {
        return sprintf('%s\\%s', $this->getNamespace(), $this->getName());
    }

    /**
     * Отдает тип таблицы.
     *
     * @return string
     */
    public function getTableType()
    {
        $sVal = $this->getAttr('table_type');

        return $sVal ? $sVal : DBTable::TypeMyIsam;
    }

    /**
     * Задает тип таблицы в базе данных.
     *
     * @param string $sTableType
     */
    public function setTableType($sTableType)
    {
        $this->setAttr('table_type', $sTableType);
    }

    /**
     * Отдает набор значений по умолчанию для полей.
     */
    public function getDefValList()
    {
        $aOut = [];
        foreach ($this->getFileds() as $oField) {
            $aOut[$oField->getName()] = $oField->getDefault();
        }

        return $aOut;
    }

    /**
     * Создвет поле и добавляет его в модель.
     *
     * @param string $sFieldName имя поля
     * @param array $aField описание плоя
     *
     * @throws exception\Model
     *
     * @return model\Field
     */
    private function createField($sFieldName, $aField)
    {
        $sDatatype = $aField['datatype'] ?? 'varchar';

        // временная мера
        switch ($sDatatype) {
            case 'datetime':
                $sClassName = 'skewer\base\ft\model\field\DateTime';
                break;

            default:
                $sClassName = 'skewer\base\ft\model\Field';
                break;
        }

        $oField = new $sClassName($sFieldName, $aField);
        if (!$oField instanceof model\Field) {
            throw new exception\Model(sprintf(
                'Класс поля %s.%s должен быть унаследован от %s',
                $this->getName(),
                $sFieldName,
                'ft\model\Field'
            ));
        }

        $oField->setModel($this);

        return $oField;
    }

    public function setPrioritySort($value)
    {
        $this->setAttr('priority_sort', $value);
    }
}

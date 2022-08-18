<?php

namespace skewer\base\ft\model;

use skewer\base\ft as ft;
use skewer\base\log\Logger;
use skewer\base\orm;
use skewer\components\catalog\Card;
use skewer\components\catalog\Dict;
use yii\db\Exception;
use yii\db\Query;
use yii\helpers\ArrayHelper;

/**
 * Класс описания поля сущности
 * ver 2.00.
 *
 * 1. сделать прототип с моделью попроще (без спец ft полей)
 * 2. этот класс сделать расширением описанного выше
 * 3. отказаться от описаний массивом
 * 4. сделать класс импорта/экспорта ( массив, yaml )
 */
class Field
{
    /** @var string имя сущности */
    protected $sName;

    /** @var string название сущности */
    protected $sTitle;

    /** @var array описание поля */
    protected $aFieldOptions = [];

    /** @var ft\Model ссылка на родительскую модель */
    protected $oModel;

    /** @var ft\Relation[] набор связей */
    protected $aRelations = [];

    /**
     * Конструктор
     *
     * @param string $sFieldName имя поля
     * @param array $aFieldOptions описание поля
     *
     * @throws ft\Exception
     */
    public function __construct($sFieldName, $aFieldOptions)
    {
        // если формат данных заведомо левый - то выходим
        if (!is_array($aFieldOptions)) {
            throw new ft\Exception('Неверный тип контейнера описания для поля');
        }
        // сохраняем описание
        $this->aFieldOptions = $aFieldOptions;

        // добавление отношений
        $aRelations = $aFieldOptions['relations'] ?? [];
        if (!is_array($aRelations)) {
            throw new ft\exception\Model('Неверный контейнер связей');
        }
        foreach ($aRelations as $aRel) {
            $oRel = new ft\Relation(
                $aRel['type'] ?? '',
                $aRel['entity'] ?? '',
                $aRel['content'] ?? '',
                $aRel['inner'] ?? '',
                $aRel['external'] ?? ''
            );
            $this->aRelations[] = $oRel;
        }

        // имя поля
        $this->sName = (string) $sFieldName;
        if (!$this->sName) {
            throw new ft\Exception('Отсутствует имя поля');
        }
        // название сущности
        $this->sTitle = (isset($aFieldOptions['title']) and $aFieldOptions['title']) ?
            (string) $aFieldOptions['title'] :
            $this->getName();
    }

    /**
     * Отдает базовый набор для генерации поля.
     *
     * @param string $sDatatype
     * @param string $sTitle
     *
     * @return array
     */
    public static function getBaseDesc($sDatatype, $sTitle)
    {
        // задан размер
        if (preg_match('/^(\w+)[(](\d+)[)]$/i', $sDatatype, $find)) {
            $sDatatype = $find[1];
            $size = $find[2];
        } else {
            $size = '';
        }

        if ($sDatatype === 'str') {
            $sDatatype = 'varchar';
        }

        // формирование описания поля
        return [
            'datatype' => $sDatatype,
            'multilang' => 0,
            'type' => 1,
            'required' => 1,
            'fictitious' => false,
            'size' => $size,
            'title' => $sTitle,
            'default' => '',
            'editor' => '',
            'params' => [],
            'widget' => [],
            'validator' => [],
            'modificator' => [],
            'relations' => [],
            'link_id' => 0, // закэшировать id связи (используется при выборе профиля галереи)
        ];
    }

    /**
     * Добавляет атрибут
     *
     * @param $sOptionName
     * @param mixed $mVal
     *
     * @return mixed
     */
    public function setOption($sOptionName, $mVal)
    {
        return $this->aFieldOptions[$sOptionName] = $mVal;
    }

    /**
     * Отдает атрибут
     *
     * @param $sOptionName
     *
     * @return null|mixed
     */
    public function getOption($sOptionName)
    {
        return isset($this->aFieldOptions[$sOptionName]) ? $this->aFieldOptions[$sOptionName] : null;
    }

    /**
     * Отдает массив описания.
     *
     * @return array
     */
    public function getModelArray()
    {
        $aModel = $this->aFieldOptions;

        $aModel['relations'] = [];

        foreach ($this->aRelations as $oRelation) {
            $aModel['relations'][] = $oRelation->getModelArray();
        }

        return $aModel;
    }

    /**
     * Задает значение флага "фиктивное поле".
     *
     * @param bool $mVal
     */
    public function setFictitious($mVal)
    {
        $this->setOption('fictitious', (bool) $mVal);
    }

    /**
     * Задает значение флага "фиктивное поле".
     *
     * @return bool
     */
    public function getFictitious()
    {
        return (bool) $this->getOption('fictitious');
    }

    /**
     * Флаг фиктивного поля.
     *
     * @return bool
     */
    public function isFictitious()
    {
        return $this->getFictitious();
    }

    /**
     * Отдает имя поля.
     *
     * @return string
     */
    public function getName()
    {
        return $this->sName;
    }

    /**
     * Задает имя поля.
     *
     * @param string $sName
     */
    public function setName($sName)
    {
        $this->sName = $sName;
    }

    /**
     * Отдает название поля.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->sTitle;
    }

    /**
     * Установить название поля.
     *
     * @param string $sTitle
     */
    public function setTitle($sTitle)
    {
        $this->sTitle = $sTitle;
    }

    /**
     * Отдает тип переменной в базе.
     *
     * @return string
     */
    public function getDatatype()
    {
        return (string) $this->getOption('datatype');
    }

    /**
     * Отдает тип переменной в базе с размерностью в скобках, если есть.
     *
     * @return string
     */
    public function getDatatypeFull()
    {
        // тип
        $sOut = $this->getDatatype();

        // расширить размерностью, если задана
        if ($this->getSize()) {
            $sOut .= sprintf('(%d)', $this->getSize());
        }

        // для int по умолчанию размерность 11
        elseif ($sOut === 'int') {
            $sOut .= '(11)';
        }

        return $sOut;
    }

    /**
     * Отдает размер переменной в базе.
     *
     * @return int
     */
    public function getSize()
    {
        return (int) $this->getOption('size');
    }

    /**
     * Задает переметр "обязательное".
     *
     * @param $mVal
     *
     * @return string
     */
    public function setRequired($mVal)
    {
        return (string) $this->setOption('required', (int) (bool) $mVal);
    }

    /**
     * Задает значение мультиязычности.
     *
     * @param $iVal
     */
    public function setMultilang($iVal)
    {
        $this->setOption('multilang', (int) $iVal);
    }

    /**
     * Отдает значение мультиязычности.
     *
     * @return int
     */
    public function getMultilang()
    {
        return (int) $this->getOption('multilang');
    }

    /**
     * Отдает флаг "мультиязычное".
     *
     * @return bool
     */
    public function isMultilang()
    {
        return (bool) $this->getMultilang();
    }

    /**
     * Возвращает true, если поле - подчиненная сущность.
     *
     * @return bool
     */
    public function isEntity()
    {
        return false;
    }

    /**
     * Отдает имя редактора.
     *
     * @return string
     */
    public function getEditorName()
    {
        $aEditor = $this->getOption('editor');
        if (is_array($aEditor) and isset($aEditor['name'])) {
            return (string) $aEditor['name'];
        }

        return '';
    }

    /**
     * Отдает параметры редактора.
     *
     * @return array
     */
    public function getEditorParams()
    {
        $aEditor = $this->getOption('editor');
        if (is_array($aEditor) and isset($aEditor['params'])) {
            return $aEditor['params'];
        }

        return [];
    }

    /**
     * Устанавливает имя редактора для поля.
     *
     * @param string $sEditorName
     * @param array $aParam
     */
    public function setEditor($sEditorName, $aParam = [])
    {
        $this->setOption('editor', [
            'name' => $sEditorName,
            'params' => $aParam,
        ]);
    }

    /**
     * Отдает массив с набором параметров.
     *
     * @return array
     */
    public function getParameterList()
    {
        return $this->aFieldOptions['params'];
    }

    /**
     * Сохраняет массив с набором параметров.
     *
     * @param $aParamList
     */
    public function setParameterList($aParamList)
    {
        $this->aFieldOptions = $aParamList;
    }

    /**
     * Отдает значение параметра.
     *
     * @static
     *
     * @param $sParamName
     *
     * @return null|mixed
     */
    public function getParameter($sParamName)
    {
        return isset($this->aFieldOptions['params'][$sParamName]) ? $this->aFieldOptions['params'][$sParamName] : null;
    }

    /**
     * Задает значение параметра.
     *
     * @static
     *
     * @param $sParamName
     * @param $mValue
     */
    public function setParameter($sParamName, $mValue)
    {
        $this->aFieldOptions['params'][(string) $sParamName] = $mValue;
    }

    /**
     * Возвращает значение всех атрибутов поля.
     *
     * @return []
     */
    public function getAttrs()
    {
        return isset($this->aFieldOptions['attrs']) ? $this->aFieldOptions['attrs'] : [];
    }

    /**
     * Возвращает значение атрибута.
     *
     * @param string $sAttrName Имя атрибута
     *
     * @return string
     */
    public function getAttr($sAttrName)
    {
        return $this->isSetAttr($sAttrName) ? $this->aFieldOptions['attrs'][$sAttrName] : null;
    }

    /**
     * Устанавливает значение для атрибута.
     *
     * @param int $sAttrName Имя атрибута
     * @param bool|int|string $sAttrValue Значение атрибута
     */
    public function setAttr($sAttrName, $sAttrValue = false)
    {
        $this->aFieldOptions['attrs'][$sAttrName] = $sAttrValue;
    }

    /**
     * Проверка наличие атрибута.
     *
     * @param string $sAttrName имя атрибута
     *
     * @return bool
     */
    public function isSetAttr($sAttrName)
    {
        return isset($this->aFieldOptions['attrs'][$sAttrName]);
    }

    /**
     * Задает ссылку на модель.
     *
     * @param ft\Model $oModel
     */
    public function setModel(ft\Model $oModel)
    {
        $this->oModel = $oModel;
    }

    /**
     * Отдает ссылку на родительскую модель.
     *
     * @return \skewer\base\ft\Model
     */
    public function getModel()
    {
        return $this->oModel;
    }

    /**
     * Добавить в контейнер описания сущности - уникальное значение.
     *
     * @param $sContName
     * @param $sValue
     */
    protected function addToArrayUnique($sContName, $sValue)
    {
        if (!in_array($sValue, $this->aFieldOptions[$sContName])) {
            $this->aFieldOptions[$sContName][] = $sValue;
        }
    }

    /**
     * Добавление виджета.
     *
     * @param $sPName
     * @param array $aParam
     */
    public function addWidget($sPName, $aParam = [])
    {
        //$this->addToArrayUnique('widget', $sPName);

        // проверка на уникальность
        if (count($this->aFieldOptions['widget'])) {
            foreach ($this->aFieldOptions['widget'] as $aWidget) {
                if ($aWidget['name'] == $sPName) {
                    return;
                }
            }
        }

        $this->aFieldOptions['widget'][] = [
            'name' => $sPName,
            'params' => $aParam,
        ];
    }

    /**
     * Удаляет виджет по имени.
     *
     * @param string $sPName
     */
    public function delWidget($sPName)
    {
        foreach ($this->aFieldOptions['widget'] as $iKey => $aWidget) {
            if ($aWidget['name'] == $sPName) {
                unset($this->aFieldOptions['widget'][$iKey]);
            }
        }
    }

    /**
     * Отдает имя первого виджета.
     *
     * @return string
     */
    public function getWidgetName()
    {
        $aWidgetList = $this->aFieldOptions['widget'];

        if (!$aWidgetList) {
            return '';
        }

        return $aWidgetList[0]['name'] ?? '';
    }

    /**
     * Добавление модификатора.
     *
     * @param string $sMName Имя модификатора
     * @param array $aParam Дополнительные парамеры
     */
    public function addModificator($sMName, $aParam = [])
    {
        $this->aFieldOptions['modificator'][] = [
            'name' => $sMName,
            'params' => $aParam,
        ];
    }

    /**
     * Добавление валидатора.
     *
     * @param string $sPName
     * @param array $aParam
     */
    public function addValidator($sPName, $aParam = [])
    {
        // проверка на уникальность
        if (count($this->aFieldOptions['validator'])) {
            foreach ($this->aFieldOptions['validator'] as $aValidator) {
                if ($aValidator['name'] == $sPName) {
                    return;
                }
            }
        }

        $this->aFieldOptions['validator'][] = [
            'name' => $sPName,
            'params' => $aParam,
        ];
    }

    /**
     * Удаляет валидатор по имени.
     *
     * @param string $sPName
     */
    public function delValidator($sPName)
    {
        foreach ($this->aFieldOptions['validator'] as $iKey => $aValidator) {
            if ($aValidator['name'] == $sPName) {
                unset($this->aFieldOptions['validator'][$iKey]);
            }
        }
    }

    /**
     * Отдает список валидаторов.
     *
     * @param orm\ActiveRecord $oRow
     *
     * @throws ft\Exception
     *
     * @return ft\proc\validator\Prototype[]
     */
    public function getValidatorList($oRow)
    {
        $aList = [];

        foreach ($this->aFieldOptions['validator'] as $aValidator) {
            $sName = is_array($aValidator) ? ($aValidator['name'] ?? '') : $aValidator;
            $sClassName = 'skewer\\base\\ft\\proc\\validator\\' . ucfirst($sName);

            if (!class_exists($sClassName)) {
                throw new ft\Exception("Не найден валидатор [{$sName}]");
            }
            /** @var ft\proc\validator\Prototype $oValidator */
            $oValidator = new $sClassName();
            $oValidator->setField($this);
            $oValidator->setModel($this->getModel());
            if ($oRow !== null) {
//                if ( !$oRow instanceof ft\ArPrototype )
//                    throw new ft\exception\Model(
//                        'First param for ft\model\Field::getValidatorList must be ft\ArPrototype or null'
//                    );
                $oValidator->setRow($oRow);
            }
            $oValidator->setParamList($aValidator['params']);

            $oValidator->checkInit();

            $aList[] = $oValidator;
        }

        return $aList;
    }

    /**
     * Задает значение поля по умолчанию.
     *
     * @param mixed $mVal
     */
    public function setDefault($mVal)
    {
        $this->setOption('default', $mVal);
    }

    /**
     * Возвращшает значение по-умолчанию для атрибута сущности.
     *
     * @return mixed
     */
    public function getDefault()
    {
        return $this->getOption('default');
    }

    /**
     * Возвращает Описание атрибута сущности.
     *
     * @return string
     */
    public function getDescription()
    {
        return '';
    }

    /**
     * Добавление связи.
     *
     * @param ft\Relation $oRelation
     */
    public function addRelation(ft\Relation $oRelation)
    {
        $this->aRelations[] = $oRelation;

        if ($oRelation->getType() == ft\Relation::MANY_TO_MANY) {
            $this->buildLinkTable();
        }
    }

    /**
     * Добавление связи.
     *
     * @return ft\Relation[]
     */
    public function getRelationList()
    {
        return $this->aRelations;
    }

    /**
     * Получаем первкю связь поля.
     *
     * @return ft\Relation
     */
    public function getFirstRelation()
    {
        return count($this->aRelations) ? $this->aRelations[0] : null;
    }

    /**
     * Имя таблицы связей (><).
     *
     * @return string
     */
    public function getLinkTableName()
    {
        // плохое имя так как невозможно повторить из связанной сущности
        return 'cr_' . $this->getModel()->getName() . '__' . $this->getName();
    }

    /**
     * Создание таблицы связей.
     *
     * @return bool
     */
    public function buildLinkTable()
    {
        ft\Entity::get($this->getLinkTableName())
            ->clear(false)
            ->setNamespace(__NAMESPACE__)
            ->addField(ft\Relation::INNER_FIELD, 'int', 'Ид внутренней сущности')
            ->addField(ft\Relation::EXTERNAL_FIELD, 'int', 'Ид внешней сущности')
            ->addField(ft\Relation::SORT_FIELD, 'int', 'Вес для сортировки')
            ->selectFields(ft\Relation::INNER_FIELD . ',' . ft\Relation::EXTERNAL_FIELD)
            ->addIndex('unique')
            ->save()
            ->build();

        return true;
    }

    /**
     * Удаление таблицы связей.
     *
     * @return bool
     */
    public function removeLinkTable()
    {
        orm\Query::dropTable($this->getLinkTableName());

        return true;
    }

    /**
     * Очистка таблицы связей.
     *
     * @return bool
     */
    public function clearLinkTable()
    {
        orm\Query::truncateTable($this->getLinkTableName());

        return true;
    }

    /**
     * Набор связанных записей.
     *
     * @param int $id Ид записи для которой собираем связи
     *
     * @return array
     */
    public function getLinkRow($id)
    {
        $query = orm\Query::SelectFrom($this->getLinkTableName())
            ->where(ft\Relation::INNER_FIELD, $id)
            ->asArray();

        // Сортировка позиций справочника мультисписка.
        // Если нужна сортировка по порядку выбора значений в админке, то блок можно просто закомментировать
        if (($this->getEditorName() == ft\Editor::MULTISELECT || $this->getEditorName() == ft\Editor::MULTISELECTIMAGE) and
             ($oRelation = $this->getFirstRelation()) and
             ($sDictTableName = Dict::getDictTableName($oRelation->getEntityName()))) {
            $query
                ->join('on', $sDictTableName, '', 'id = ' . ft\Relation::EXTERNAL_FIELD)
                ->order(Card::FIELD_SORT);
        } else {
            $query
                ->order(ft\Relation::SORT_FIELD);
        }

        $out = [];

        while ($row = $query->each()) {
            $out[] = $row[ft\Relation::EXTERNAL_FIELD];
        }

        return $out;
    }

    /**
     * Получаем существующие данные о связях.
     *
     * @param array $aExtId
     * @param mixed $aInnId
     *
     * @return bool|orm\state\StateSelect
     */
    public function getRelatData($aInnId = '')
    {
        if (!$aInnId) {
            return [];
        }

        $aRelatData = orm\Query::SelectFrom($this->getLinkTableName())
            ->fields([ft\Relation::EXTERNAL_FIELD, ft\Relation::SORT_FIELD])
            ->where(ft\Relation::INNER_FIELD, $aInnId)
            ->asArray()
            ->get();

        $aRelatData = ArrayHelper::index($aRelatData, ft\Relation::EXTERNAL_FIELD);

        return $aRelatData;
    }

    public function getMaxPosExts()
    {
        $aMaxPosExtData = (new Query())
            ->select(ft\Relation::EXTERNAL_FIELD . ', max(' . ft\Relation::SORT_FIELD . ') as ' . ft\Relation::SORT_FIELD)
            ->from($this->getLinkTableName())
            ->groupBy(ft\Relation::EXTERNAL_FIELD)
            ->all();

        if ($aMaxPosExtData) {
            $aMaxPosExtData = ArrayHelper::index($aMaxPosExtData, ft\Relation::EXTERNAL_FIELD);
        }

        return $aMaxPosExtData;
    }

    /**
     * Обнавление набора связанных записей.
     *
     * @param $iGoodsId
     * @param array $aRelatId
     *
     * @throws Exception
     *
     * @return bool
     */
    public function updLinkRow($iGoodsId, $aRelatId = [])
    {
        try {
            $transaction = \Yii::$app->db->beginTransaction();

            //список элементов для товара
            $aRelatData = $this->getRelatData($iGoodsId);
            //max pos для элементов коллекции
            $aMaxPosExtData = $this->getMaxPosExts();

            //для удаления
            $aRemoveData = [];
            //для добавления
            $aAddData = [];

            //проверяем то что надо удалить
            if ($aRelatData) {
                foreach ($aRelatData as $key => $item) {
                    if (!in_array($key, $aRelatId)) {
                        $aRemoveData[] = $key;
                        unset($aRelatData[$key]);
                    }
                }
            }

            //удаляем
            $this->removeLinkTableRows($iGoodsId, $aRemoveData);

            //проверяем то что надо добавить
            if ($aRelatId) {
                foreach ($aRelatId as $item) {
                    if (!array_key_exists($item, $aRelatData)) {
                        $aAddData[] = [$iGoodsId, $item, $this->checkPos($item, $aMaxPosExtData)];
                    }
                }
            }

            //добавляем
            $this->addLinkTableRows($aAddData);

            $transaction->commit();

            return true;
        } catch (Exception $e) {
            Logger::dumpException($e);
            $transaction->rollBack();
        }
    }

    /**
     * Удаление всех связей с элементом
     *
     * @param int $id Идентификатор элемента связанной сущности
     *
     * @return bool
     */
    public function unLinkAllRow($id)
    {
        orm\Query::DeleteFrom($this->getLinkTableName())
            ->where(ft\Relation::EXTERNAL_FIELD, $id)
            ->get();

        return true;
    }

    /**
     * @param $iCollectionId
     * @param $iItemId
     * @param $iPlaceId
     * @param string $sPos
     *
     * @return bool
     */
    public function sortSwap($iCollectionId, $iItemId, $iPlaceId, $sPos = '')
    {
        $oItem = orm\Query::SelectFrom($this->getLinkTableName())
            ->where(ft\Relation::INNER_FIELD, $iItemId)
            ->where(ft\Relation::EXTERNAL_FIELD, (int) $iCollectionId)
            ->getOne();

        $oTarget = orm\Query::SelectFrom($this->getLinkTableName())
            ->where(ft\Relation::INNER_FIELD, $iPlaceId)
            ->where(ft\Relation::EXTERNAL_FIELD, (int) $iCollectionId)
            ->getOne();

        if (empty($oItem) || empty($oTarget)) {
            return false;
        }

        $iItemPos = $oItem[ft\Relation::SORT_FIELD];
        $iTargetPos = $oTarget[ft\Relation::SORT_FIELD];

        // выбираем напрвление сдвига
        if ($iItemPos > $iTargetPos) {
            $iStartPos = $sPos == 'before' ? $iTargetPos - 1 : $iTargetPos;
            $iEndPos = $iItemPos;
            $iNewPos = $sPos == 'before' ? $iTargetPos : $iTargetPos + 1;
            $this->shiftPosition($iCollectionId, $iStartPos, $iEndPos, '+');
            $this->changePosition($iItemId, $iCollectionId, $iNewPos);
        } else {
            $iStartPos = $iItemPos;
            $iEndPos = $sPos == 'after' ? $iTargetPos + 1 : $iTargetPos;
            $iNewPos = $sPos == 'after' ? $iTargetPos : $iTargetPos - 1;
            $this->shiftPosition($iCollectionId, $iStartPos, $iEndPos, '-');
            $this->changePosition($iItemId, $iCollectionId, $iNewPos);
        }

        return true;
    }

    /**
     * @param $iCollection
     * @param $iStartPos
     * @param $iEndPos
     * @param string $sSign
     */
    private function shiftPosition($iCollection, $iStartPos, $iEndPos, $sSign = '+')
    {
        orm\Query::UpdateFrom($this->getLinkTableName())
            ->set(ft\Relation::SORT_FIELD . '=' . ft\Relation::SORT_FIELD . $sSign . '?', 1)
            ->where(ft\Relation::SORT_FIELD . '>?', (int) $iStartPos)
            ->where(ft\Relation::SORT_FIELD . '<?', (int) $iEndPos)
            ->where(ft\Relation::EXTERNAL_FIELD, (int) $iCollection)
            ->get();
    }

    /**
     * @param $iGoods
     * @param $iCollection
     * @param $iPos
     */
    private function changePosition($iGoods, $iCollection, $iPos)
    {
        orm\Query::UpdateFrom($this->getLinkTableName())
            ->set(ft\Relation::SORT_FIELD, (int) $iPos)
            ->where(ft\Relation::INNER_FIELD, (int) $iGoods)
            ->where(ft\Relation::EXTERNAL_FIELD, (int) $iCollection)
            ->get();
    }

    /**
     * @param $iGoodId
     * @param $aExtData
     *
     * @throws \yii\db\Exception
     */
    public function removeLinkTableRows($iGoodId, $aExtData)
    {
        (new Query())->createCommand()->delete($this->getLinkTableName(), [ft\Relation::INNER_FIELD => $iGoodId, ft\Relation::EXTERNAL_FIELD => $aExtData])->execute();
    }

    /**
     * @param $aData
     *
     * @throws Exception
     */
    public function addLinkTableRows($aData)
    {
        (new Query())->createCommand()->batchInsert($this->getLinkTableName(), [ft\Relation::INNER_FIELD, ft\Relation::EXTERNAL_FIELD, ft\Relation::SORT_FIELD], $aData)->execute();
    }

    /**
     * @param $iExtId
     * @param $aMaxPos
     *
     * @return int
     */
    private function checkPos($iExtId, $aMaxPos)
    {
        if (array_key_exists($iExtId, $aMaxPos)) {
            return $aMaxPos[$iExtId][ft\Relation::SORT_FIELD] + 1;
        }

        return 1;
    }
}

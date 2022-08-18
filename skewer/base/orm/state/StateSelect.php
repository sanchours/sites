<?php

namespace skewer\base\orm\state;

use skewer\base\ft;
use skewer\base\orm\ActiveRecord;
use skewer\base\orm\service;

/**
 * Построитель запросов типа SELECT
 * Class StateSelect.
 */
class StateSelect extends StatePrototype
{
    /** @var bool Флаг возвращать результат в виде массива */
    protected $asArray = false;

    /** @var int Счетчик JOIN в запросе */
    protected $iJoinCount = 0;

    /** @var int Счетчик элментов в выборке */
    protected $pRowCounter;

    /** @var bool Считать только кол-во */
    protected $bOnlyCount = false;

    /** @var bool Флаг использоания итератора */
    protected $bEachIterator = false;

    /** @var string Имя поля, которое использовать в качестве ключей выходного массива */
    private $sIndexField = '';

    /**
     * Флаг использоания итератора.
     *
     * @return bool
     */
    public function getBEachIterator()
    {
        return $this->bEachIterator;
    }

    /**
     * Задает набор полей для вывода в результате.
     *
     * @param $mFieldList
     * @param bool $bPure
     *
     * @return StateSelect
     */
    public function fields($mFieldList, $bPure = false)
    {
        if ($bPure) {
            $this->getStorage()->add('fields', $mFieldList);

            return $this;
        }

        if (!is_array($mFieldList)) {
            $mFieldList = explode(',', $mFieldList);
        }

        foreach ($mFieldList  as $sField) {
            $this->field($sField);
        }

        return $this;
    }

    /** Добавляет одно поле в набор полей */
    public function field($sField)
    {
        if (($pos = mb_strpos($sField, '.')) !== false) {
            $this->getStorage()->add('fields', [
                'alias' => trim(mb_substr($sField, 0, $pos)),
                'name' => trim(mb_substr($sField, $pos + 1)),
            ]);
        } else {
            $this->getStorage()->add('fields', [
                'name' => trim($sField),
                'alias' => '',
            ]);
        }
    }

    /**
     * Добавление выражения в секцию WHERE.
     *
     * @param string $sExpr Выражение
     * @param mixed $mValue Данные
     *
     * @return StateSelect
     */
    public function where($sExpr, $mValue = true)
    {
        if (is_array($sExpr)) {
            foreach ($sExpr as $sCurExpr => $mCurValue) {
                $this->addExpr2Section('where', $sCurExpr, $mCurValue, service\Storage::SEP_LOGIC_AND);
            }

            return $this;
        }

        $this->addExpr2Section('where', $sExpr, $mValue, service\Storage::SEP_LOGIC_AND);

        return $this;
    }

    /**
     * Добавить условие без форматирования.
     *
     * @param $sExpr
     * @param string $sSep
     *
     * @return $this
     */
    public function whereRaw($sExpr, $sSep = service\Storage::SEP_LOGIC_AND)
    {
        $this->addRawExpr2Section('where', $sExpr, $sSep);

        return $this;
    }

    /**
     * Добавление выражения в секцию WHERE с разделителем AND.
     *
     * @param string $sExpr Выражение
     * @param mixed $mValue Данные
     *
     * @return StateSelect
     */
    public function andWhere($sExpr, $mValue = true)
    {
        return $this->where($sExpr, $mValue);
    }

    /**
     * Добавление выражения в секцию WHERE с разделителем OR.
     *
     * @param string $sExpr Выражение
     * @param bool $mValue Данные
     *
     * @return StateSelect
     */
    public function orWhere($sExpr, $mValue = true)
    {
        $this->addExpr2Section('where', $sExpr, $mValue, service\Storage::SEP_LOGIC_OR);

        return $this;
    }

    /**
     * Добавление условия в секцию ORDER ИН.
     *
     * @param $sField
     * @param string $sWay
     *
     * @return StateSelect
     */
    public function order($sField, $sWay = 'ASC')
    {
        $this->addOrder($sField, $sWay);

        return $this;
    }

    /**
     * Добавление секции LIMIT.
     *
     * @param int $iCount Кол-во элементов в выборке
     * @param int $iShift Смещение выборки
     *
     * @return StateSelect
     */
    public function limit($iCount, $iShift = 0)
    {
        if ($iShift < 0) {
            $iShift = 0;
        }
        $this->addLimit($iCount, $iShift);

        return $this;
    }

    /**
     * Получение набора позиций выборки.
     *
     * @return mixed
     */
    public function getAll()
    {
        if (!$this->asArray && $this->getQ()->getEntity() === null) {
            $this->asArray = true;
        }

        return $this->get();
    }

    /**
     * Итератор
     *
     * @return null|ActiveRecord|array
     */
    public function each()
    {
        if (!$this->bEachIterator) {
            $this->get(false);
            $this->bEachIterator = true;
        }

        if ($row = $this->oAdapter->fetchArray()) {
            $row = $this->asArray ? $row : $this->getNewRow($row);
        }

        return $row;
    }

    /**
     * Получение первой позиции выборки.
     *
     * @return ActiveRecord|array|bool
     */
    public function getOne()
    {
        $aItems = $this->limit(1)->getAll();

        return count($aItems) ? $aItems[0] : false;
    }

    /**
     * Получить кол-во позиций.
     *
     * @param mixed $sField
     *
     * @return int
     */
    public function getCount($sField = '*')
    {
        $this->bOnlyCount = $sField;

        return $this->get();
    }

    /**
     * Добавление новой таблицы или набора таблиц в секцию FROM.
     *
     * @param array|string $mTable Переменая с именами таблиц
     * @param string $sAlias Псевдоним таблицы
     *
     * @return StateSelect
     */
    public function from($mTable, $sAlias = '')
    {
        if (mb_strpos($mTable, ',') !== false) {
            $mTable = explode(',', $mTable);
        }

        if (is_array($mTable)) {
            foreach ($mTable as $sTableName) {
                $this->addFrom(trim($sTableName));
            }
        } elseif ($sAlias) {
            $this->addFrom($mTable . ' AS ' . $sAlias);
        } else {
            $this->addTableName($mTable, $sAlias);
        }

        return $this;
    }

    /**
     * Добавление секции JOIN.
     *
     * @param string $sType Тип (склейки) запроса: 'left', 'right', 'inner', 'full outer'
     * @param string $mTable Имя таблицы
     * @param string $sAlias Псевдоним таблицы
     * @param string $sCondition Условие склейки
     *
     * @return StateSelect
     */
    public function join($sType, $mTable, $sAlias = '', $sCondition)
    {
        ++$this->iJoinCount;

        $aJoinType = ['left' => 'LEFT JOIN', 'right' => 'RIGHT JOIN', 'inner' => 'INNER JOIN', 'full outer' => 'FULL OUTER JOIN'];
        if (!isset($aJoinType[$sType])) {
            $sType = 'left';
        }

        $sType = $aJoinType[$sType];

        if (!$sAlias) {
            $sAlias = 'jt' . ($this->iJoinCount);
        }

        $this->getStorage()->add(
            'join' . ($this->iJoinCount),
            [
                'type' => $sType,
                'table' => $mTable,
                'alias' => $sAlias,
            ]
        );

        $this->addExpr2Section('on' . ($this->iJoinCount), $sCondition, false, service\Storage::SEP_LOGIC_AND);

        return $this;
    }

    /**
     * @param $sExpr
     * @param bool $mValue
     *
     * @return StateSelect
     */
    public function on($sExpr, $mValue = true)
    {
        if (!$this->iJoinCount) {
            return $this;
        }

        $this->addExpr2Section('on' . ($this->iJoinCount), $sExpr, $mValue, service\Storage::SEP_LOGIC_AND);

        return $this;
    }

    /**
     * Установить флаг возврата результата как массив.
     *
     * @return StateSelect
     */
    public function asArray()
    {
        $this->asArray = true;

        return $this;
    }

    /**
     * Сохранение общего кол-ва элементов.
     *
     * @param int $iAllCount
     *
     * @return $this
     */
    public function setCounterRef(&$iAllCount = 0)
    {
        $this->pRowCounter = &$iAllCount;

        return $this;
    }

    public function groupBy($fields)
    {
        $this->addGroupBy($fields);

        return $this;
    }

    protected function buildQuery()
    {
        $sQuery = sprintf(
            'SELECT %s%s FROM %s%s%s%s%s%s',
            $this->pRowCounter === null ? '' : ' SQL_CALC_FOUND_ROWS ',
            $this->bOnlyCount ? 'COUNT(' . $this->bOnlyCount . ') AS `cnt`' : $this->getBuilder()->getFields4Select(),
            $this->getBuilder()->getTableName(),
            $this->getBuilder()->getJoin($this->iJoinCount),
            $this->getBuilder()->getWhere(),
            $this->getBuilder()->getGroupBy(),
            $this->getBuilder()->getOrder(),
            $this->getBuilder()->getLimit()
        );

        return $sQuery;
    }

    protected function getNewRow($aData)
    {
        $oEntity = $this->getQ()->getEntity();

        if ($oEntity instanceof ActiveRecord) {
            $oRow = clone  $oEntity;
        } elseif ($oEntity instanceof ft\Model) {
            $oRow = ActiveRecord::getByFTModel($oEntity);
        } else {
            $oRow = new ActiveRecord();
        }

        $oRow->setData($aData);

        return $oRow;
    }

    protected function getResult()
    {
        if ($this->bOnlyCount) {
            return $this->oAdapter->getValue('cnt');
        }

        $aItems = [];

        if ($this->oAdapter->rowsCount()) {
            while ($aData = $this->oAdapter->fetchArray()) {
                if ($this->asArray) {
                    ($this->sIndexField) ?
                        $aItems[$aData[$this->sIndexField]] = $aData :
                        $aItems[] = $aData;
                } else {
                    ($this->sIndexField) ?
                        $aItems[$aData[$this->sIndexField]] = $this->getNewRow($aData) :
                        $aItems[] = $this->getNewRow($aData);
                }
            }
        }

        $this->getFullCount();

        return $aItems;
    }

    public function getFullCount()
    {
        if ($this->pRowCounter !== null) {
            $this->oAdapter->applyQuery('SELECT FOUND_ROWS() as `cnt`;');
            $this->pRowCounter = (int) $this->oAdapter->getValue('cnt');
        }
    }

    /** Установить поле для индексации выходного массива */
    public function index($sFieldName)
    {
        $this->sIndexField = $sFieldName;

        return $this;
    }

    /**
     * Добавляет выражение LIKE '%$mValue%'.
     *
     * @param string $sColumnName - имя столбца таблицы
     * @param string $sValue - значение
     *
     * @return self
     */
    public function like($sColumnName, $sValue)
    {
        $aReplaceSymbols = [
            '%' => '\%',
            '_' => '\_',
            '\\' => '\\\\',
        ];

        $sValue = strtr($sValue, $aReplaceSymbols);

        $this->where("{$sColumnName} LIKE ?", "%{$sValue}%");

        return $this;
    }
}

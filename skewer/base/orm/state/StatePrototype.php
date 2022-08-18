<?php

namespace skewer\base\orm\state;

use skewer\base\orm;
use skewer\base\orm\service;

/**
 * Прототип для построителя запросов.
 */
abstract class StatePrototype
{
    /** @var orm\Query Ссылка на объект запросника */
    private $oQuery;

    /** @var service\Builder Сборщик запросов */
    private $oBuilder;

    /** @var service\Storage Хранилище для сборки запроса */
    protected $oStorage;

    /** @var service\DataBaseAdapter Инстанс адапрета */
    protected $oAdapter;

    /** @var bool Выводить запрос в отладчик */
    protected $bDebug = false;

    public function __construct($oQuery)
    {
        $this->oQuery = $oQuery;
        $this->oStorage = new service\Storage();
        $this->oBuilder = new service\Builder($this->oStorage);
        //$this->oBuilder->setTableName( $sTableName, $sTableAlias );
    }

    protected function getBuilder()
    {
        if (!$this->oBuilder) {
            throw new \Exception('Не найден сборщик для состояния');
        }

        return $this->oBuilder;
    }

    protected function getStorage()
    {
        return $this->oStorage;
    }

    protected function getQ()
    {
        return $this->oQuery;
    }

    public function debug()
    {
        $this->bDebug = true;

        return $this;
    }

    abstract protected function buildQuery();

    abstract protected function getResult();

    private static function getFormatedQuery($query, $data)
    {
        if (count($data)) {
            foreach ($data as $sLabel => $mValue) {
                if (is_string($mValue)) {
                    $query = preg_replace("/:{$sLabel}\\b/", "'" . $mValue . "'", $query);
                } else {
                    $query = preg_replace("/:{$sLabel}\\b/", $mValue, $query);
                }
            }
        }

        return $query;
    }

    /**
     * Получить текста запроса.
     *
     * @return string
     *
     * @deprecated эта функция нужны для тестирования и может отдавать некорректные данные
     */
    public function getQuery()
    {
        $self = clone $this;

        $sQuery = $self->buildQuery();
        $aData = $self->getStorage()->getVars();

        return self::getFormatedQuery($sQuery, $aData);
    }

    /**
     * Выполнение запроса.
     *
     * @param bool $bWithResult выводить с результатом
     *
     * @return bool|int
     */
    public function get($bWithResult = true)
    {
        $this->oAdapter = new service\DataBaseAdapter();
        $query = $this->buildQuery();
        $data = $this->getStorage()->getVars();

        if ($this->bDebug) {
            \Yii::warning(self::getFormatedQuery($query, $data));
        }

        $this->oAdapter->applyQuery($query, $data);

        return $bWithResult ? $this->getResult() : true;
    }

    /**
     * выполнить запрос и получить список всех изменившихся записей.
     *
     * @return int
     * WTF???
     *
     * @deprecated use get()
     */
    public function getAffectedRows()
    {
        $oDBAdapter = new service\DataBaseAdapter();
        $oDBAdapter->applyQuery($this->buildQuery(), $this->getStorage()->getVars());

        return $oDBAdapter->affectedRows();
    }

    /**
     * Добавление выражения в секцию.
     *
     * @param string $sSectionName Имя секции
     * @param string $sExpression Выражение
     * @param string $mData Данные для выражения
     * @param string $sSep Разделитель между выражениями
     */
    protected function addExpr2Section($sSectionName, $sExpression, $mData = '', $sSep = service\Storage::SEP_COMMA)
    {
        $this->getStorage()->add($sSectionName, [
            'expr' => $sExpression,
            'data' => $mData,
            'sep' => $sSep,
        ]);
    }

    /**
     * Добавление выражения без обработки в секцию.
     *
     * @param string $sSectionName Имя секции
     * @param string $sExpression Выражение
     * @param string $sSep Разделитель между выражениями
     */
    protected function addRawExpr2Section($sSectionName, $sExpression, $sSep = service\Storage::SEP_COMMA)
    {
        $this->getStorage()->add($sSectionName, [
            'expr' => $sExpression,
            'sep' => $sSep,
        ]);
    }

    protected function addOrder($sField, $sWay = 'ASC')
    {
        if (!in_array($sWay, ['DESC', 'ASC'/*, 'ASC_NUMERIC', 'DESC_NUMERIC'*/])) {
            $sWay = 'ASC';
        }

        $sTable = '';

        if (($pos = mb_strpos($sField, '.')) !== false) {
            $sTable = mb_substr($sField, 0, $pos);
            $sField = mb_substr($sField, $pos + 1);
        }

        $this->getStorage()->add('order', [
            'field' => $sField,
            'table' => $sTable,
            'way' => $sWay,
        ]);
    }

    protected function addLimit($iCount, $iShift = 0)
    {
        if ($this->getStorage()->count('limit')) {
            throw new \Exception('Секция LIMIT задается несколько раз');
        }
        $this->oStorage->add('limit', [
            'count' => (int) $iCount,
            'shift' => (int) $iShift,
        ]);
    }

    protected function addSet($sField, $sValue = null)
    {
        $this->oStorage->add('set', [
            'field' => $sField,
            'value' => $sValue,
        ]);
    }

    protected function addSetInc($sField, $aParams = [])
    {
        $this->oStorage->add('setInc', [
            'field' => $sField,
            'params' => $aParams,
        ]);
    }

    protected function addSetUpdate($sField, $sValue = null)
    {
        $this->oStorage->add('setUpdate', [
            'field' => $sField,
            'value' => $sValue,
        ]);
    }

    protected function addGroupBy($sField)
    {
        $sTable = '';

        if (($pos = mb_strpos($sField, '.')) !== false) {
            $sTable = mb_substr($sField, 0, $pos);
            $sField = mb_substr($sField, $pos + 1);
        }

        $this->oStorage->add('groupBy', [
            'table' => $sTable,
            'field' => $sField,
        ]);
    }

    /**
     * Добавление имени таблицы.
     *
     * @param $mTable
     * @param string $sAlias
     */
    public function addTableName($mTable, $sAlias = '')
    {
        $this->oBuilder->setTableName($mTable, $sAlias);
    }

    /**
     * Добавление новой таблицы в выборку.
     *
     * @param string $sTableName
     */
    public function addFrom($sTableName)
    {
        if (($pos = mb_strpos($sTableName, ' AS ')) !== false) {
            $this->oStorage->add('from', [
                'name' => trim(mb_substr($sTableName, 0, $pos)),
                'alias' => trim(mb_substr($sTableName, $pos + 4)),
            ]);
        } else {
            $this->oStorage->add('from', [
                'name' => $sTableName,
                'alias' => '',
            ]);
        }
    }
}

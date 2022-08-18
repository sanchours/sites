<?php

namespace skewer\base\orm\service;

class Builder
{
    private $sTableName = '';
    private $sTableAlias = '';

    /** @var Storage Хранилище для сборщика запросов */
    private $oStorage;

    public function __construct($oStorage)
    {
        $this->oStorage = $oStorage;
    }

    public function setTableName($sName, $sAlias = '')
    {
        $this->sTableName = $sName;
        $this->sTableAlias = $sAlias;
    }

    public function getTableName()
    {
        if ($this->sTableName) {
            $sOut = sprintf('`%s`', $this->sTableName);
        } else {
            $sOut = $this->getFrom();
        }

        return $sOut;
    }

    public function getTableAlias()
    {
        return $this->sTableAlias;
    }

    private function getStorage()
    {
        return $this->oStorage;
    }

    /**
     * Формирует список полей для запроса типа SELECT.
     *
     * @return string
     */
    public function getFields4Select()
    {
        if ($this->getStorage()->count('fields')) {
            $sFieldList = $this->getStorage()->get('fields');

            if (!$sFieldList) {
                return '*';
            }

            $aItems = [];
            foreach ($sFieldList as $aField) {
                $sOut = '';

                if (is_string($aField)) {
                    $sOut = $aField;
                } else {
                    if ($aField['alias']) {
                        $sOut .= $aField['alias'] . '.';
                    }

                    // Взять имя поля в обратные апострофы, если ещё не взято
                    if (($aField['name'] != '*') and (mb_strpos($aField['name'], '`') === false)) {
                        $aField['name'] = '`' . $aField['name'] . '`';
                    }

                    $sOut .= $aField['name'];
                }

                $aItems[] = $sOut;
            }
            $sRes = implode(', ', $aItems);
        } else {
            $sRes = '*';
        }

        return $sRes;
    }

    /**
     * Формирование секции FROM.
     */
    public function getFrom()
    {
        $aItems = [];

        while ($this->getStorage()->count('from')) {
            $aItem = $this->getStorage()->shift('from');

            $aItems[] = $aItem['alias'] ? sprintf('`%s` AS %s', $aItem['name'], $aItem['alias']) : '`' . $aItem['name'] . '`';
        }

        $sResult = implode(', ', $aItems);

        return $sResult;
    }

    /**
     * Сборка последовательности выражений.
     *
     * @param string $sSection Раздел
     * @param string $sStartWord
     *
     * @return string
     */
    protected function buildSequenceExpressions($sSection, $sStartWord = 'WHERE')
    {
        $aItems = [];
        $sResult = '';

        while ($this->getStorage()->count($sSection)) {
            $aItem = $this->getStorage()->shift($sSection);

            $sExp = $aItems[] = $this->getStorage()->parseExpression($aItem);

            $sResult .= ($sResult ? $aItem['sep'] : ' ' . $sStartWord . ' ') . $sExp;
        }

        return $sResult;
    }

    /**
     * Формирование раздела WHERE.
     *
     * @return string
     */
    public function getWhere()
    {
        return $this->buildSequenceExpressions('where', 'WHERE');
    }

    public function getOrder()
    {
        $aItems = [];

        while ($this->getStorage()->count('order')) {
            $aItem = $this->getStorage()->shift('order');

            if ($aItem['field'] != 'RAND()') {
                $aItems[] = sprintf(
                    '%s`%s` %s',
                    $aItem['table'] ? ($aItem['table'] . '.') : '',
                    $aItem['field'],
                    $aItem['way']
                );
            } else {
                $aItems[] = 'RAND()';
            }
        }

        return count($aItems) ? (' ORDER BY ' . implode(', ', $aItems)) : '';
    }

    public function getLimit()
    {
        $sItem = '';

        if ($this->getStorage()->count('limit')) {
            $aItem = $this->getStorage()->shift('limit');

            if ($aItem['count']) {
                $sItem = ' LIMIT ' . ($aItem['shift'] ? $aItem['shift'] . ', ' : '') . $aItem['count'];
            }
        }

        return $sItem;
    }

    public function getSet($sPref = '')
    {
        $aItems = [];
        //$sResult = '';

        while ($this->getStorage()->count('set' . $sPref)) {
            $aItem = $this->getStorage()->shift('set' . $sPref);

            if ($aItem['value'] === null) {
                $aItems[] = $this->getStorage()->parseEqualExprassion($aItem['field']);
            } elseif (mb_strpos($aItem['field'], '=')) {
                $aItems[] = $this->getStorage()->parseEqualExprassion($aItem['field'], $aItem['value']);
            } else {
                $sLabel = $this->getStorage()->getLabel4Var($aItem['value']);
                $aItems[] = sprintf('`%s`=:%s', $aItem['field'], $sLabel);
            }
        }

        if (!count($aItems)) {
            throw new \Exception('Не задана секция SET' . $sPref);
        }
        $sResult = implode(', ', $aItems);

        return $sResult;
    }

    public function getSetInc()
    {
        $aItems = [];

        while ($this->getStorage()->count('setInc')) {
            $aItem = $this->getStorage()->shift('setInc');

            $sFieldName = $aItem['field'];
            $aParams = $aItem['params'];

            $aWhereItems = [];

            foreach ($aParams as $sParamName => $sValue) {
                $sLabel = $this->getStorage()->getLabel4Var($sValue);
                $aWhereItems[] = sprintf('`%s`=:%s', $sParamName, $sLabel);
            }

            $aItems[] = sprintf(
                '`%s` = (SELECT IFNULL(max( `%s` ), 0) AS pos FROM `%s` AS t%s) + 1',
                $sFieldName,
                $sFieldName,
                $this->sTableName,
                count($aWhereItems) ? ' WHERE ' . implode(' AND ', $aWhereItems) : ''
            );
        }

        $sResult = (count($aItems) ? ', ' : '') . implode(', ', $aItems);

        return $sResult;
    }

    public function getJoin($iCount)
    {
        if (!$iCount) {
            return '';
        }

        $aItems = [];

        for ($i = 1; $i <= $iCount; ++$i) {
            $aJoinItem = $this->getStorage()->shift('join' . $i);

            $aItems[] = sprintf(
                ' %s `%s` AS %s%s',
                $aJoinItem['type'],
                $aJoinItem['table'],
                $aJoinItem['alias'],
                $this->buildSequenceExpressions('on' . $i, 'ON')
            );
        }

        return implode(' ', $aItems);
    }

    public function getGroupBy()
    {
        $sItem = '';

        if ($this->getStorage()->count('groupBy')) {
            $aItem = $this->getStorage()->shift('groupBy');

            $sItem = sprintf(
                ' GROUP BY %s`%s`',
                $aItem['table'] ? ($aItem['table'] . '.') : '',
                $aItem['field']
            );
        }

        return $sItem;
    }
}

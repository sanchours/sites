<?php

namespace skewer\base\orm\service;

class Storage
{
    const SEP_SPACE = ' ';
    const SEP_COMMA = ', ';
    const SEP_LOGIC_AND = ' AND ';
    const SEP_LOGIC_OR = ' OR ';

    private $aOperators = ['<>', '!=', '>=', '<=', '=', '>', '<', ' LIKE ', ' BETWEEN ', ' NOT IN ', ' IN '];

    private $aMathOperators = ['+', '-', '*'];

    /** @var array Хранилище секций для сборки запросов */
    private $aData = [];

    /** @var array Хранилице переменных для посдтановки в запрос */
    private $aVars = [];

    /**
     * @param $sSection
     *
     * @return bool|mixed
     */
    public function shift($sSection)
    {
        return $this->count($sSection) ? array_shift($this->aData[$sSection]) : false;
    }

    /**
     * @param $sSection
     *
     * @return bool|mixed
     */
    public function get($sSection)
    {
        return $this->count($sSection) ? $this->aData[$sSection] : false;
    }

    /**
     * @param $sSection
     * @param $mItem
     */
    public function add($sSection, $mItem)
    {
        $this->aData[$sSection][] = $mItem;
    }

    /**
     * Кол-во элементов в разделе.
     *
     * @param string $sSection Раздел
     *
     * @return int
     */
    public function count($sSection)
    {
        if (!isset($this->aData[$sSection])) {
            return 0;
        }

        return count($this->aData[$sSection]);
    }

    /**
     * Устанавливает значение для переменной.
     *
     * @param $sVarName
     * @param bool $sValue
     */
    public function setVar($sVarName, $sValue = true)
    {
        $this->aVars[$sVarName] = $sValue;
    }

    /**
     * Получение значения переменной.
     *
     * @param $sVarName
     */
    public function getVar($sVarName)
    {
        return isset($this->aVars[$sVarName]) ? $this->aVars[$sVarName] : null;
    }

    /**
     * Добавление переменной и получение ее метки.
     *
     * @param $mValue
     *
     * @return string
     */
    public function getLabel4Var($mValue)
    {
        $sLabel = 'var' . (count($this->aVars) + 1);
        $this->setVar($sLabel, $mValue);

        return $sLabel;
    }

    /**
     * Получение набора переменных.
     *
     * @return array
     */
    public function getVars()
    {
        return $this->aVars;
    }

    private function getFieldName($sField)
    {
        if (($pos = mb_strpos($sField, '.')) !== false) {
            $sField = sprintf(
                '%s.`%s`',
                trim(mb_substr($sField, 0, $pos)),
                trim(mb_substr($sField, $pos + 1))
            );
        } else {
            $sField = '`' . trim($sField) . '`';
        }

        return $sField;
    }

    /**
     * Поиск оператора и преобразование строки.
     *
     * @param $sExp
     * @param $mData
     *
     * @return bool
     */
    private function findOperator($sExp, $mData = false)
    {
        $iPos = false;

        foreach ($this->aOperators as $sOperator) {
            if ($iPos = mb_strpos($sExp, $sOperator)) {
                break;
            }
        }

        if (!$iPos) {
            return false;
        }

        $sExpLeft = trim(mb_substr($sExp, 0, $iPos));
        $sExpRight = trim(mb_substr($sExp, $iPos + mb_strlen($sOperator)));

        if ($sOperator == ' BETWEEN ') {
            $self = $this;

            return sprintf(
                '(%s%s%s)',
                $this->getFieldName($sExpLeft),
                $sOperator,
                implode(self::SEP_LOGIC_AND, array_map(static function ($s) use (&$self) { return ':' . $self->getLabel4Var($s); }, $mData))
            );
        }

        if ($sExpRight == '?') {
            if (is_array($mData)) {
                $self = $this;

                return sprintf(
                    '%s%s(%s)',
                    $this->getFieldName($sExpLeft),
                    $sOperator,
                    implode(
                        ', ',
                        array_map(
                            static function ($s) use (&$self) {
                                return is_numeric($s) ? (int) $s : ':' . $self->getLabel4Var($s);
                            },
                            $mData
                    )
                    )
                );
            }

            $sLabel = $this->getLabel4Var($mData);
            $sRes = sprintf(
                '%s%s:%s',
                $this->getFieldName($sExpLeft),
                $sOperator,
                $sLabel
            );
        } else {
            $sRes = sprintf(
                '%s%s%s',
                $this->getFieldName($sExpLeft),
                $sOperator,
                $this->getFieldName($sExpRight)
            );
        }

        return $sRes;
    }

    /**
     * Поиск мат оператора и преобразование строки.
     *
     * @param $sExp
     * @param $mData
     *
     * @return bool|string
     */
    private function findMathOp($sExp, $mData = false)
    {
        $iPos = false;

        foreach ($this->aMathOperators as $sOperator) {
            if ($iPos = mb_strpos($sExp, $sOperator)) {
                break;
            }
        }

        if (!$iPos) {
            return '`' . $sExp . '`';
        }

        $sExpLeft = mb_substr($sExp, 0, $iPos);
        $sExpRight = mb_substr($sExp, $iPos + mb_strlen($sOperator));

        if ($sExpRight == '?') {
            $sLabel = $this->getLabel4Var($mData);
            $sRes = sprintf(
                '%s%s:%s',
                $this->getFieldName($sExpLeft),
                $sOperator,
                $sLabel
            );
        } else {
            $sRes = sprintf(
                '%s%s%s',
                $this->getFieldName($sExpLeft),
                $sOperator,
                $this->getFieldName($sExpRight)
            );
        }

        return $sRes;
    }

    /**
     * Поиск оператора равенства и преображование строки.
     *
     * @param $sExp
     * @param bool $aData
     *
     * @return bool
     */
    private function findEqual($sExp, $aData = false)
    {
        // разбеение по оператору равенства
        $sOperator = '=';
        $iPos = mb_strpos($sExp, $sOperator);

        if (!$iPos) {
            return false;
        }

        $sField = mb_substr($sExp, 0, $iPos);
        $sValue = mb_substr($sExp, $iPos + mb_strlen($sOperator));

        if ($sValue == '?') {
            $sValue = $aData;
        } else {
            $sValue = $this->findMathOp($sValue, $aData);
        }

        return sprintf(
            '`%s`%s%s',
            $sField,
            $sOperator,
            $sValue
        );
    }

    /**
     * Сборка выражения.
     *
     * @param $aItem
     *
     * @return string
     */
    public function parseExpression($aItem)
    {
        if (isset($aItem['data'])) {
            if ($sExp = $this->findOperator($aItem['expr'], $aItem['data'])) {
                //var_dump($sExp);
            } else {
                $sFieldName = '`' . $aItem['expr'] . '`';
                $sFieldName = str_replace('.', '`.`', $sFieldName);

                if (is_array($aItem['data'])) {
                    $sExp = sprintf(
                        '%s IN (%s)',
                        $sFieldName,
                        implode(',', array_map(static function ($i) {return (int) $i; }, $aItem['data']))
                    );
                } else {
                    $sLabel = $this->getLabel4Var($aItem['data']);
                    $sExp = sprintf('%s=:%s', $sFieldName, $sLabel);
                }
            }
        } else {
            $sExp = $aItem['expr'];
        }

        return $sExp;
    }

    /**
     * Сборка выражения равенства.
     *
     * @param $sExp
     * @param bool $mVal
     *
     * @return string
     */
    public function parseEqualExprassion($sExp, $mVal = false)
    {
        return $this->findEqual($sExp, $mVal);
    }
}

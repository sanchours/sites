<?php

namespace skewer\base\orm\service;

use yii\db\DataReader;

/**
 * Адаптер для прямых запросов к базе данных и получения результатов.
 */
class DataBaseAdapter
{
    /** @var DataReader Команда PDO */
    private $oCmd;

    private $sError = '';

    public function __construct()
    {
    }

    public function getError()
    {
        return $this->sError;
    }

    public function applyQuery($sQuery, $aData = [])
    {
        $this->sError = '';

        $i = 1;
        while (($pos = mb_strpos($sQuery, '?')) !== false) {
            $label = 'vl' . $i;
            $sQuery = mb_substr($sQuery, 0, $pos) . ':' . $label . mb_substr($sQuery, $pos + 1);
            $aData[':' . $label] = array_shift($aData);
            ++$i;
        }

        $newData = [];
        foreach ($aData as $k => &$item) {
            /*Преобразуем строковое null в нормальный null*/
            if ($item === 'null') {
                $item = null;
            }

            if ($k[0] != ':') {
                $k = ':' . $k;
            }
            $newData[$k] = $item;
        }

        $aData = $newData;

        $this->oCmd = \Yii::$app->db->createCommand($sQuery, $aData)->query();

        return $this;
    }

    public function rowsCount()
    {
        return $this->oCmd->count();
    }

    /**
     * @deprecated использовать запросник Yii
     *
     * @param int $iMode режим работы выборки \PDO::FETCH_ASSOC \ \PDO::FETCH_NUM
     *
     * @return null|mixed
     */
    public function fetchArray($iMode = \PDO::FETCH_ASSOC)
    {
        if ($iMode) {
            $this->oCmd->setFetchMode($iMode);
        }

        return $this->oCmd->read();
    }

    /**
     * @deprecated использовать запросник Yii
     *
     * @return int
     */
    public function affectedRows()
    {
        return $this->oCmd->rowCount;
    }

    /**
     * @deprecated использовать запросник Yii
     *
     * @return string
     */
    public function lastId()
    {
        return \Yii::$app->db->lastInsertID;
    }

    /**
     * @deprecated использовать запросник Yii
     *
     * @param $sField
     */
    public function getValue($sField)
    {
        $result = $this->oCmd->read();
        if (!$result) {
            return;
        }

        if (isset($result[$sField])) {
            return $result[$sField];
        }
    }
}

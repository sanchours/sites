<?php

namespace skewer\components\catalog;

use skewer\base\ft;
use skewer\base\log\Logger;
use skewer\base\orm\ActiveRecord;
use skewer\base\orm\Query;

abstract class GoodsRowPrototype
{
    /** @var ActiveRecord AR объект записи базовой карточки */
    protected $oBaseRow;

    /** @var ActiveRecord AR объект записи расширенной карточки */
    protected $oExtRow;

    /** @var int id записи */
    protected $iRowId = 0;

    /** @var string имя карточки базовой записи */
    protected $sBaseCardName = '';

    /** @var string имя карточки расширенной записи */
    protected $sExtCardName = '';

    /** @var int id базовой записи */
    protected $iMainRowId = 0;

    /** @var string[] набор ошибок по полям */
    protected $aErrorList = [];

    /** @var bool Были ли изменены атритуты записи */
    protected $bUpdated = false;

    /**
     * Отдает набор ошибок по полям
     *
     * @return \string[]
     */
    public function getErrorList()
    {
        return $this->aErrorList;
    }

    /**
     * Задает AR объект записи базовой карточки.
     *
     * @param ActiveRecord $oRow
     */
    public function setBaseRow(ActiveRecord $oRow)
    {
        $this->oBaseRow = $oRow;
    }

    /**
     * Отдает AR объект записи базовой карточки.
     *
     * @return ActiveRecord
     */
    public function getBaseRow()
    {
        return $this->oBaseRow;
    }

    /**
     * Отдает флаг наличия AR объекта записи базовой карточки.
     *
     * @return bool
     */
    public function hasBaseRow()
    {
        return (bool) $this->oBaseRow;
    }

    /**
     * Задает AR объект записи расширенной карточки.
     *
     * @param ActiveRecord $oRow
     */
    public function setExtRow(ActiveRecord $oRow)
    {
        $this->oExtRow = $oRow;
    }

    /**
     * Отдает AR объект записи расширенной карточки.
     *
     * @return ActiveRecord
     */
    public function getExtRow()
    {
        return $this->oExtRow;
    }

    /**
     * Отдает флаг наличия AR объекта записи расширенной карточки.
     *
     * @return bool
     */
    public function hasExtRow()
    {
        return (bool) $this->oExtRow;
    }

    /**
     * Отдает id записи.
     *
     * @return int
     */
    public function getRowId()
    {
        return $this->iRowId;
    }

    /**
     * Задает id записи.
     *
     * @param int $iBaseId
     */
    protected function setRowId($iBaseId)
    {
        $this->iRowId = $iBaseId;
    }

    /**
     * Отдает id записи.
     *
     * @return int
     */
    public function getMainRowId()
    {
        return $this->iMainRowId;
    }

    /**
     * Задает id базовой записи.
     *
     * @param int $iMainId
     */
    public function setMainRowId($iMainId)
    {
        $this->iMainRowId = ($iMainId == $this->iRowId) ? false : $iMainId;
    }

    /**
     * Базовый ли это товар
     *
     * @return bool
     */
    public function isMainRow()
    {
        return !$this->iMainRowId || ($this->iMainRowId == $this->iRowId);
    }

    /**
     * Отдает имя базовой карточки в связи.
     *
     * @return string
     */
    public function getBaseCardName()
    {
        return $this->sBaseCardName;
    }

    /**
     * Задает имя базовой карточки в связи.
     *
     * @param string $sBaseCardName
     */
    public function setBaseCardName($sBaseCardName)
    {
        $this->sBaseCardName = $sBaseCardName;
    }

    /**
     * Отдает имя расширенной карточки в связи.
     *
     * @return string
     */
    public function getExtCardName()
    {
        return $this->sExtCardName;
    }

    /**
     * Задает имя расширенной карточки в связи.
     *
     * @param string $sExtCardName
     */
    public function setExtCardName($sExtCardName)
    {
        $this->sExtCardName = $sExtCardName;
    }

    /**
     * Отдает id расширенной карточки по имени в текущей записи.
     *
     * @return int
     */
    protected function getExtCardId()
    {
        return ft\Cache::get($this->getExtCardName())->getEntityId();
    }

    /**
     * Отдает id базовой карточки по имени в текущей записи.
     *
     * @return int
     */
    protected function getBaseCardId()
    {
        return ft\Cache::get($this->getBaseCardName())->getEntityId();
    }

    /**
     * Отдает набор полей.
     *
     * @return ft\model\Field[]
     */
    public function getFields()
    {
        $aData = [];

        if ($this->hasBaseRow()) {
            $aData = $this->getBaseRow()->getModel()->getFileds();
        }

        if ($this->hasExtRow()) {
            $oExtModel = clone $this->getExtRow()->getModel();
            foreach ($oExtModel->getFileds() as $oField) {
                $aData[$oField->getName()] = $oField;
            }
        }

        return $aData;
    }

    /**
     * Задание флага того, что данные были модифицированы последним запросом
     *
     * @param mixed $mVal
     */
    protected function setWasUpdated($mVal)
    {
        $this->bUpdated = (bool) $mVal;
    }

    /**
     * Флаг того, что данные были модифицированы последним запросом
     *
     * @return bool
     */
    public function wasUpdated()
    {
        return $this->bUpdated;
    }

    /**
     * Сохранение базовой и расширенной карточки.
     *
     * @throws \Exception
     *
     * @return int id основной карточки
     */
    public function save()
    {
        if (!$this->hasBaseRow()) {
            throw new \Exception('Не задан AR объект базовой карточки');
        }
        $oBaseRow = $this->getBaseRow();
        $oExtRow = $this->getExtRow();

        $bNew = !$this->iRowId; // !$oBaseRow->getPrimaryKeyValue();

        try {
            // старт транзакции
            Query::startTransaction();

            // сохранить базовую
            $iBaseId = $oBaseRow->save();

            if (!$iBaseId) {
                $this->aErrorList = $oBaseRow->getErrorList();
                throw new ft\exception\Query(
                    sprintf(
                        'не сохранена базовая запись %s - base.id=%d',
                        $oBaseRow->getModel()->getName(),
                        $this->iRowId
                    )
                );
            }

            if ($bNew) {
                $this->setRowId($iBaseId);
            }

            if ($oExtRow) {
                // перекрыть расширяющий id
                $oExtRow->setPrimaryKeyValue($iBaseId);

                // сохранить расширенную
                $iExtId = $oExtRow->save();
            } else {
                $iExtId = 0;
            }

            $this->setWasUpdated($oBaseRow->wasUpdated() || $oExtRow->wasUpdated());

            // если не сохранена базовая или ( задана расширенная и не сохранена )
            if (!$iBaseId or ($oExtRow and !$iExtId)) {
                $this->aErrorList = $oBaseRow->getErrorList();

                if ($oExtRow) {
                    foreach ($oExtRow->getErrorList() as $sFieldName => $sVal) {
                        $this->aErrorList[$sFieldName] = $sVal;
                    }
                }

                $oMainRow = $oExtRow ? $oExtRow : $oBaseRow;
                throw new ft\exception\Query(
                    sprintf(
                        'не сохранена композитная запись %s - base.id=%d, ext.id=%d',
                        $oMainRow->getModel()->getName(),
                        $iBaseId,
                        $iExtId
                    )
                );
            }

            // конец транзакции
            Query::commitTransaction();

            $iRes = $iBaseId;
        } catch (ft\exception\Query $e) {
            // ошибка должна содержаться в $this->aErrorList
            if (!$this->aErrorList) {
                Logger::dumpException($e);
            }

            if ($bNew) {
                $oBaseRow->setPrimaryKeyValue(0);
                $oExtRow->setPrimaryKeyValue(0);
                $this->setRowId(0);
            }

            Query::rollbackTransaction();

            $iRes = 0;
        }

        return $iRes;
    }

    /**
     * Удаление базовой и расширенной карточки.
     *
     * @return bool
     */
    public function delete()
    {
        try {
            // старт транзакции
            Query::startTransaction();

            $iDelCnt = $this->oBaseRow->delete();
            $iAddDelCnt = $this->oExtRow->delete();

            // если ничего не удалено
            if (!$iDelCnt and !$iAddDelCnt) {
                $this->aErrorList = $this->oBaseRow->getErrorList();

                if ($this->oExtRow) {
                    foreach ($this->oExtRow->getErrorList() as $sFieldName => $sVal) {
                        $this->aErrorList[$sFieldName] = $sVal;
                    }
                }

                throw new ft\exception\Query(sprintf(
                    'не удалена ни одна из записей копозитной сущности [%s:%d]',
                    $this->oExtRow->getModel()->getName(),
                    $this->oBaseRow->getPrimaryKeyValue()
                ));
            }

            // конец транзакции
            Query::commitTransaction();

            return true;
        } catch (ft\exception\Query $e) {
            Query::rollbackTransaction();

            return false;
        }
    }

    /**
     * Returns the fully qualified name of this class.
     *
     * @return string the fully qualified name of this class
     */
    public static function className()
    {
        return get_called_class();
    }
}

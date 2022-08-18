<?php

namespace skewer\base\orm;

use skewer\base\ft;
use skewer\components\catalog\CatalogGoodRow;

/**
 * Class MagicTable.
 */
class MagicTable
{
    /** @var ft\Model Модель */
    protected $oModel;

    protected function setModel($oModel)
    {
        $this->oModel = $oModel;
    }

    /**
     * Генератор объекта для модели.
     *
     * @param $oModel
     *
     * @return MagicTable
     */
    public static function init($oModel)
    {
        $oTable = new self();

        $oTable->setModel($oModel);

        return $oTable;
    }

    public function getTableName()
    {
        return $this->oModel->getTableName();
    }

    /**
     * Отдает имя сущности.
     *
     * @return string
     */
    public function getName()
    {
        return $this->oModel->getName();
    }

    public function getKeyField()
    {
        return $this->oModel->getPrimaryKey();
    }

    /**
     * Поиск записей.
     *
     * @param null $id
     *
     * @return ActiveRecord|bool|CatalogGoodRow|state\StateSelect
     */
    public function find($id = null)
    {
        if ($id !== null) {
            $oRow = static::getNewRow();
            $res = Query::SelectFrom($this->getTableName(), $oRow)->where($this->getKeyField(), $id)->getOne();
        } else {
            $oRow = static::getNewRow();
            $res = Query::SelectFrom($this->getTableName(), $oRow);
        }

        return $res;
    }

    /**
     * Удаление записей.
     *
     * @param null $id
     *
     * @return state\StateDelete
     */
    public function delete($id = null)
    {
        if ($id !== null) {
            $res = Query::DeleteFrom($this->getTableName())->where($this->getKeyField(), $id)->get();
        } else {
            $res = Query::DeleteFrom($this->getTableName());
        }

        \Yii::$app->router->updateModificationDateSite();

        return $res;
    }

    /**
     * Удаление записей по набору полей.
     *
     * @param array $set
     * @param array $fields
     *
     * @return bool
     */
    public function update($set = [], $fields = [])
    {
        $query = Query::UpdateFrom($this->getTableName());

        foreach ($set as $field => $value) {
            $query->set($field, $value);
        }

        if (count($fields)) {
            foreach ($fields as $field => $value) {
                $query->where($field, $value);
            }
        }

        \Yii::$app->router->updateModificationDateSite();

        return $query->get();
    }

    /**
     * Получить экземлять записи для таблицы.
     *
     * @param array $aData
     *
     * @return ActiveRecord
     */
    public function getNewRow($aData = [])
    {
        $oRow = ActiveRecord::getByFTModel($this->oModel);

        if ($aData) {
            $oRow->setData($aData);
        }

        return $oRow;
    }

    public function getModel()
    {
        return $this->oModel;
    }
}

<?php

namespace skewer\components\catalog\model;

use skewer\base\orm\ActiveRecord;

/**
 * Запись атрибута для поля сущности
 * Class FieldAttrRow.
 */
class FieldAttrRow extends ActiveRecord
{
    public $id = 0;
    public $field = 0;
    public $tpl = 0;
    public $value = '';

    /**
     * Отдает имя таблицы.
     *
     * @return string
     */
    public function getTableName()
    {
        return 'c_field_attr';
    }

    public function initSave()
    {
        \Yii::$app->router->updateModificationDateSite();

        return parent::initSave();
    }

    public function preDelete()
    {
        \Yii::$app->router->updateModificationDateSite();
    }
}

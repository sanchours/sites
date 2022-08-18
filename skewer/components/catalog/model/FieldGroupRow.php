<?php

namespace skewer\components\catalog\model;

use skewer\base\orm\ActiveRecord;
use skewer\helpers\Transliterate;

/**
 * Запись для группы полей сущности
 * Class FieldGroupRow.
 */
class FieldGroupRow extends ActiveRecord
{
    public $id = 0;
    public $name = '';
    public $title = '';
    public $position = 0;
    public $group_type = 0;

    public function getTableName()
    {
        return 'c_field_group';
    }

    public function __toString()
    {
        return $this->title;
    }

    /**
     * Получение списка полей для группы.
     *
     * @return FieldRow[]
     */
    public function getFields()
    {
        return FieldTable::find()
            ->where('group', $this->id)
            ->order('position')
            ->getAll();
    }

    public function save()
    {
        $this->checkUniqueName();
        $this->checkPos();

        return parent::save();
    }

    public function beforeDelete()
    {
        FieldTable::update()
            ->set('group', 0)
            ->where('group', $this->id)
            ->get();

        \Yii::$app->router->updateModificationDateSite();

        return parent::beforeDelete();
    }

    private function checkUniqueName()
    {
        if (!$this->name) {
            $this->name = $this->title ?: 'field_group';
        }

        $name = Transliterate::genSysName($this->name, 'field_group');

        $i = '';
        do {
            $this->name = $name . $i;
            $res = FieldGroupTable::findOne([
                'name' => $name . $i,
                'id<>?' => $this->id,
            ]);
            ++$i;
        } while ($res);

        return true;
    }

    private function checkPos()
    {
        if (!$this->position) {
            /** @var FieldGroupRow $oGroup */
            $oGroup = FieldGroupTable::find()
                ->order('position', 'DESC')
                ->getOne();

            $this->position = $oGroup ? $oGroup->position + 1 : 1;
        }
    }
}

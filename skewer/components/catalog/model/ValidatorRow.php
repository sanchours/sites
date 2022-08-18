<?php

namespace skewer\components\catalog\model;

use skewer\base\orm\ActiveRecord;

/**
 * Запись валидатора на поля сущностей
 * Class ValidatorRow.
 */
class ValidatorRow extends ActiveRecord
{
    public $id = 0;
    public $name = '';
    public $field = 0;

    public function getTableName()
    {
        return 'c_validator';
    }

    public function __toString()
    {
        return $this->name;
    }
}
